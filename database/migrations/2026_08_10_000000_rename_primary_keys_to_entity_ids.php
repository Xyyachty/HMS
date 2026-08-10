<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Give every primary key a name that says what it identifies: users.id becomes
 * users.user_id, tasks.id becomes tasks.task_id, and so on. A join now reads
 * students.student_id = tasks.student_id instead of students.id = tasks.student_id,
 * and a column named student_id means the same thing in every table that has one.
 *
 * Two tables are deliberately left alone. sessions.id is read and written by
 * Illuminate\Session\DatabaseSessionHandler, which hardcodes the name — renaming it
 * logs out every user and cannot be fixed from application code. migrations.id belongs
 * to the migrator itself, which would stop being able to record this very migration.
 *
 * students needed one extra step. It already had a student_id column holding the
 * school-issued student number, so the primary key had nowhere to land. That column is
 * renamed to student_number first, which also removes a long-standing ambiguity: the
 * same name meant "school number" on students and "foreign key to students" on tasks,
 * student_groups and team_template_edit_grants.
 *
 * Foreign keys named for their role rather than their table are untouched — tasks
 * carries both assigned_to and feedback_by pointing at users, and a table cannot have
 * two columns called user_id. Same for updated_by, created_by, granted_by, recorded_by.
 * template_*.version_id also stays: 0 means "live" there, so it is not a real foreign
 * key (see TemplateCustomizationStore::LIVE_VERSION_ID).
 *
 * The rename is metadata-only in PostgreSQL. Foreign key constraints, indexes, and
 * sequence ownership all reference the column by identity, not by name, so they follow
 * automatically and no row is rewritten.
 */
return new class extends Migration
{
    /**
     * table => primary key column name. Order is irrelevant; a column rename cannot
     * break a dependent constraint the way a drop-and-add would.
     */
    private const PRIMARY_KEYS = [
        'users' => 'user_id',
        'faculties' => 'faculty_id',
        'faculty_classes' => 'faculty_class_id',
        'students' => 'student_id',
        'activity_logs' => 'activity_log_id',
        'groups' => 'group_id',
        'student_groups' => 'student_group_id',
        'student_group_roles' => 'student_group_role_id',
        'tasks' => 'task_id',
        'group_settings' => 'group_setting_id',
        'team_role_templates' => 'team_role_template_id',
        'team_role_template_versions' => 'team_role_template_version_id',
        'team_template_edit_grants' => 'team_template_edit_grant_id',
        'template_layouts' => 'template_layout_id',
        'template_elements' => 'template_element_id',
        'template_images' => 'template_image_id',
        'template_content_items' => 'template_content_item_id',
        'template_content_fields' => 'template_content_field_id',
        'hotel_customers' => 'hotel_customer_id',
        'hotel_rooms' => 'hotel_room_id',
        'hotel_menu_items' => 'hotel_menu_item_id',
        'hotel_food_orders' => 'hotel_food_order_id',
        'reservation_notifications' => 'reservation_notification_id',
        'user_notifications' => 'user_notification_id',
    ];

    /**
     * Foreign keys whose name no longer matches the primary key they point at.
     * table => [old column => new column].
     */
    private const FOREIGN_KEYS = [
        'template_content_fields' => ['content_item_id' => 'template_content_item_id'],
    ];

    public function up(): void
    {
        // students.student_id holds the school number and is standing on the name the
        // primary key needs. It has to move before students.id can take its place.
        $this->rename('students', 'student_id', 'student_number');
        $this->renameIndex('students_student_id_unique', 'students_student_number_unique');

        foreach (self::PRIMARY_KEYS as $table => $column) {
            $this->rename($table, 'id', $column);
        }

        foreach (self::FOREIGN_KEYS as $table => $columns) {
            foreach ($columns as $from => $to) {
                $this->rename($table, $from, $to);
            }
        }
    }

    public function down(): void
    {
        foreach (self::FOREIGN_KEYS as $table => $columns) {
            foreach ($columns as $from => $to) {
                $this->rename($table, $to, $from);
            }
        }

        foreach (self::PRIMARY_KEYS as $table => $column) {
            $this->rename($table, $column, 'id');
        }

        // Only safe once students.student_id has been handed back to the primary key.
        $this->renameIndex('students_student_number_unique', 'students_student_id_unique');
        $this->rename('students', 'student_number', 'student_id');
    }

    /**
     * Guarded so the migration is idempotent and survives a partially applied run:
     * a missing table, an already-renamed column, or a name that is somehow taken
     * is skipped rather than aborting the batch halfway through.
     */
    private function rename(string $table, string $from, string $to): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        if (!Schema::hasColumn($table, $from) || Schema::hasColumn($table, $to)) {
            return;
        }

        DB::statement(sprintf(
            'ALTER TABLE %s RENAME COLUMN %s TO %s',
            $this->quote($table),
            $this->quote($from),
            $this->quote($to)
        ));
    }

    /**
     * Cosmetic, and PostgreSQL-only — MySQL has no ALTER INDEX ... RENAME and names
     * indexes per table anyway. Wrapped because an index missing under this exact name
     * (renamed by hand, or created before the unique constraint was added) is not a
     * reason to fail the whole migration.
     */
    private function renameIndex(string $from, string $to): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        try {
            DB::statement(sprintf('ALTER INDEX IF EXISTS %s RENAME TO %s', $this->quote($from), $this->quote($to)));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function quote(string $identifier): string
    {
        return DB::getDriverName() === 'mysql'
            ? '`' . $identifier . '`'
            : '"' . $identifier . '"';
    }
};
