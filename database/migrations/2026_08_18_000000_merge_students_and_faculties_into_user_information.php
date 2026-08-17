<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fold `students` and `faculties` into one table, `user_information`.
 *
 * Both were the same shape: one row per users row, holding the extra fields that only
 * that kind of account needs. `user_type` ('student' | 'faculty') now says which kind a
 * row is, and the columns that only apply to one kind are nullable:
 *
 *   faculty only   phone_number, status, block
 *   student only   faculty_class_id, student_number, faculty_id
 *
 * faculty_id keeps its meaning everywhere in the database — "the faculty this row
 * belongs to" — so on user_information it is a self-reference: a student row points at
 * its faculty's row, and a faculty row leaves it null. The primary key is
 * user_information_id, because one table cannot have two primary keys named faculty_id
 * and student_id (the same reason tasks.assigned_to and tasks.feedback_by keep
 * role-based names — see 2026_08_10_000000_rename_primary_keys_to_entity_ids).
 *
 * Identity handling — the one part that rewrites data:
 *
 *   faculties.faculty_id values are carried over unchanged. Roughly two dozen tables
 *   store a faculty_id, most of them hotel tables with no foreign key at all
 *   (hotel_rooms, hotel_bookings, hotel_concepts, reservation_notifications, ...).
 *   Preserving those ids means none of them are touched.
 *
 *   students.student_id values collide with that range, so they move up by a fixed
 *   offset — the highest existing faculty_id. Only three tables store a student_id
 *   (student_groups, tasks, team_template_edit_grants) and all three are shifted by the
 *   same amount, which is a bijection: unique indexes over student_id stay unique.
 *
 * down() restores two tables but not the original student numbering: the offset cannot
 * be recovered afterwards, because faculty rows created after the merge draw ids from
 * the shared sequence and land above the student range. Instead both tables take their
 * key straight from user_information_id, which leaves every child faculty_id/student_id
 * already pointing at the right row — so down() rewrites no child data at all.
 */
