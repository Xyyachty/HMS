<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupSettings;
use App\Models\HotelAddon;
use App\Models\HotelAmenity;
use App\Models\HotelMenuItem;
use App\Models\HotelRoom;
use App\Models\StudentGroup;
use App\Support\HotelAddonDesk;
use App\Support\HotelBookingDesk;
use App\Support\HotelTemplateBuilder;
use Illuminate\Http\Request;

/**
 * The Mini Portfolio: a team's hotel site as a guest meets it.
 *
 * Every other hotel route in this app answers "which hotel?" with the logged-in student's
 * own membership. A visitor here has no login, so the slug in the URL is the entire
 * identity of the request, and `team()` is the single place that turns it into the
 * (group_name, faculty_id) pair the rest of the system keys off.
 *
 * Two rules run through all of it.
 *
 * NOTHING HERE WRITES ON A GET. No ensureTemplate(), no seedDefaults(), no updateOrCreate.
 * The student routes seed a team's rooms and menu on first read, which is right when the
 * team itself opens the page and wrong when a stranger does — a visitor must never create
 * rows, and a team that has built nothing should show an empty hotel rather than have one
 * conjured for it.
 *
 * AND THE PAYLOADS ARE NARROWER. The staff room grid ships the current occupant's name,
 * email, ID number and folio; this ships HotelRoom::toPublicArray(), which has none of it.
 * The booking endpoint takes no money and no add-ons. Read the guards, not the intent.
 */
class PublicSiteController extends Controller
{
    /**
     * Resolve the slug, or 404.
     *
     * @return array{0: string, 1: int} [$groupName, $facultyId]
     */
    private function team(string $slug): array
    {
        $team = Group::resolveSlug($slug);
        abort_if($team === null, 404);

        return [$team['group_name'], (int) $team['faculty_id']];
    }

    /**
     * The team, plus its settings, refusing anything that is not published.
     *
     * @return array{0: string, 1: int, 2: GroupSettings}
     */
    private function publishedTeam(string $slug): array
    {
        [$groupName, $facultyId] = $this->team($slug);

        $settings = GroupSettings::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->first();

        // The gate. Front Desk pressing Publish in the builder is what opens the door;
        // until then the link 404s rather than showing a half-built hotel. Deliberately
        // the same 404 an unknown slug gets, so the URL cannot be used to discover which
        // teams exist but have not published.
        abort_unless($settings && $settings->is_published, 404);

        return [$groupName, $facultyId, $settings];
    }

    /** The team's site, rendered from their own saved design. */
    public function show(string $slug)
    {
        [$groupName, $facultyId, $settings] = $this->publishedTeam($slug);

        $selected = in_array((string) $settings->selected_template, ['1', '2'], true)
            ? (string) $settings->selected_template
            : '1';

        return view("students.template.{$selected}defaulttemplate", [
            // The same merged design the team sees in their builder. This function takes
            // no user and reads no session — it is the seam the whole feature hangs on.
            'customizations'  => HotelTemplateBuilder::mergeTeamCustomizations($groupName, $facultyId),
            // Read-only, twice over: the editor also disables itself outside the builder
            // iframe, but a public page should not be relying on that to be safe.
            'canEditTemplate' => false,
            'editablePages'   => [],
            'builderRole'     => null,
            // Presence of this is what switches the bridge to the public API map.
            'publicSlug'      => $slug,
        ]);
    }

    /* ── The four reads the template polls every eight seconds ─────────────── */

    public function rooms(string $slug)
    {
        [$groupName, $facultyId] = $this->publishedTeam($slug);

        // No HotelRoomDefaults::ensureFor() — see the class docblock. A visitor does not
        // seed a hotel.
        $rooms = HotelRoom::with('openBookings')
            ->where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->orderBy('hotel_room_id')
            ->get()
            ->map(fn (HotelRoom $room) => $room->toPublicArray());

        return response()->json(['rooms' => $rooms]);
    }

    public function menus(string $slug)
    {
        [$groupName, $facultyId] = $this->publishedTeam($slug);

        $items = HotelMenuItem::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn (HotelMenuItem $item) => $item->toTemplateArray());

