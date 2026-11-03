<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who pays what for a child, as each hotel decides it (App\Models\HotelChildPolicy),
 * and the children on each stay.
 *
 * hotel_bookings.children is a snapshot like room_rate: one entry per child with the
 * age given and the nightly rate the policy set then, so a policy changed later does
 * not move a sold stay. guest_count stays the whole party, children included, which
 * is what the capacity checks already read. Bookings taken before this stay null and
 * bill exactly as they did. Nothing is backfilled.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hotel_child_policies')) {
            Schema::create('hotel_child_policies', function (Blueprint $table) {
                $table->id('hotel_child_policy_id');
                $table->string('group_name');
                $table->unsignedBigInteger('faculty_id');
                $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
                // From this age a guest is priced as an adult.
                $table->unsignedTinyInteger('adult_age')->default(12);
                // Children who fit in the room's own beds stay free; only a child past
                // the room's limit pays their age rate.
                $table->boolean('free_with_existing_bedding')->default(false);
                // [{min_age, max_age, rate}] — rate is whole pesos per night, 0 = free.
                $table->json('bands')->nullable();
                $table->timestamps();

                $table->unique(['faculty_id', 'group_name']);
            });
        }

        if (Schema::hasTable('hotel_bookings') && !Schema::hasColumn('hotel_bookings', 'children')) {
            Schema::table('hotel_bookings', function (Blueprint $table) {
                $table->json('children')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hotel_bookings', 'children')) {
            Schema::table('hotel_bookings', function (Blueprint $table) {
                $table->dropColumn('children');
            });
        }
        Schema::dropIfExists('hotel_child_policies');
    }
};