return new class extends Migration
{
    /** table => [column => on-delete rule] for every foreign key into `faculties`. */
    private const FACULTY_REFERENCES = [
        'faculty_classes'           => ['faculty_id' => 'cascade'],
        'student_groups'            => ['faculty_id' => 'null'],
        'tasks'                     => ['faculty_id' => 'cascade'],
        'group_settings'            => ['faculty_id' => 'cascade'],
        'team_role_templates'       => ['faculty_id' => 'cascade'],
        'team_template_edit_grants' => ['faculty_id' => 'cascade'],
        'groups'                    => ['faculty_id' => 'cascade'],
    ];

    /** table => [column => on-delete rule] for every foreign key into `students`. */
    private const STUDENT_REFERENCES = [
        'student_groups'            => ['student_id' => 'cascade'],
        'tasks'                     => ['student_id' => 'null'],
        'team_template_edit_grants' => ['student_id' => 'cascade'],
    ];

    public function up(): void
    {
        if (Schema::hasTable('user_information')) {
            return;
        }

        $this->createUserInformation();

        // Highest faculty_id in use. Student ids are lifted above it so the two key
        // spaces can share one column without overlapping.
        $offset = (int) (DB::table('faculties')->max('faculty_id') ?? 0);

        DB::statement(<<<'SQL'
            INSERT INTO user_information
                (user_information_id, user_type, user_id, phone_number, status, block, created_at, updated_at)
            SELECT faculty_id, 'faculty', user_id, phone_number, status, block, created_at, updated_at
            FROM faculties
        SQL);

        DB::insert(<<<'SQL'
            INSERT INTO user_information
                (user_information_id, user_type, user_id, faculty_id, faculty_class_id, student_number, created_at, updated_at)
            SELECT student_id + ?, 'student', user_id, faculty_id, faculty_class_id, student_number, created_at, updated_at
            FROM students
        SQL, [$offset]);

        $this->resyncSequence('user_information', 'user_information_id');

        // The old constraints have to go before any id moves, and before the tables
        // they point at can be dropped.
        $this->dropReferences(self::FACULTY_REFERENCES, 'faculties');
        $this->dropReferences(self::STUDENT_REFERENCES, 'students');

        if ($offset > 0) {
            foreach (self::STUDENT_REFERENCES as $table => $columns) {
                foreach (array_keys($columns) as $column) {
                    DB::update(sprintf(
                        'UPDATE %s SET %s = %s + ? WHERE %s IS NOT NULL',
                        $this->quote($table),
                        $this->quote($column),
                        $this->quote($column),
                        $this->quote($column)
                    ), [$offset]);
                }
            }
        }

        Schema::dropIfExists('students');
        Schema::dropIfExists('faculties');

        Schema::table('user_information', function (Blueprint $table) {
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('faculty_id')->references('user_information_id')->on('user_information')->nullOnDelete();
            $table->foreign('faculty_class_id')->references('faculty_class_id')->on('faculty_classes')->nullOnDelete();
        });

        $this->addReferences(self::FACULTY_REFERENCES, 'user_information', 'user_information_id');
        $this->addReferences(self::STUDENT_REFERENCES, 'user_information', 'user_information_id');

        // 2026_08_05_000001 closed the Supabase REST API off table by table and this one
        // did not exist yet. Its revoked default privileges already cover the grants;
        // this adds the second layer the Security Advisor looks for.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "user_information" ENABLE ROW LEVEL SECURITY');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('user_information')) {
            return;
        }

        $this->dropReferences(self::FACULTY_REFERENCES, 'user_information');
        $this->dropReferences(self::STUDENT_REFERENCES, 'user_information');

        foreach ([
            'user_id' => 'users',
            'faculty_id' => 'user_information',
            'faculty_class_id' => 'faculty_classes',
        ] as $column => $referencedTable) {
            $this->dropForeignKey('user_information', $column, $referencedTable);
        }

        if (!Schema::hasTable('faculties')) {
            Schema::create('faculties', function (Blueprint $table) {
                $table->bigIncrements('faculty_id');
                $table->foreignId('user_id')->constrained(null, 'user_id')->cascadeOnDelete();
                $table->string('phone_number')->nullable();
                $table->string('status')->default('active');
                $table->string('block', 5)->nullable()->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table) {
                $table->bigIncrements('student_id');
                $table->foreignId('user_id')->constrained(null, 'user_id')->cascadeOnDelete();
                $table->unsignedBigInteger('faculty_id')->nullable();
                $table->unsignedBigInteger('faculty_class_id')->nullable();
                // Nullable where the original was NOT NULL: a row that lost its number
                // is not a reason for a rollback to abort.
                $table->string('student_number')->nullable()->unique();
                $table->timestamps();

                $table->foreign('faculty_id')->references('faculty_id')->on('faculties')->nullOnDelete();
                $table->foreign('faculty_class_id')->references('faculty_class_id')->on('faculty_classes')->nullOnDelete();
            });
        }

        // Both keys come straight from user_information_id, so every faculty_id and
        // student_id already stored in the child tables still resolves.
        DB::statement(<<<'SQL'
            INSERT INTO faculties (faculty_id, user_id, phone_number, status, block, created_at, updated_at)
            SELECT user_information_id, user_id, phone_number, status, block, created_at, updated_at
            FROM user_information
            WHERE user_type = 'faculty'
        SQL);

        DB::statement(<<<'SQL'
            INSERT INTO students (student_id, user_id, faculty_id, faculty_class_id, student_number, created_at, updated_at)
            SELECT user_information_id, user_id, faculty_id, faculty_class_id, student_number, created_at, updated_at
            FROM user_information
            WHERE user_type = 'student'
        SQL);

        $this->resyncSequence('faculties', 'faculty_id');
        $this->resyncSequence('students', 'student_id');

        Schema::dropIfExists('user_information');

        $this->addReferences(self::FACULTY_REFERENCES, 'faculties', 'faculty_id');
        $this->addReferences(self::STUDENT_REFERENCES, 'students', 'student_id');
    }

    private function createUserInformation(): void
    {
        Schema::create('user_information', function (Blueprint $table) {
            $table->bigIncrements('user_information_id');
            // 'student' | 'faculty'. users.role stays authoritative for authorisation;
            // this says which set of columns the row fills in.
            $table->string('user_type', 16);
            $table->unsignedBigInteger('user_id');
            // Self-reference, added as a constraint once the rows are in: a student's
            // faculty. Null on faculty rows.
            $table->unsignedBigInteger('faculty_id')->nullable();
            $table->unsignedBigInteger('faculty_class_id')->nullable();
            // School-issued number. Unique among students; null on faculty rows, and
            // PostgreSQL lets a unique index hold any number of nulls.
            $table->string('student_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('status')->default('active');
            $table->string('block', 5)->nullable();
            $table->timestamps();

            $table->unique('student_number');
            $table->unique('block');
            $table->index('user_id');
            // Every read is scoped to one kind of account, usually within one faculty.
            $table->index(['user_type', 'faculty_id']);
        });
    }

    private function dropReferences(array $references, string $referencedTable): void
    {
        foreach ($references as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach (array_keys($columns) as $column) {
                $this->dropForeignKey($table, $column, $referencedTable);
            }
        }
    }

    /**
     * Drops the foreign key on $table.$column that points at $referencedTable.
     *
     * The name is read out of the catalog rather than assumed to be Laravel's
     * <table>_<column>_foreign, and nothing here is wrapped in a try/catch on purpose:
     * PostgreSQL aborts the entire surrounding transaction on a failed DROP CONSTRAINT,
     * and the migrator runs this whole migration inside one, so a swallowed error would
     * poison every statement after it instead of being harmless. Looking the name up
     * first means there is nothing to swallow.
     *
     * $referencedTable is part of the lookup so a column with more than one meaning is
     * never hit by accident — student_groups.group_id, for instance, must survive.
     */
    private function dropForeignKey(string $table, string $column, string $referencedTable): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            // MySQL has no transactional DDL, so the conventional name plus a guard is
            // both the best available option and harmless here.
            try {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropForeign([$column]));
            } catch (\Throwable $e) {
                report($e);
            }

            return;
        }

        $constraints = DB::select(<<<'SQL'
            SELECT con.conname AS name
            FROM pg_constraint con
            JOIN pg_class src ON src.oid = con.conrelid
            JOIN pg_class tgt ON tgt.oid = con.confrelid
            JOIN pg_attribute att ON att.attrelid = con.conrelid AND att.attnum = con.conkey[1]
            WHERE con.contype = 'f'
              AND src.relnamespace = 'public'::regnamespace
              AND src.relname = ?
              AND tgt.relname = ?
              AND att.attname = ?
              AND array_length(con.conkey, 1) = 1
        SQL, [$table, $referencedTable, $column]);

        foreach ($constraints as $constraint) {
            DB::statement(sprintf(
                'ALTER TABLE %s DROP CONSTRAINT %s',
                $this->quote($table),
                $this->quote($constraint->name)
            ));
        }
    }

    private function addReferences(array $references, string $onTable, string $onColumn): void
    {
        foreach ($references as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($columns, $onTable, $onColumn) {
                foreach ($columns as $column => $onDelete) {
                    $foreign = $blueprint->foreign($column)->references($onColumn)->on($onTable);

                    $onDelete === 'null'
                        ? $foreign->nullOnDelete()
                        : $foreign->cascadeOnDelete();
                }
            });
        }
    }

    /**
     * bigIncrements owns a sequence that starts at 1, but the rows were inserted with
     * explicit ids. Without this the next insert collides with row 1.
     */
    private function resyncSequence(string $table, string $column): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(sprintf(
            "SELECT setval(
                pg_get_serial_sequence('%s', '%s'),
                COALESCE((SELECT MAX(%s) FROM %s), 1),
                (SELECT COUNT(*) > 0 FROM %s)
            )",
            $table,
            $column,
            $this->quote($column),
            $this->quote($table),
            $this->quote($table)
        ));
    }

    private function quote(string $identifier): string
    {
        return DB::getDriverName() === 'mysql'
            ? '`' . $identifier . '`'
            : '"' . $identifier . '"';
    }
};