        // can_manage is always false: it drives the staff menu-editing tools, and there is
        // no one to grant it to here.
        return response()->json(['items' => $items, 'can_manage' => false]);
    }

    public function amenities(string $slug)
    {
        [$groupName, $facultyId] = $this->publishedTeam($slug);

        $items = HotelAmenity::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->orderBy('hotel_amenity_id')
            ->get()
            // null repair: whether the pool is shut is public, but Maintenance's working
            // notes on the repair are not.
            ->map(fn (HotelAmenity $amenity) => $amenity->toTemplateArray(null));

        return response()->json(['items' => $items, 'can_manage' => false]);
    }

    public function addons(string $slug)
    {
        [$groupName, $facultyId] = $this->publishedTeam($slug);

        $addons = HotelAddon::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->orderBy('hotel_addon_id')
            ->get();

        // What is out on loan is derived from open stays. reservedFor() needs a membership
        // row purely for tenancy; a team with no members yet simply has nothing on loan.
        $membership = $this->anyMembership($groupName, $facultyId);
        $reserved = $membership ? HotelAddonDesk::reservedFor($membership) : [];

        return response()->json([
            'items'      => $addons->map(fn (HotelAddon $addon) => $addon->toTemplateArray(
                $reserved[$addon->hotel_addon_id] ?? 0
            )),
            'can_manage' => false,
        ]);
    }

    /* ── The one write ────────────────────────────────────────────────────── */

    /**
     * A guest asks for a room.
     *
     * Lands as an ordinary hotel_bookings row at 'Booked', which is exactly what Front
     * Desk's Guest Information screen already lists and can Arrive and Check In. No new
     * table, no new status, no new screen — the portfolio feeds the pipeline that exists.
     *
     * Narrower than the staff route in four ways, each deliberate:
     *  - no payment is read, because a guest cannot take their own money;
     *  - no add-ons, because those are Housekeeping's stock to attach;
     *  - the room must be Available, not merely free on the dates;
     *  - booked_by records the channel rather than a person.
     */
    public function book(Request $request, string $slug)
    {
        [$groupName, $facultyId] = $this->publishedTeam($slug);

        $data = $request->validate([
            'room_id'          => 'required|integer',
            'guest.full_name'  => 'required|string|max:255',
            'guest.contact_no' => 'nullable|string|max:100',
            'guest.email'      => 'nullable|string|email|max:255',
            'check_in'         => 'required|date',
            'check_in_time'    => 'nullable|string|max:10',
            'check_out'        => 'required|date|after:check_in',
            'notes'            => 'nullable|string|max:2000',
            // 'payment' and 'addons' are deliberately absent. Anything sent under those
            // keys is not validated, not read, and never reaches HotelBookingDesk.
        ]);

        $room = HotelRoom::where('hotel_room_id', $data['room_id'])
            ->where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->first();

        if (!$room) {
            return response()->json(['message' => 'That room is not part of this hotel.'], 404);
        }

        // Housekeeping's own state. A room being cleaned or repaired is not sellable, and
        // a guest has no way to know that from the calendar alone.
        if ($room->status !== 'Available') {
            return response()->json([
                'message' => 'That room is not available to book right now. Please choose another.',
            ], 422);
        }

        // The same half-open interval test the staff booking route makes: a stay ending on
        // the day another begins does not overlap.
        $overlaps = $room->openBookings()
            ->where('check_in', '<', $data['check_out'])
            ->where('check_out', '>', $data['check_in'])
            ->exists();

        if ($overlaps) {
            return response()->json(['message' => 'Those dates are already booked for this room.'], 409);
        }

        $membership = $this->anyMembership($groupName, $facultyId);
        if (!$membership) {
            return response()->json(['message' => 'This hotel is not taking bookings yet.'], 422);
        }

        $guestName = trim($data['guest']['full_name']);

        $booking = HotelBookingDesk::reserve(
            $membership,
            $room,
            [
                'full_name'  => $guestName,
                'contact_no' => $data['guest']['contact_no'] ?? null,
                'email'      => $data['guest']['email'] ?? null,
                // No id_number: a guest is not asked for identification online. Front Desk
                // takes it on arrival.
                'id_number'  => null,
            ],
            [
                'check_in'      => $data['check_in'],
                // Front Desk can move it; a default keeps the stay-length maths honest.
                'check_in_time' => trim((string) ($data['check_in_time'] ?? '')) ?: '14:00',
                'check_out'     => $data['check_out'],
                // The channel, not a user. Front Desk can see at a glance which stays came
                // in from the portfolio rather than across the desk.
                'booked_by'     => 'Online · ' . $guestName,
                'notes'         => $data['notes'] ?? null,
            ]
            // No $payment, no $addons — the defaults are null and [].
        );

        return response()->json([
            'message' => 'Thank you. Your booking request has been sent to the front desk.',
            'booking' => [
                'reference' => $booking->hotel_booking_id,
                'roomName'  => $room->name,
                'checkIn'   => $data['check_in'],
                'checkOut'  => $data['check_out'],
                'status'    => $booking->status,
            ],
        ], 201);
    }

    /**
     * Any one membership row for the team.
     *
     * HotelBookingDesk::reserve() and HotelAddonDesk::reservedFor() both take a
     * StudentGroup, but only ever to read group_name / faculty_id / group_id off it for
     * tenancy — never the student behind it. So any member's row scopes the write
     * correctly, and reusing those two untouched is worth more than a signature change
     * rippling through the staff screens.
     */
    private function anyMembership(string $groupName, int $facultyId): ?StudentGroup
    {
        return StudentGroup::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->orderBy('student_group_id')
            ->first();
    }
}
