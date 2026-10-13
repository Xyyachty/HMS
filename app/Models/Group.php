<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Group extends Model
{
    protected $primaryKey = 'group_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'slug',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id', 'user_information_id');
    }

    public function studentGroups()
    {
        return $this->hasMany(StudentGroup::class, 'group_id', 'group_id');
    }

    /**
     * The team behind a public URL, as the (group_name, faculty_id) pair every other query
     * in this app keys off.
     *
     * This is the only way into the system that does not start from auth()->user() — a
     * visitor to a team's hotel site has no login and no membership, so the slug is the
     * whole of their identity for the request.
     *
     * @return array{group_name: string, faculty_id: int}|null
     */
    public static function resolveSlug(?string $slug): ?array
    {
        $slug = trim((string) $slug);
        if ($slug === '') {
            return null;
        }

        $group = self::where('slug', $slug)->first();
        if (!$group) {
            return null;
        }

        return [
            'group_name' => (string) $group->group_name,
            'faculty_id' => (int) $group->faculty_id,
        ];
    }

    /**
     * A free slug for a team that has none, derived from its name.
     *
     * Only ever called to fill a gap. A team that already has a slug keeps it, including
     * through a rename — a link somebody has shared must not stop working because the team
     * changed what it calls itself.
     */
    public static function slugFor(string $groupName): string
    {
        $base = Str::slug($groupName) ?: 'team';
        $slug = $base;
        $n = 2;

        while (self::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $n;
            $n++;
        }

        return $slug;
    }

    /**
     * Every table that owns rows by team: the ones carrying faculty_id and group_name.
     *
     * Read from the schema rather than listed, so a table added later follows a team
     * through a rename or a handover without anyone remembering to add it here.
     *
     * @return array<int, string>
     */
    public static function teamOwnedTables(): array
    {
        // On PostgreSQL getTables() lists every schema (Supabase's auth and
        // storage too); only the app's own tables are team-owned.
        $schema = DB::getDriverName() === 'pgsql' ? DB::scalar('select current_schema()') : null;
        $tables = [];

        foreach (Schema::getTables() as $table) {
            if ($schema !== null && ($table['schema'] ?? null) !== $schema) {
                continue;
            }
            $columns = Schema::getColumnListing($table['name']);
            if (in_array('faculty_id', $columns, true) && in_array('group_name', $columns, true)) {
                $tables[] = $table['name'];
            }
        }

        return $tables;
    }

    /** The team's slug, minting one on first ask if the row predates the column. */
    public static function slugForTeam(string $groupName, int $facultyId): ?string
    {
        $group = self::where('group_name', $groupName)->where('faculty_id', $facultyId)->first();
        if (!$group) {
            return null;
        }

        if (!$group->slug) {
            $group->slug = self::slugFor($groupName);
            $group->save();
        }

        return $group->slug;
    }
}
