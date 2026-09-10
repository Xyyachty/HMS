<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The line a team's hotel introduces itself with.
 *
 * The concept carried a name, a type and a description, and the site's tagline —
 * the small line above the headline on the landing page and in the footer — was
 * filled with the type's label for want of anything better, so every boutique
 * hotel in the class read "Boutique Hotel". A team writes its own now, at the
 * point where the rest of the identity is decided.
 *
 * Nullable, and on the revisions table too: a concept written before this has
 * none, and a revision is a snapshot of the fields as they stood.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_concepts') && !Schema::hasColumn('hotel_concepts', 'tagline')) {
            Schema::table('hotel_concepts', function (Blueprint $table) {
                $table->string('tagline', 160)->nullable()->after('title');
            });
        }

        if (Schema::hasTable('hotel_concept_revisions') && !Schema::hasColumn('hotel_concept_revisions', 'tagline')) {
            Schema::table('hotel_concept_revisions', function (Blueprint $table) {
                $table->string('tagline', 160)->nullable()->after('title');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('hotel_concepts') && Schema::hasColumn('hotel_concepts', 'tagline')) {
            Schema::table('hotel_concepts', function (Blueprint $table) {
                $table->dropColumn('tagline');
            });
        }

        if (Schema::hasTable('hotel_concept_revisions') && Schema::hasColumn('hotel_concept_revisions', 'tagline')) {
            Schema::table('hotel_concept_revisions', function (Blueprint $table) {
                $table->dropColumn('tagline');
            });
        }
    }
};
