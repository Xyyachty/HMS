<?php

use App\Models\HotelGuest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Cleans up what HotelGuest::findOrCreateFor() used to leave behind.
 *
 * It matched the name exactly and only on the one detail it was given first, so a guest
 * who booked online with an email and then gave an ID at the desk became two guests, and
 * Guest Information listed them twice. Guests on one team with the same name are merged
 * here by the rule findOrCreateFor() follows now: one shared ID, email or phone, or no
 * details on one side to tell them apart. Two different ID numbers are never merged.
 *
 * No booking or payment is removed: a merged guest's bookings move to the guest kept,
 * which takes over any detail it was missing, and only the empty guest row goes.
 *
 * Then the old multi-room payments: the walk-in desk split one payment over its rooms
 * with no link between the shares. Shares taken in the same second, by the same method
 * and reference, for one guest's different bookings, get one transaction_ref.
 *
 * Not reversible: the guest rows merged away are not kept.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hotel_guests') || !Schema::hasColumn('hotel_booking_payments', 'transaction_ref')) {
            return;
        }

        DB::transaction(function () {
            $this->mergeGuests();
            $this->linkSplitPayments();
        });
    }

    public function down(): void
    {
        // Nothing to undo that would be worth having: the bookings are all still there.
    }

    private function mergeGuests(): void
    {
        $byName = DB::table('hotel_guests')->orderBy('hotel_guest_id')->get()
            ->groupBy(fn ($g) => $g->group_name . '|' . $g->faculty_id . '|' . mb_strtolower(trim((string) $g->full_name)));

        foreach ($byName as $rows) {
            $keepers = [];
            foreach ($rows as $row) {
                $row = (array) $row;
                $into = null;
                foreach ($keepers as $i => $keeper) {
                    if (HotelGuest::sharesDetail($keeper, $row) || !HotelGuest::hasDetails($keeper) || !HotelGuest::hasDetails($row)) {
                        $into = $i;
                        break;
                    }
                }
                if ($into === null) {
                    $keepers[] = $row;
                    continue;
                }

                $keepId = $keepers[$into]['hotel_guest_id'];
                DB::table('hotel_bookings')
                    ->where('hotel_guest_id', $row['hotel_guest_id'])
                    ->update(['hotel_guest_id' => $keepId]);

                $fill = [];
                foreach (HotelGuest::IDENTIFIERS as $field) {
                    if (blank($keepers[$into][$field]) && !blank($row[$field])) {
                        $fill[$field] = $keepers[$into][$field] = $row[$field];
                    }
                }
                if ($fill) {
                    DB::table('hotel_guests')->where('hotel_guest_id', $keepId)->update($fill);
                }

                DB::table('hotel_guests')->where('hotel_guest_id', $row['hotel_guest_id'])->delete();
            }
        }
    }

    private function linkSplitPayments(): void
    {
        $shares = DB::table('hotel_booking_payments as p')
            ->join('hotel_bookings as b', 'b.hotel_booking_id', '=', 'p.hotel_booking_id')
            ->whereNull('p.transaction_ref')
            ->get(['p.hotel_booking_payment_id', 'p.hotel_booking_id', 'p.method', 'p.reference', 'p.paid_at', 'b.hotel_guest_id'])
            ->groupBy(fn ($p) => $p->hotel_guest_id . '|' . $p->method . '|' . $p->reference . '|' . substr((string) $p->paid_at, 0, 19));

        foreach ($shares as $payment) {
            if ($payment->pluck('hotel_booking_id')->unique()->count() > 1) {
                DB::table('hotel_booking_payments')
                    ->whereIn('hotel_booking_payment_id', $payment->pluck('hotel_booking_payment_id'))
                    ->update(['transaction_ref' => (string) Str::uuid()]);
            }
        }
    }
};
