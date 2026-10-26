<?php

namespace App\Support;

use App\Models\HotelBooking;
use App\Models\HotelCustomer;
use App\Models\HotelGuest;
use App\Models\StudentGroup;

/**
 * The stay behind a guest signed into the hotel website.
 *
 * A guest account (hotel_customers) and a stay (hotel_bookings, through
 * hotel_guests) are recorded by different desks and are not joined by a key: the
 * account is made on the site, the stay is written at the front desk from the ID
 * the guest handed over. What ties them is the email, which is the one detail
 * both desks take, so it is what is matched on here.
 *
 * Booking a facility asks one thing of that stay: that it is happening now.
 * Somebody who has an account but has not arrived, or who has checked out, is a
 * visitor reading the page — the pool and the spa are for the people in the
 * building, and the front desk's own screens make the same distinction.
 */
class HotelGuestStay
{
    /**
     * The signed-in guest's current stay, or null when there is not one.
     *
     * Null covers every reason at once: nobody signed in, signed in as staff, an
     * account with no stay against it, a stay still to arrive, and a stay already
     * closed. The callers need no more than "may they book?".
     */
    public static function checkedInBooking(?StudentGroup $membership = null): ?HotelBooking
    {
        $auth = HotelSimulationAuth::current();
        if (!is_array($auth) || ($auth['type'] ?? null) !== 'customer') {
            return null;
        }

        $membership = $membership ?: HotelAmenityAccess::membership();
        if (!$membership) {
            return null;
        }

        $customer = HotelCustomer::where('hotel_customer_id', $auth['customer_id'] ?? 0)
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->first();

        if (!$customer || blank($customer->email)) {
            return null;
        }

        // Every guest row this account could be: a repeat visitor has one row, but a
        // desk that mistyped a name once has two, and either may hold the stay.
        $guestIds = HotelGuest::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->whereRaw('LOWER(email) = LOWER(?)', [$customer->email])
            ->pluck('hotel_guest_id');

        if ($guestIds->isEmpty()) {
            return null;
        }

        return HotelBooking::with('room')
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->whereIn('hotel_guest_id', $guestIds)
            ->where('status', 'Checked In')
            ->orderByDesc('hotel_booking_id')
            ->first();
    }

    /** Whether the signed-in guest may book a facility right now. */
    public static function canBookAmenities(?StudentGroup $membership = null): bool
    {
        return self::checkedInBooking($membership) !== null;
    }

    /**
     * What the website needs to draw the booking controls: whether they are open,
     * and the room to show against them so a guest can see which stay they are
     * booking on.
     *
     * @return array{checked_in: bool, booking_id: int|null, room: string|null, guest_name: string|null}
     */
    public static function payload(?StudentGroup $membership = null): array
    {
        $booking = self::checkedInBooking($membership);

        return [
            'checked_in' => $booking !== null,
            'booking_id' => $booking?->hotel_booking_id,
            'room' => $booking?->room?->name,
            'guest_name' => $booking?->guest?->full_name,
        ];
    }
}
