<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Baseline schema — a single description of every table as it exists today.
 *
 * Replaces the 44 incremental migrations now archived in database/migrations_legacy/.
 * Those could not be replayed: five used ->change() without doctrine/dbal installed,
 * one issued raw MySQL DDL (ALTER ... MODIFY ... BIGINT UNSIGNED), half were data
 * backfills against data already in its final state, and three tables in the live
 * database had lost their migration files entirely.
 *
 * Deliberate differences from the MariaDB original, all PostgreSQL-driven:
 *
 *   enum(...)            -> string, validated in the application. Laravel renders a
 *                           Postgres enum as a CHECK constraint whose name it does not
 *                           control and which needs a drop/recreate to extend.
 *   unsigned integers    -> plain integers. PostgreSQL has no unsigned types; the
 *                           columns are all populated from id() values.
 *   json                 -> json, NOT jsonb. jsonb reorders object keys and strips
 *                           whitespace; every access here is a whole-column read or
 *                           write through an 'array' cast, so fidelity beats indexing.
 *   ON UPDATE current_timestamp() on users.email_verified_at is dropped — MySQL-only
 *                           column semantics; Postgres would need a trigger, and
 *                           nothing in the application relies on it.
 *
 * Every create is guarded by hasTable(), so this is also safe to run against the
 * existing MariaDB database: it finds all 25 tables present, creates nothing, and
 * simply records itself in the migrations table.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->createIdentityTables();
        $this->createTeamTables();
        $this->createTemplateTables();
        $this->createHotelTables();
        $this->createFrontDeskTables();

        $this->applyCaseInsensitiveColumns();
    }

    private function createIdentityTables(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('first_name')->nullable();
                $table->string('middle_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('email')->unique();
                $table->string('phone_number')->nullable();
                $table->string('avatar')->nullable();
                $table->timestamp('email_verified_at')->useCurrent();
                $table->string('password');
                $table->string('role')->default('student');
                $table->string('status')->default('active');
                $table->string('remember_token', 100);
                $table->timestamps();
                $table->timestamp('last_seen_at')->nullable();
            });
        }

        if (!Schema::hasTable('faculties')) {
            Schema::create('faculties', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('phone_number')->nullable();
                $table->string('status')->default('active');
                $table->string('block', 5)->nullable()->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('faculty_classes')) {
            Schema::create('faculty_classes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
                $table->string('name');
                $table->string('letter', 8);
                $table->smallInteger('capacity')->default(40);
                $table->string('status', 20)->default('open');
                $table->smallInteger('sort_order')->default(1);
                $table->timestamps();
                $table->unique(['faculty_id', 'letter']);
            });
        }

        if (!Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('faculty_id')->nullable()->constrained('faculties')->nullOnDelete();
                $table->foreignId('faculty_class_id')->nullable()->constrained('faculty_classes')->nullOnDelete();
                $table->string('student_id')->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('role', 32)->default('');
                $table->string('activity', 64);
                $table->string('description', 500)->default('');
                // Written once and never touched again — see ActivityLog::UPDATED_AT.
                $table->timestamp('created_at')->nullable();
                $table->index(['user_id', 'created_at']);
                $table->index('activity');
            });
        }
    }

    private function createTeamTables(): void
    {
        if (!Schema::hasTable('student_groups')) {
            Schema::create('student_groups', function (Blueprint $table) {
                $table->id();
                $table->string('group_name');
                $table->foreignId('faculty_id')->nullable()->constrained('faculties')->nullOnDelete();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                // Legacy single-role column; the real roles live in student_group_roles.
                $table->string('role')->nullable();
                $table->timestamps();
                $table->unique(['group_name', 'student_id']);
            });
        }

        if (!Schema::hasTable('student_group_roles')) {
            Schema::create('student_group_roles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_group_id')->constrained('student_groups')->cascadeOnDelete();
                $table->string('role');
                $table->timestamps();
                $table->unique(['student_group_id', 'role']);
            });
        }

        if (!Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
                $table->foreignId('assigned_to')->nullable()->constrained('users')->cascadeOnDelete();
                // Was enum(front_desk, restaurant_management, room_management,
                // maintenance, housekeeping) — validated in FacultyController.
                $table->string('role', 32);
                $table->string('title');
                $table->text('description')->nullable();
                $table->date('due_date')->nullable();
                $table->string('priority', 16)->default('medium');   // low | medium | high
                $table->string('status', 16)->default('active');     // active | archived
                $table->text('feedback')->nullable();
                $table->timestamp('feedback_at')->nullable();
                $table->foreignId('feedback_by')->nullable()->constrained('users')->nullOnDelete();
                $table->unsignedTinyInteger('revision_count')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('group_settings')) {
            Schema::create('group_settings', function (Blueprint $table) {
                $table->id();
                $table->string('group_name');
                $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
                $table->string('selected_template')->nullable();
                $table->boolean('is_published')->default(false);
                $table->timestamps();
                $table->unique(['group_name', 'faculty_id']);
            });
        }
    }

    private function createTemplateTables(): void
    {
        if (!Schema::hasTable('team_role_templates')) {
            Schema::create('team_role_templates', function (Blueprint $table) {
                $table->id();
                $table->string('group_name');
                $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
                $table->string('role', 64);
                $table->string('selected_template')->nullable();
                $table->boolean('is_published')->default(false);
                $table->integer('version')->default(1);
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['group_name', 'faculty_id', 'role'], 'team_role_templates_unique');
                $table->index(['faculty_id', 'group_name']);
            });
        }

        if (!Schema::hasTable('team_role_template_versions')) {
            Schema::create('team_role_template_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_role_template_id')->constrained('team_role_templates')->cascadeOnDelete();
                $table->integer('version');
                $table->string('selected_template')->nullable();
                $table->boolean('is_published')->default(false);
                $table->string('label')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['team_role_template_id', 'version'], 'team_role_template_versions_unique');
            });
        }

        if (!Schema::hasTable('team_template_edit_grants')) {
            Schema::create('team_template_edit_grants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
                $table->string('group_name');
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->string('role', 64);
                $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(
                    ['faculty_id', 'group_name', 'student_id', 'role'],
                    'team_template_edit_grants_unique'
                );
            });
        }

        // version_id 0 means "live"; anything else points at a saved version row.
        // Deliberately not a foreign key — see TemplateCustomizationStore::LIVE_VERSION_ID.
        if (!Schema::hasTable('template_layouts')) {
            Schema::create('template_layouts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_role_template_id')->constrained('team_role_templates')->cascadeOnDelete();
                $table->unsignedBigInteger('version_id')->default(0);
                $table->integer('sort_order')->default(0);
                $table->string('section_id', 120);
                $table->boolean('is_visible')->default(true);
                $table->timestamps();
                $table->index(['team_role_template_id', 'version_id'], 'tpl_layouts_template_version_idx');
                $table->index('version_id');
            });
        }

        if (!Schema::hasTable('template_elements')) {
            Schema::create('template_elements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_role_template_id')->constrained('team_role_templates')->cascadeOnDelete();
                $table->unsignedBigInteger('version_id')->default(0);
                // A CSS selector, occasionally long — text, and never indexed on its own.
                $table->text('element_key');
                $table->string('hms_id', 120)->nullable();
                $table->string('page', 64)->nullable();
                $table->boolean('free_position')->default(false);
                $table->string('move_mode', 40)->nullable();
                $table->boolean('keep_fixed')->default(false);
                $table->text('text_content')->nullable();
                $table->string('icon_class')->nullable();
                $table->string('display_value')->nullable();
                $table->string('image_src', 500)->nullable();
                $table->string('image_background', 500)->nullable();

                foreach (['color', 'background_color'] as $column) {
                    $table->string($column, 120)->nullable();
                }
                $table->string('font_family')->nullable();
                foreach (['font_weight', 'font_style', 'font_size', 'text_align', 'line_height', 'letter_spacing'] as $column) {
                    $table->string($column, 40)->nullable();
                }
                $table->string('text_decoration', 80)->nullable();
                foreach (['background_size', 'background_position'] as $column) {
                    $table->string($column, 80)->nullable();
                }
                $table->string('background_repeat', 40)->nullable();
                $table->string('padding', 80)->nullable();
                foreach (['padding_top', 'padding_right', 'padding_bottom', 'padding_left'] as $column) {
                    $table->string($column, 40)->nullable();
                }
                $table->string('margin', 80)->nullable();
                foreach (['margin_top', 'margin_right', 'margin_bottom', 'margin_left'] as $column) {
                    $table->string($column, 40)->nullable();
                }
                $table->string('border', 120)->nullable();
                $table->string('border_radius', 80)->nullable();
                $table->string('box_shadow')->nullable();
                $table->string('opacity', 20)->nullable();
                // left/right/bottom are reserved words, hence the _pos suffix.
                foreach (['position', 'top', 'left_pos', 'right_pos', 'bottom_pos', 'width', 'height', 'max_width', 'min_width', 'min_height'] as $column) {
                    $table->string($column, 40)->nullable();
                }
                $table->string('z_index', 20)->nullable();
                $table->string('transform')->nullable();
                foreach (['display', 'overflow'] as $column) {
                    $table->string($column, 40)->nullable();
                }
                $table->timestamps();

                $table->index(['team_role_template_id', 'version_id'], 'tpl_elements_template_version_idx');
                $table->index('version_id');
            });
        }

        if (!Schema::hasTable('template_images')) {
            Schema::create('template_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_role_template_id')->constrained('team_role_templates')->cascadeOnDelete();
                $table->unsignedBigInteger('version_id')->default(0);
                $table->text('element_key');
                $table->string('kind', 40)->default('src');
                // A path on the public disk. Raw base64 is never stored here.
                $table->string('image_path', 500);
                $table->timestamps();
                $table->index(['team_role_template_id', 'version_id'], 'tpl_images_template_version_idx');
                $table->index('version_id');
            });
        }

        if (!Schema::hasTable('template_content_items')) {
            Schema::create('template_content_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_role_template_id')->constrained('team_role_templates')->cascadeOnDelete();
                $table->unsignedBigInteger('version_id')->default(0);
                $table->string('collection', 64);
                $table->integer('sort_order')->default(0);
                $table->string('item_ref', 120)->nullable();
                // Self-referencing: nested content items (a card inside a section).
                $table->foreignId('parent_id')->nullable()->constrained('template_content_items')->cascadeOnDelete();
                $table->timestamps();
                $table->index(['team_role_template_id', 'version_id', 'collection'], 'tpl_content_collection_idx');
                $table->index('version_id');
            });
        }

        if (!Schema::hasTable('template_content_fields')) {
            Schema::create('template_content_fields', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_item_id')->constrained('template_content_items')->cascadeOnDelete();
                $table->string('field_name', 120);
                $table->longText('field_value')->nullable();
                $table->timestamps();
                $table->index(['content_item_id', 'field_name'], 'tpl_content_fields_idx');
            });
        }
    }

    private function createHotelTables(): void
    {
        // The hotel tables scope by (group_name, faculty_id) but carry no foreign key
        // on faculty_id — matching the live schema. Adding one needs an orphan audit
        // first, so it is deliberately left for a separate migration.
        if (!Schema::hasTable('hotel_customers')) {
            Schema::create('hotel_customers', function (Blueprint $table) {
                $table->id();
                $table->string('group_name');
                $table->unsignedBigInteger('faculty_id');
                $table->string('name');
                $table->string('email');
                $table->string('password');
                $table->timestamps();
                $table->unique(['group_name', 'faculty_id', 'email'], 'hotel_customers_team_email_unique');
                $table->index(['group_name', 'faculty_id']);
            });
        }

        if (!Schema::hasTable('hotel_rooms')) {
            Schema::create('hotel_rooms', function (Blueprint $table) {
                $table->id();
                $table->string('group_name');
                $table->unsignedBigInteger('faculty_id');
                $table->string('name');
                $table->string('category')->default('Classic');
                $table->string('status')->default('Available');
                $table->json('reservation')->nullable();
                $table->integer('price')->default(0);
                $table->text('description')->nullable();
                // A path on the public disk — see App\Support\HotelImageStore.
                $table->longText('image')->nullable();
                $table->timestamps();
                $table->index(['group_name', 'faculty_id']);
            });
        }

        if (!Schema::hasTable('hotel_menu_items')) {
            Schema::create('hotel_menu_items', function (Blueprint $table) {
                $table->id();
                $table->string('group_name');
                $table->unsignedBigInteger('faculty_id');
                $table->string('name');
                $table->string('category')->default('Main Dishes');
                $table->integer('price')->default(0);
                $table->integer('stock')->default(0);
                $table->string('description')->nullable();
                $table->longText('image')->nullable();
                $table->timestamps();
                $table->index(['group_name', 'faculty_id']);
            });
        }

        if (!Schema::hasTable('hotel_food_orders')) {
            Schema::create('hotel_food_orders', function (Blueprint $table) {
                $table->id();
                $table->string('group_name');
                $table->unsignedBigInteger('faculty_id');
                $table->string('room_number');
                $table->string('guest_name');
                // Line items: menu_item_id, name, price, qty.
                $table->json('items');
                $table->integer('total')->default(0);
                $table->string('status')->default('Preparing');
                $table->string('placed_by')->nullable();
                $table->timestamps();
                $table->index(['group_name', 'faculty_id']);
            });
        }
    }

    private function createFrontDeskTables(): void
    {
        // front_desk_canvases / front_desk_activities used to be rebuilt here too, but
        // they had no code referencing them anywhere and were empty on the live database.
        // Dropped by the 2026_08_05_000004 migration; not recreated on fresh installs.
        if (!Schema::hasTable('reservation_notifications')) {
            Schema::create('reservation_notifications', function (Blueprint $table) {
                $table->id();
                $table->string('group_name', 100);
                $table->unsignedBigInteger('faculty_id');
                $table->string('room_id', 100);
                $table->string('room_name')->nullable();
                $table->string('guest_name')->nullable();
                $table->date('check_in')->nullable();
                $table->date('check_out')->nullable();
                $table->boolean('acknowledged')->default(false);
                $table->timestamp('acknowledged_at')->nullable();
                $table->unsignedBigInteger('recorded_by')->nullable();
                $table->timestamps();
                $table->index(['group_name', 'faculty_id']);
                $table->index(['acknowledged', 'created_at']);
            });
        }
    }

    /**
     * MySQL's utf8mb4_unicode_ci compared these columns case-insensitively; PostgreSQL
     * compares case-sensitively, which would split tenants ("Team A" vs "team a") and
     * let duplicate accounts past the users.email unique index. citext restores the old
     * behaviour at the storage layer instead of touching ~25 comparison sites.
     *
     * Note the extension lands in the `extensions` schema on Supabase, so DB_SEARCH_PATH
     * must include it — otherwise the type name will not resolve at runtime.
     */
    private function applyCaseInsensitiveColumns(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // Named schema on purpose. A bare CREATE EXTENSION lands in the first schema on
        // the search path — `public` here — which Supabase flags as "Extension in Public"
        // because that is where the application's own tables live.
        DB::statement('CREATE SCHEMA IF NOT EXISTS extensions');
        DB::statement('CREATE EXTENSION IF NOT EXISTS citext WITH SCHEMA extensions');

        $columns = [
            'users' => ['email'],
            'student_groups' => ['group_name'],
            'group_settings' => ['group_name'],
            'team_role_templates' => ['group_name'],
            'hotel_menu_items' => ['name'],
        ];

        foreach ($columns as $table => $tableColumns) {
            foreach ($tableColumns as $column) {
                DB::statement(sprintf(
                    'ALTER TABLE %s ALTER COLUMN %s TYPE citext',
                    '"' . $table . '"',
                    '"' . $column . '"'
                ));
            }
        }
    }

    public function down(): void
    {
        // Children before parents.
        foreach ([
            'reservation_notifications',
            'hotel_food_orders',
            'hotel_menu_items',
            'hotel_rooms',
            'hotel_customers',
            'template_content_fields',
            'template_content_items',
            'template_images',
            'template_elements',
            'template_layouts',
            'team_template_edit_grants',
            'team_role_template_versions',
            'team_role_templates',
            'group_settings',
            'tasks',
            'student_group_roles',
            'student_groups',
            'activity_logs',
            'students',
            'faculty_classes',
            'faculties',
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
