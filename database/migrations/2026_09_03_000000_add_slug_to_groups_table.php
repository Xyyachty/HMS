<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * A public handle for a team, so its hotel site can be given a link.
 *
 * Nothing in this app could address a team from outside it. group_name is unique only per
 * faculty and is citext, so two faculties can each own a "Team A" and neither name is a
 * usable URL on its own. Everything else resolves the team from the logged-in student's
 * membership, which an anonymous visitor does not have.
 *
 * The slug is globally unique and, deliberately, is never regenerated. Renaming a team
 * already rewrites nine tables in FacultyController::updateGroup(); a link somebody has
 * shared must not become a tenth thing that breaks. So the slug is set once, from the name
 * the team had at the time, and then left alone.
 *
 * Backfill has two halves because `groups` is very nearly a dead table — three writes in
 * FacultyController and no reads anywhere. A team that predates it, or that was created by
 * a path which skipped it, has membership rows in student_groups and no groups row at all.
 * Those get one before they get a slug, or they could never be published.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('groups', 'slug')) {
            Schema::table('groups', function (Blueprint $table) {
                // Nullable: a team with no slug simply has no public site yet, which is a
                // better failure than a migration that cannot find a free name for one.
                $table->string('slug')->nullable()->unique()->after('group_name');
            });
        }

        $this->createMissingGroupRows();
        $this->backfillSlugs();
    }

    public function down(): void
    {
        if (Schema::hasColumn('groups', 'slug')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            });
        }
    }

    /**
     * Every real team is a distinct (group_name, faculty_id) in student_groups. Give any
     * that has no groups row one, so it can carry a slug.
     */
    private function createMissingGroupRows(): void
    {
        $now = now();

        DB::table('student_groups')
            ->select('group_name', 'faculty_id')
            ->whereNotNull('group_name')
            ->distinct()
            ->orderBy('group_name')
            ->chunk(200, function ($teams) use ($now) {
                foreach ($teams as $team) {
                    $exists = DB::table('groups')
                        ->where('group_name', $team->group_name)
                        ->where('faculty_id', $team->faculty_id)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    DB::table('groups')->insert([
                        'group_name' => $team->group_name,
                        'faculty_id' => $team->faculty_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });
    }

    /**
     * "Team A" becomes team-a. A collision across faculties takes -2, -3 and so on, since
     * the column is unique across the whole table rather than per faculty.
     */
    private function backfillSlugs(): void
    {
        $taken = DB::table('groups')->whereNotNull('slug')->pluck('slug')->all();
        $taken = array_flip($taken);

        DB::table('groups')
            ->whereNull('slug')
            ->orderBy('group_id')
            ->select('group_id', 'group_name')
            ->chunkById(200, function ($groups) use (&$taken) {
                foreach ($groups as $group) {
                    $base = Str::slug((string) $group->group_name) ?: 'team';
                    $slug = $base;
                    $n = 2;
                    while (isset($taken[$slug])) {
                        $slug = $base . '-' . $n;
                        $n++;
                    }
                    $taken[$slug] = true;

                    DB::table('groups')
                        ->where('group_id', $group->group_id)
                        ->update(['slug' => $slug, 'updated_at' => now()]);
                }
            }, 'group_id');
    }
};
