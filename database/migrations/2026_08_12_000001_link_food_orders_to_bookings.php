<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ties a room-service order to the stay it was ordered against.
 *
 * The order already recorded a room_number and a guest_name, but those are just
 * strings copied off the room at the time — they cannot survive a rename and cannot
 * be joined, so there was no way to total up what a guest ordered during their stay.
 * The booking id makes that a one-line query, which is what lets the room-service
 * charges land on the final bill at check-out.
 *
 * Dine-in orders keep a null booking: nobody is staying in a restaurant table.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('hotel_food_orders', 'hotel_booking_id')) {
            Schema::table('hotel_food_orders', function (Blueprint $table) {
                // nullOnDelete rather than cascade: deleting a stay should not take the
                // kitchen's record of what it cooked with it.
                $table->foreignId('hotel_booking_id')
                    ->nullable()
                    ->after('order_type')
                    ->constrained('hotel_bookings', 'hotel_booking_id')
                    ->nullOnDelete();
                // "what did this stay order" — the only lookup this column exists for.
                $table->index('hotel_booking_id');
            });
        }

        $this->backfillFromOpenBookings();
    }

    public function down(): void
    {
        if (Schema::hasColumn('hotel_food_orders', 'hotel_booking_id')) {
            Schema::table('hotel_food_orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('hotel_booking_id');
            });
        }
    }

    /**
     * Existing room-service orders predate the column. Match each one back to a stay
     * on the only thing both rows carry: the team, the room's name and the guest's
     * name. Anything ambiguous is left null rather than guessed at — a wrong link
     * would put someone else's dinner on a guest's bill.
     */
    private function backfillFromOpenBookings(): void
    {
        $orders = DB::table('hotel_food_orders')
            ->whereNull('hotel_booking_id')
            ->where('order_type', 'room_service')
            ->whereNotNull('room_number')
            ->get();

        foreach ($orders as $order) {
            $matches = DB::table('hotel_bookings')
                ->join('hotel_rooms', 'hotel_rooms.hotel_room_id', '=', 'hotel_bookings.hotel_room_id')
                ->join('hotel_guests', 'hotel_guests.hotel_guest_id', '=', 'hotel_bookings.hotel_guest_id')
                ->where('hotel_bookings.group_name', $order->group_name)
                ->where('hotel_bookings.faculty_id', $order->faculty_id)
                ->where('hotel_rooms.name', $order->room_number)
                ->where('hotel_guests.full_name', $order->guest_name)
                // The stay had to already exist when the order was taken.
                ->where('hotel_bookings.created_at', '<=', $order->created_at)
                ->orderByDesc('hotel_bookings.hotel_booking_id')
                ->limit(2)
                ->pluck('hotel_bookings.hotel_booking_id');

            if ($matches->count() !== 1) {
                continue;
            }

            DB::table('hotel_food_orders')
                ->where('hotel_food_order_id', $order->hotel_food_order_id)
                ->update(['hotel_booking_id' => $matches->first()]);
        }
    }
};
