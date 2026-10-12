<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Empties the database for a fresh round of user testing: every account except the
 * dean's is deleted, and every other table is emptied. The schema, the migrations
 * table and the dean login stay, so the site works exactly as before, just with
 * nothing in it. Hotel defaults (rooms, menu, amenities) and task checklists live in
 * code and re-seed themselves on a team's first visit.
 *
 * Runs in one transaction: it either finishes or changes nothing. Uploaded files on
 * disk or in storage are not touched.
 *
 * WARNING: .env points at the live Supabase database, so this wipes production.
 */
class ResetForTesting extends Command
{
    protected $signature = 'hms:reset-for-testing {--force : Skip the confirmation prompt}';

    protected $description = 'Delete every account except the dean and empty every other table';

    /**
     * Kept out of the TRUNCATE. faculty_classes is emptied with DELETE instead because
     * user_information (which holds the dean's row) references it, and PostgreSQL
     * refuses to TRUNCATE a table referenced from a table that is not truncated with it.
     */
    private const KEEP = ['migrations', 'users', 'user_information', 'faculty_classes'];

    public function handle(): int
    {
        $deans = DB::table('users')->where('role', 'dean')->pluck('email', 'user_id');
        // By id, not role <> 'dean': a NULL role would slip past that comparison.
        $deanIds = $deans->keys()->all();
        if ($deans->isEmpty()) {
            $this->error('No dean account found. Nothing was changed.');

            return self::FAILURE;
        }

        $tables = collect(DB::select(
            "SELECT table_name FROM information_schema.tables
             WHERE table_schema = current_schema() AND table_type = 'BASE TABLE'"
        ))->pluck('table_name')->diff(self::KEEP)->values();

        $this->line('Database: ' . DB::connection()->getDatabaseName() . ' on ' . config('database.connections.' . DB::getDefaultConnection() . '.host'));
        $this->line('Keeping: ' . $deans->implode(', '));
        $this->line('Deleting ' . DB::table('users')->whereNotIn('user_id', $deanIds)->count() . ' other account(s) and emptying: ' . $tables->implode(', ') . ', faculty_classes');

        if (!$this->option('force') && !$this->confirm('This cannot be undone. Continue?')) {
            return self::FAILURE;
        }

        DB::transaction(function () use ($tables, $deanIds) {
            // Truncate first, so no emptied table still points at a user when the
            // accounts go. Then only user_information is left referencing users, and
            // its foreign key cascades.
            DB::statement('TRUNCATE TABLE ' . $tables->map(fn ($t) => '"' . $t . '"')->implode(', ') . ' RESTART IDENTITY');

            DB::table('users')->whereNotIn('user_id', $deanIds)->delete();

            DB::table('faculty_classes')->delete();

            if (DB::table('users')->whereNotIn('user_id', $deanIds)->exists()) {
                throw new \RuntimeException('Non-dean accounts survived the wipe. Rolled back.');
            }
        });

        $this->info('Done. Only the dean account is left.');

        return self::SUCCESS;
    }
}
