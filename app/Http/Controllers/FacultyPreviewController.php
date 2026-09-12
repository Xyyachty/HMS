<?php

namespace App\Http\Controllers;

use App\Models\HotelAddon;
use App\Models\HotelAmenity;
use App\Models\HotelMenuItem;
use App\Models\HotelRoom;
use App\Models\StudentGroup;
use App\Support\HotelAddonDesk;
use App\Support\HotelAmenityDesk;
use App\Support\HotelRoomDefaults;
use Illuminate\Http\Request;

/**
 * The catalogue behind a faculty's preview of a team's site.
 *
 * The template draws its rooms, menu, facilities and add-ons from the database at
 * runtime, through endpoints that resolve the team from the signed-in student. A
 * faculty reviewing a submission is not a student: those calls answered with an
 * empty list, and every screen fell back to the template's own built-in
 * placeholders — which is why a review of "Create Your Room Categories" showed
 * Classic, Superior, Deluxe, Premium and Family with nothing in them, whatever
 * the student had actually submitted.
 *
 * These are the same reads, with the team named in the URL and checked against
 * the faculty who owns it. Read-only by construction: there is no write here, and
 * the preview renders with editing off.
 *
 * They deliberately do not seed. ensureFor / ensureCategoriesFor write a team's
 * starting inventory on first read, which is right when the team's own member
 * opens the page and wrong when a faculty looks at it — a review must show what
 * is there, not create it.
 */
class FacultyPreviewController extends Controller
{
    /** The team named in the URL, or 404 if it is not this faculty's. */
    private function team(string $group): StudentGroup
    {
        $facultyId = auth()->user()?->faculty?->user_information_id;
        if (!$facultyId) {
            abort(403);
        }

        $membership = StudentGroup::where('faculty_id', $facultyId)
            ->where('group_name', $group)
            ->first();

        if (!$membership) {
            abort(404, 'That team does not belong to you.');
        }

        return $membership;
    }

    public function rooms(Request $request, string $group)
    {
        $membership = $this->team($group);

        $rooms = HotelRoom::with(['activeBooking.guest', 'activeBooking.payments', 'activeBooking.foodOrders', 'activeBooking.charges', 'activeBooking.addons', 'openBookings'])
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->orderBy('hotel_room_id')
            ->get()
            ->map(fn (HotelRoom $room) => $room->toTemplateArray());

        return response()->json([
            'rooms' => $rooms,
            // The point of the whole controller: the categories the team actually
            // has, including one added this morning with no rooms in it yet.
            'categories' => HotelRoomDefaults::categoriesFor($membership),
        ]);
    }

    public function menus(Request $request, string $group)
    {
        $membership = $this->team($group);

        $items = HotelMenuItem::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->orderBy('hotel_menu_item_id')
            ->get()
            ->map(fn (HotelMenuItem $item) => $item->toTemplateArray());

        /* The courses too, not just the dishes. Without them the review renders the
           five constants, so a course the team named itself has no tab and its dishes
           fall into whichever one is first — the faculty would be reviewing a menu the
           student never built. */
        return response()->json([
            'items' => $items,
            'categories' => \App\Support\HotelMenuDefaults::categoriesForTeam(
                $membership->group_name,
                $membership->faculty_id
            ),
        ]);
    }

    public function amenities(Request $request, string $group)
    {
        $membership = $this->team($group);

        $amenities = HotelAmenity::with('services')
            ->where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->orderBy('hotel_amenity_id')
            ->get();

        $repairs = HotelAmenityDesk::latestRepairsFor($membership);

        return response()->json([
            'items' => $amenities
                ->map(fn (HotelAmenity $amenity) => $amenity->toTemplateArray(
                    $repairs[$amenity->hotel_amenity_id] ?? null
                ))
                ->values(),
        ]);
    }

    public function addons(Request $request, string $group)
    {
        $membership = $this->team($group);

        $addons = HotelAddon::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->orderBy('hotel_addon_id')
            ->get();

        $reserved = HotelAddonDesk::reservedFor($membership);

        return response()->json([
            'items' => $addons
                ->map(fn (HotelAddon $addon) => $addon->toTemplateArray(
                    $reserved[$addon->hotel_addon_id] ?? 0
                ))
                ->values(),
        ]);
    }
}
