<?php

use App\Models\HotelAmenity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * The seeded facilities never got the access type they were described by.
 *
 * HotelAmenity's own docblock says the pool and the gym keep an entry register,
 * the spa is booked by appointment and the function room by the event — but
 * seedDefaults() wrote none of it, so every row fell to 'open', the one type
 * nothing can be booked under. It went unnoticed while only Front Desk booked
 * facilities, from a screen that asks which kind it is; it stops a guest booking
 * anything at all from the site.
 *
 * Only rows still carrying the seeded name and still 'open' are touched: a team
 * that renamed the Spa, or deliberately set a facility to open access, has made a
 * decision this has no business overruling.
 */
return new class extends Migration
{
    private const SEEDED = [
        'Swimming Pool' => 'registered',
        'Gym'           => 'registered',
        'Spa'           => 'appointment',
        'Function Room' => 'event',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('hotel_amenities')) {
            return;
        }

        foreach (self::SEEDED as $name => $accessType) {
            HotelAmenity::where('name', $name)
                ->where('access_type', 'open')
                ->update(['access_type' => $accessType]);
        }

        // An event booking is refused when the party is larger than the hall, and a
        // hall with no capacity at all cannot refuse anything.
        HotelAmenity::where('name', 'Function Room')
            ->whereNull('capacity')
            ->update(['capacity' => 80]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('hotel_amenities')) {
            return;
        }

        // Deliberately not reversed: putting these back to 'open' would undo a
        // team's own edits as readily as this migration's, and 'open' is the value
        // that was wrong in the first place.
    }
};
