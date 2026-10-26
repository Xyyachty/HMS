<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Promos and packages the Front Desk runs during the Simulation. The team's
 * site shows the active ones that have not passed their end date in place of
 * the Promos and Packages section's starting cards. See App\Models\HotelPromo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotel_promos')) {
            return;
        }

        Schema::create('hotel_promos', function (Blueprint $table) {
            $table->id('hotel_promo_id');
            $table->string('group_name');
            $table->unsignedBigInteger('faculty_id');
            $table->foreignId('group_id')->nullable()->constrained('groups', 'group_id')->nullOnDelete();
            $table->string('name');
            $table->text('description');
            $table->string('image')->nullable();
            // Whole pesos, like hotel_rooms.price and hotel_addons.price.
            $table->unsignedInteger('original_price')->nullable();
            $table->unsignedInteger('promo_price');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['faculty_id', 'group_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_promos');
    }
};
