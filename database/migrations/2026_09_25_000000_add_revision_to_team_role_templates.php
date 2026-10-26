<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A counter that goes up every time a role's template content is written.
 *
 * The builder sends back the revision it last loaded, and a save that finds a
 * different one on the row is refused with a conflict instead of writing over
 * a teammate's newer work. updated_at cannot do this job: it is stored to the
 * second, and a team autosaving every seven seconds lands two writes in the
 * same second often enough to matter.
 *
 * It also drives team sync: the sum across a team's rows changes on every
 * write, where the newest updated_at did not change for a second write inside
 * the same second.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('team_role_templates')) {
            return;
        }

        Schema::table('team_role_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('team_role_templates', 'revision')) {
                $table->unsignedBigInteger('revision')->default(0);
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('team_role_templates')) {
            return;
        }

        Schema::table('team_role_templates', function (Blueprint $table) {
            if (Schema::hasColumn('team_role_templates', 'revision')) {
                $table->dropColumn('revision');
            }
        });
    }
};
