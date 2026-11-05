<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupSettings;
use App\Models\HotelAddon;
use App\Models\HotelAmenity;
use App\Models\HotelDineInReservation;
use App\Models\HotelDineInTable;
use App\Models\HotelMenuItem;
use App\Models\HotelRoom;
use App\Models\StudentGroup;
use App\Support\HotelAddonDesk;
use App\Support\HotelBookingDesk;
use App\Support\HotelTemplateBuilder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            // The approved concept's name and words, for every field the team left unset.
            'hotelDefaults'   => HotelTemplateBuilder::hotelDefaults($groupName, $facultyId),
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

        return response()->json([
            'rooms' => $rooms,
            // Read, not written: the team's own categories decide which tabs a visitor
            // sees, so a room in one the team invented is not filed under Classic.
            'categories' => \App\Support\HotelRoomDefaults::categoriesForTeam($groupName, $facultyId),
            // So a guest sees what their children cost before booking.
            'childPolicy' => \App\Models\HotelChildPolicy::forTeam($groupName, (int) $facultyId)->toTemplateArray(),
        ]);
    }

    public function menus(string $slug)
    {
        [$groupName, $facultyId] = $this->publishedTeam($slug);

        return response()->json($this->menuPayload($groupName, $facultyId));
    }

    private function menuPayload(string $groupName, int $facultyId): array
    {
        $items = HotelMenuItem::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn (HotelMenuItem $item) => $item->toTemplateArray());

        // can_manage is always false: it drives the staff menu-editing tools, and there is
        // no one to grant it to here. The courses are the team's own, so a visitor sees
        // the menu under the headings the team wrote rather than the five it started with.
        return [
            'items' => $items,
            'can_manage' => false,
            'categories' => \App\Support\HotelMenuDefaults::categoriesForTeam($groupName, $facultyId),
        ];
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

    /** Only the promos the site may show; see HotelPromo::siteFeed(). */
    public function promos(string $slug)
    {
        [$groupName, $facultyId] = $this->publishedTeam($slug);

        return response()->json(\App\Models\HotelPromo::siteFeed($groupName, $facultyId));
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
    /**
     * Food, ordered by the guest reading the menu on the published site.
     *
     * Room service if they are checked in — billed to their room like an order the
     * desk would place for them — and a counter order if they are not, cooked the
     * same way and collected at the front desk. Either way the kitchen sees it in
     * the queue it already works from.
     */
    public function orderFood(Request $request, string $slug)
    {
        [$groupName, $facultyId] = $this->publishedTeam($slug);

        $auth = \App\Support\HotelSimulationAuth::current();
        if (!is_array($auth) || ($auth['type'] ?? null) !== 'customer'
            || strcasecmp((string) ($auth['group_name'] ?? ''), $groupName) !== 0) {
            return response()->json(['message' => 'Sign in as a guest to order.'], 403);
        }

        $data = $request->validate([
            'items'                => 'required|array|min:1|max:50',
            'items.*.name'         => 'required|string|max:255',
            'items.*.menu_item_id' => 'nullable|integer',
            'items.*.price'        => 'nullable|integer|min:0',
            'items.*.qty'          => 'required|integer|min:1|max:99',
        ]);

        $items = \App\Models\HotelFoodOrder::sanitizeItems($data['items']);
        if (!$items) {
            return response()->json(['message' => 'Add at least one menu item to the order.'], 422);
        }

        $membership = StudentGroup::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->first();

        $booking = \App\Support\HotelGuestStay::checkedInBooking($membership);

        $order = \App\Models\HotelFoodOrder::create([
            'group_name'       => $groupName,
            'faculty_id'       => $facultyId,
            'group_id'         => $membership?->group_id,
            'order_type'       => 'room_service',
            'hotel_booking_id' => $booking?->hotel_booking_id,
            'room_number'      => $booking?->room?->name,
            'guest_name'       => $booking?->guest?->full_name ?: ($auth['name'] ?? 'Guest'),
            'items'            => $items,
            'total'            => \App\Models\HotelFoodOrder::totalFor($items),
            'status'           => 'Preparing',
            // Nobody on staff placed it: the guest did, from the website.
            'placed_by'        => $auth['name'] ?? 'Guest (website)',
        ]);

        return response()->json(['order' => $order->toTemplateArray()], 201);
    }

    /**
     * A guest booking a facility from the published site.
     *
     * The staff endpoint behind this asks the front desk who the booking is for;
     * here there is only ever one answer - the guest signed into this hotel - so
     * it is read off the session and nothing about who is taken from the request.
     * Everything else the desk's own booking does, this does: the same desk class,
     * the same clash and opening-hours checks, and the same Pending status waiting
     * for the desk to confirm it.
     */
    public function bookAmenity(Request $request, string $slug)
    {
        [$groupName, $facultyId] = $this->publishedTeam($slug);

        $auth = \App\Support\HotelSimulationAuth::current();
        if (!is_array($auth) || ($auth['type'] ?? null) !== 'customer'
            || strcasecmp((string) ($auth['group_name'] ?? ''), $groupName) !== 0) {
            return response()->json(['message' => 'Sign in as a guest to book a facility.'], 403);
        }

        $data = $request->validate([
            'hotel_amenity_id'         => 'required|integer',
            'kind'                     => ['required', Rule::in(\App\Models\HotelAmenityReservation::KINDS)],
            'scheduled_on'             => 'required|date_format:Y-m-d',
            'starts_at'                => 'required|date_format:H:i',
            'ends_at'                  => 'nullable|date_format:H:i',
            'special_requests'         => 'nullable|string|max:2000',
            'hotel_amenity_service_id' => 'nullable|integer',
            'guest_count'              => 'nullable|integer|min:1|max:9999',
            'package'                  => ['nullable', Rule::in(\App\Models\HotelAmenityReservation::PACKAGES)],
        ]);

        $amenity = \App\Models\HotelAmenity::where('hotel_amenity_id', $data['hotel_amenity_id'])
            ->where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->firstOrFail();

        $stay = \App\Support\HotelGuestStay::checkedInBooking(
            \App\Models\StudentGroup::where('group_name', $groupName)->where('faculty_id', $facultyId)->first()
        );

        $data['customer_name'] = $auth['name'] ?? 'Guest';
        $data['email'] = $auth['email'] ?? null;
        $data['hotel_booking_id'] = $stay?->hotel_booking_id;

        try {
            $reservation = \App\Support\HotelAmenityReservationDesk::book($amenity, $data, null);
        } catch (\RuntimeException $e) {
            $clash = str_contains($e->getMessage(), 'already taken');

            return response()->json(['message' => $e->getMessage()], $clash ? 409 : 422);
        }

        return response()->json(['reservation' => $reservation->toTemplateArray()], 201);
    }

    /**
     * Signing yourself into a facility that keeps a register — the pool, the gym.
     *
     * The register records who is inside; a guest with no stay yet is still inside
     * it, so the visit is written with their name and no room rather than refused.
     */
    /**
     * The dining tables, as much as a visitor needs to pick one: which exist, how many
     * each seats and whether it is free. Who is sitting at a table, or due at it, stays
     * with the staff endpoint.
     */
    public function tables(string $slug)
    {
        [$groupName, $facultyId] = $this->publishedTeam($slug);
        \App\Support\HotelTableDesk::releaseNoShows($groupName, (int) $facultyId);

        $tables = \App\Models\HotelDineInTable::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->orderBy('name')
            ->get()
            ->map(fn ($table) => [
                'id'       => $table->hotel_dine_in_table_id,
                'name'     => $table->name,
                'capacity' => (int) $table->capacity,
                'status'   => $table->status,
            ]);

        return response()->json(['tables' => $tables, 'can_manage' => false, 'can_assign' => false]);
    }

    /** A signed-in guest reserving a table for themselves; see HotelTableDesk::reserveForGuest(). */
    public function reserveTable(Request $request, string $slug)
    {
        [$groupName, $facultyId] = $this->publishedTeam($slug);

        $auth = \App\Support\HotelSimulationAuth::current();
        if (!is_array($auth) || ($auth['type'] ?? null) !== 'customer'
            || strcasecmp((string) ($auth['group_name'] ?? ''), $groupName) !== 0) {
            return response()->json(['message' => 'Sign in as a guest to reserve a table.'], 403);
        }

        $data = $request->validate([
            'table_id'     => 'required|integer',
            'party_size'   => 'required|integer|min:1|max:50',
            'reserved_for' => 'required|date',
            // A party too big for one table: the others reserved with table_id.
            'with_tables'   => 'sometimes|array|max:9',
            'with_tables.*' => 'integer',
        ]);

        $ids = array_values(array_unique(array_merge([(int) $data['table_id']], array_map('intval', $data['with_tables'] ?? []))));
        $found = \App\Models\HotelDineInTable::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->whereIn('hotel_dine_in_table_id', $ids)
            ->get()
            ->keyBy('hotel_dine_in_table_id');
        if ($found->count() !== count($ids)) {
            abort(404);
        }
        $picked = array_map(fn ($id) => $found->get($id), $ids);

        try {
            [$table, $reservation, $token] = \App\Support\HotelTableDesk::reserveForGuest(
                $picked, $auth, (int) $data['party_size'], (string) $data['reserved_for']
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() === 409 ? 409 : 422);
        }

        return response()->json([
            'table' => [
                'id'       => $table->hotel_dine_in_table_id,
                'name'     => $table->name,
                'capacity' => (int) $table->capacity,
                'status'   => $table->status,
            ],
            'reservation' => self::guestReservationLink($reservation, $token, $slug, $table),
        ], 201);
    }

    /**
     * What the confirmation shows right after a guest reserves: the reference, the
     * reservation itself and the private link to follow it. Null if the guest
     * record could not be written (the migration has not run yet).
     */
    public static function guestReservationLink(?HotelDineInReservation $reservation, ?string $token, ?string $slug, HotelDineInTable $table): ?array
    {
        if (!$reservation || !$token || !$slug) {
            return null;
        }

        // Root-relative, like the site's API map: APP_URL is wrong behind a proxy.
        return $reservation->toGuestArray($table) + [
            'link' => '/hotel/' . rawurlencode($slug) . '/reservation/' . $token,
        ];
    }

    /**
     * The guest's own reservation and the restaurant's menu, behind the private link.
     *
     * The token is the whole of the access check, so this works for a team still
     * testing in the Simulation as well as a published site: it is long, random and
     * stored only as a hash, and it opens exactly one reservation. Read-only; the
     * one thing a read does is the same no-show release every table read does.
     */
    public function reservation(string $slug, string $token)
    {
        return $this->reservationPage($slug, $this->guestReservation($slug, $token), '/api/reservation/' . $token);
    }

    public function reservationData(string $slug, string $token)
    {
        return $this->reservationJson($this->guestReservation($slug, $token));
    }

    /**
     * The same page, opened from My Bookings by the signed-in guest who made the
     * reservation. The account is the access check here instead of the token, so a
     * guest who lost the private link still has a way back to the status and menu.
     */
    public function myReservation(string $slug, int $id)
    {
        return $this->reservationPage($slug, $this->accountReservation($slug, $id), '/api/my-reservation/' . $id);
    }

    public function myReservationData(string $slug, int $id)
    {
        return $this->reservationJson($this->accountReservation($slug, $id));
    }

    /**
     * A room stay of the guest signed into this hotel, opened from My Bookings: its
     * status and its whole bill, as the Front Desk's Statement of Account while the
     * stay is open and its Official Receipt once checked out.
     */
    public function myStay(string $slug, int $id)
    {
        [$booking, $error, $code] = $this->accountStay($slug, $id);

        $team = Group::resolveSlug($slug);
        $groupName = $team ? (string) $team['group_name'] : '';
        $facultyId = $team ? (int) $team['faculty_id'] : 0;
        $custom = $team ? HotelTemplateBuilder::mergeTeamCustomizations($groupName, $facultyId) : [];

        return response()
            ->view('hotel.stay', [
                'error'     => $error,
                'dataUrl'   => $booking ? '/hotel/' . rawurlencode($slug) . '/api/my-stay/' . $id : null,
                'template'  => $team ? $this->templateFor($groupName, $facultyId) : '1',
                'brandName' => $team ? $this->brandName($custom, $groupName, $facultyId) : HotelTemplateBuilder::DEFAULT_BRAND_NAME,
                'theme'     => $this->siteTheme($custom),
                'siteUrl'   => '/hotel/' . rawurlencode($slug),
                'printIdentity' => $team
                    ? HotelTemplateBuilder::printIdentity($custom, $groupName, $facultyId, $this->templateFor($groupName, $facultyId))
                    : null,
            ], $code)
            ->header('Cache-Control', 'no-store')
            ->header('X-Robots-Tag', 'noindex');
    }

    public function myStayData(string $slug, int $id)
    {
        [$booking, $error, $code] = $this->accountStay($slug, $id);
        if (!$booking) {
            return response()->json(['message' => $error], $code);
        }

        $bill = $booking->toBillArray();
        // The guest's own bill, less what only staff need: who added a charge, and
        // payment notes and the reference that groups a shared payment.
        $bill['otherCharges']['items'] = array_map(
            fn ($c) => array_intersect_key($c, array_flip(['id', 'description', 'amount', 'addedAt'])),
            $bill['otherCharges']['items'] ?? []
        );
        $bill['payments'] = array_map(
            fn ($p) => array_intersect_key($p, array_flip(['id', 'type', 'amountPaid', 'method', 'reference', 'paidAt'])),
            $bill['payments'] ?? []
        );

        return response()->json([
            'stay' => $booking->toGuestArray() + ['checkedOutAt' => $bill['checkedOutAt'] ?? null],
            'bill' => $bill,
        ])->header('Cache-Control', 'no-store');
    }

    /**
     * One of the signed-in guest's own stays at this hotel, by id: the same stays My
     * Bookings lists (HotelGuestStay::bookings). Another guest's, another hotel's, or
     * nobody signed in all get the same 404.
     *
     * @return array{0: ?\App\Models\HotelBooking, 1: ?string, 2: int}
     */
    private function accountStay(string $slug, int $id): array
    {
        $context = \App\Support\HotelSimulationAuth::teamContextForSlug($slug);
        if ($context) {
            \App\Support\HotelSimulationAuth::restore(request()->user(), $context);
        }

        $booking = $context
            ? \App\Support\HotelGuestStay::bookings($context['group_name'], (int) $context['faculty_id'])
                ->first(fn ($b) => (int) $b->hotel_booking_id === $id)
            : null;

        return $booking
            ? [$booking, null, 200]
            : [null, 'Sign in to the hotel website with the account that made this booking, then open it from My Bookings.', 404];
    }

    /** @param array{0: ?HotelDineInReservation, 1: ?string, 2: int} $found */
    private function reservationPage(string $slug, array $found, string $dataPath)
    {
        [$reservation, $error, $code] = $found;

        // Dressed as the team's own site, the not-found page too: the slug is in the
        // URL already, so this tells nobody anything the address does not.
        $team = Group::resolveSlug($slug);
        $groupName = $team ? (string) $team['group_name'] : '';
        $facultyId = $team ? (int) $team['faculty_id'] : 0;
        $custom = $team ? HotelTemplateBuilder::mergeTeamCustomizations($groupName, $facultyId) : [];

        return response()
            ->view('hotel.reservation', [
                'error'     => $error,
                'dataUrl'   => $reservation ? '/hotel/' . rawurlencode($slug) . $dataPath : null,
                'template'  => $team ? $this->templateFor($groupName, $facultyId) : '1',
                'brandName' => $team ? $this->brandName($custom, $groupName, $facultyId) : HotelTemplateBuilder::DEFAULT_BRAND_NAME,
                'theme'     => $this->siteTheme($custom),
                'siteUrl'   => '/hotel/' . rawurlencode($slug),
                // The header of the guest's printed bill, the same as the restaurant prints.
                'printIdentity' => $team
                    ? HotelTemplateBuilder::printIdentity($custom, $groupName, $facultyId, $this->templateFor($groupName, $facultyId))
                    : null,
            ], $code)
            // The token is in the URL: never send it on to an image host or a cache.
            ->header('Referrer-Policy', 'no-referrer')
            ->header('Cache-Control', 'no-store')
            ->header('X-Robots-Tag', 'noindex');
    }

    /** @param array{0: ?HotelDineInReservation, 1: ?string, 2: int} $found */
    private function reservationJson(array $found)
    {
        [$reservation, $error, $code] = $found;
        if (!$reservation) {
            return response()->json(['message' => $error], $code);
        }

        \App\Support\HotelTableDesk::releaseNoShows($reservation->group_name, (int) $reservation->faculty_id);
        $reservation->refresh();

        $table = $reservation->outcome === null && $reservation->hotel_dine_in_table_id
            ? HotelDineInTable::where('hotel_dine_in_table_id', $reservation->hotel_dine_in_table_id)
                ->where('group_name', $reservation->group_name)
                ->where('faculty_id', $reservation->faculty_id)
                ->first()
            : null;

        return response()->json([
            'reservation' => $reservation->toGuestArray($table),
            'bill'        => $this->guestBill($reservation, $table),
            'menu'        => $this->menuPayload($reservation->group_name, (int) $reservation->faculty_id),
        ])->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    /**
     * The guest's own bill, for their reservation page: while they are seated, what
     * has been ordered so far, priced by the same HotelDineInBill the restaurant
     * settles from (Senior Citizen / PWD discounts are applied by the cashier when
     * paying); once paid, the payment that settled it. Only what the paper shows:
     * no staff names, no payment reference. Null before they are seated.
     */
    private function guestBill(HotelDineInReservation $reservation, ?HotelDineInTable $table): ?array
    {
        $line = fn (array $i) => array_intersect_key($i, array_flip(['tableName', 'placedAt', 'name', 'qty', 'price', 'line']));
        $paper = fn (array $b) => array_intersect_key($b, array_flip([
            'tableName', 'guestName', 'partySize', 'orderIds', 'subtotal', 'discounts', 'serviceChargeRate', 'serviceCharge',
        ]));

        if ($table && $table->status === 'Occupied') {
            $bill = \App\Support\HotelDineInBill::for($table);

            return ['state' => 'open', 'bill' => $paper($bill) + [
                'items' => array_map($line, $bill['items']),
                'total' => $bill['total'],
            ]];
        }

        if (HotelDineInReservation::paymentLinkSupported() && $reservation->hotel_dine_in_payment_id) {
            $payment = \App\Models\HotelDineInPayment::where('hotel_dine_in_payment_id', $reservation->hotel_dine_in_payment_id)
                ->where('group_name', $reservation->group_name)
                ->where('faculty_id', $reservation->faculty_id)
                ->first();
            if ($payment) {
                $p = $payment->toTemplateArray();

                return ['state' => 'paid', 'payment' => $paper($p) + [
                    'items'      => array_map($line, $p['items'] ?? []),
                    'totalDue'   => $p['totalDue'],
                    'amountPaid' => $p['amountPaid'],
                    'balance'    => $p['balance'],
                    'method'     => $p['method'],
                    'paidAt'     => $p['paidAt'],
                    // Each method and amount of a split payment, without references.
                    'splits'     => array_map(fn ($s) => ['method' => $s['method'] ?? '', 'amount' => (float) ($s['amount'] ?? 0)], $p['splits'] ?? []),
                ]];
            }
        }

        return null;
    }

    /**
     * @return array{0: ?HotelDineInReservation, 1: ?string, 2: int}
     */
    private function guestReservation(string $slug, string $token): array
    {
        $team = Group::resolveSlug($slug);
        $reservation = $team ? HotelDineInReservation::findByToken($token) : null;

        // Same answer for a wrong token and a token for another hotel, so a link
        // cannot be used to learn which hotel a reservation belongs to.
        if (!$reservation
            || strcasecmp($reservation->group_name, (string) $team['group_name']) !== 0
            || (int) $reservation->faculty_id !== (int) $team['faculty_id']) {
            return [null, 'We could not find this reservation. Check that you opened the full link from your confirmation.', 404];
        }
        if ($reservation->linkExpired()) {
            return [null, 'This reservation link has expired. Please contact the hotel if you need your reservation details.', 410];
        }

        return [$reservation, null, 200];
    }

    /**
     * A reservation of the guest signed into this hotel, by id. Another guest's id,
     * another hotel's, or nobody signed in all get the same 404.
     *
     * @return array{0: ?HotelDineInReservation, 1: ?string, 2: int}
     */
    private function accountReservation(string $slug, int $id): array
    {
        $context = \App\Support\HotelSimulationAuth::teamContextForSlug($slug);
        if ($context) {
            // A "remember me" guest opening the page in a new tab is signed back in first.
            \App\Support\HotelSimulationAuth::restore(request()->user(), $context);
        }
        $auth = \App\Support\HotelSimulationAuth::current();

        $reservation = $context && HotelDineInReservation::supported()
            && is_array($auth) && ($auth['type'] ?? null) === 'customer'
            ? HotelDineInReservation::where('hotel_dine_in_reservation_id', $id)
                ->where('group_name', $context['group_name'])
                ->where('faculty_id', $context['faculty_id'])
                ->where('hotel_customer_id', (int) ($auth['customer_id'] ?? 0))
                ->first()
            : null;

        if (!$reservation) {
            return [null, 'Sign in to the hotel website with the account that made this reservation, then open it from My Bookings.', 404];
        }

        return [$reservation, null, 200];
    }

    private function templateFor(string $groupName, int $facultyId): string
    {
        $selected = (string) GroupSettings::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->value('selected_template');

        return $selected === '2' ? '2' : '1';
    }

    /** The name the team gave its site, else the approved concept's: what the site's header shows. */
    private function brandName(array $custom, string $groupName, int $facultyId): string
    {
        $label = trim((string) data_get($custom, HotelTemplateBuilder::BRAND_NAME_KEY . '.items.0.label', ''));

        return $label !== ''
            ? $label
            : HotelTemplateBuilder::hotelDefaults($groupName, $facultyId)['name'];
    }

    /**
     * The parts of the team's design the reservation page wears: the site and header
     * colours, the menu card colour, the site-wide type and the logo. Raw strings as
     * the builder saved them; the page turns colours into a palette the same way the
     * templates' SiteTheme does, and ignores anything that does not parse.
     */
    private function siteTheme(array $custom): array
    {
        $str = fn ($value) => is_string($value) ? trim($value) : '';

        $colors = [];
        foreach ((array) data_get($custom, HotelTemplateBuilder::SITE_COLORS_KEY . '.items', []) as $item) {
            if (is_array($item) && is_string($item['id'] ?? null)) {
                $colors[$item['id']] = $str($item['bg'] ?? null);
            }
        }

        $type = (array) data_get($custom, HotelTemplateBuilder::TYPOGRAPHY_KEY . '.items.0', []);

        // The shared logo, then the per-section ones older sites still carry (the
        // templates' resolveLogo() reads them in this order), then the default.
        $images = (array) data_get($custom, HotelTemplateBuilder::CARD_IMAGES_KEY . '.map', []);
        $logo = '';
        foreach (['logo', 'logo-home', 'logo-rooms', 'logo-restaurant'] as $id) {
            if (($logo = $str($images['brand:' . $id] ?? null)) !== '') {
                break;
            }
        }

        return [
            'site'         => $colors['site'] ?? '',
            'header'       => $colors['header'] ?? '',
            'menuCard'     => $str(data_get($custom, HotelTemplateBuilder::MENU_CARD_STYLE_KEY . '.items.0.bg')),
            'family'       => $str($type['family'] ?? null),
            'size'         => $str($type['size'] ?? null),
            'color'        => $str($type['color'] ?? null),
            'headingColor' => $str($type['headingColor'] ?? null),
            'logo'         => $logo,
            'defaultLogo'  => asset('new_logo_in_chtm....png'),
        ];
    }

    public function enterAmenity(Request $request, string $slug)
    {
        [$groupName, $facultyId] = $this->publishedTeam($slug);

        $auth = \App\Support\HotelSimulationAuth::current();
        if (!is_array($auth) || ($auth['type'] ?? null) !== 'customer'
            || strcasecmp((string) ($auth['group_name'] ?? ''), $groupName) !== 0) {
            return response()->json(['message' => 'Sign in as a guest to use a facility.'], 403);
        }

        $data = $request->validate([
            'hotel_amenity_id' => 'required|integer',
            'party_size'       => 'nullable|integer|min:1|max:999',
            'notes'            => 'nullable|string|max:500',
        ]);

        $amenity = \App\Models\HotelAmenity::where('hotel_amenity_id', $data['hotel_amenity_id'])
            ->where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->firstOrFail();

        $membership = \App\Models\StudentGroup::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->first();

        $data['guest_name'] = $auth['name'] ?? 'Guest';

        try {
            $visit = \App\Support\HotelAmenityVisitDesk::registerEntry(
                $amenity,
                \App\Support\HotelGuestStay::checkedInBooking($membership),
                $data,
                null
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['visit' => $visit->toTemplateArray()], 201);
    }

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
            'guest_count'      => 'nullable|integer|min:1|max:99',
            // Adults and each child's age, priced by the hotel's child policy.
            'adults'           => 'nullable|integer|min:1|max:99',
            'child_ages'       => 'nullable|array|max:20',
            'child_ages.*'     => 'required|integer|min:0|max:17',
            'notes'            => 'nullable|string|max:2000',
            'promo_id'         => 'nullable|integer',
            // The guest's online payment, full or a deposit. In the Simulation nothing is
            // charged: it is recorded against the stay like a payment at the desk, never
            // for more than the room costs.
            'payment'             => 'nullable|array',
            'payment.type'        => 'nullable|string|max:20',
            'payment.amount_paid' => 'nullable|numeric|min:0',
            'payment.method'      => 'nullable|string|max:50',
            'payment.reference'   => 'nullable|string|max:255',
            'payment.payer_name'  => 'nullable|string|max:255',
            // Housekeeping's extras the guest picked, held for the stay the same way Front
            // Desk's are; an extra bed lets the room sleep one more (HotelBookingDesk::reserve).
            'addons'              => 'nullable|array|max:20',
            'addons.*.addon_id'   => 'required|integer',
            'addons.*.qty'        => 'required|integer|min:1|max:10',
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

        // A signed-in guest's stay belongs to their account: My Bookings finds stays by
        // the account's email, so that is the email the stay is kept under.
        $auth = \App\Support\HotelSimulationAuth::current();
        $accountEmail = ($auth['type'] ?? null) === 'customer'
            && ($auth['group_name'] ?? null) === $groupName
            && (int) ($auth['faculty_id'] ?? 0) === (int) $facultyId
                ? ($auth['email'] ?? null)
                : null;

        try {
            $booking = \Illuminate\Support\Facades\DB::transaction(function () use ($membership, $room, $data, $guestName, $accountEmail) {
                $booking = HotelBookingDesk::reserve(
                    $membership,
                    $room,
                    [
                        'full_name'  => $guestName,
                        'contact_no' => $data['guest']['contact_no'] ?? null,
                        'email'      => $accountEmail ?: ($data['guest']['email'] ?? null),
                        // No id_number: a guest is not asked for identification online. Front Desk
                        // takes it on arrival.
                        'id_number'  => null,
                    ],
                    [
                        'check_in'      => $data['check_in'],
                        // Front Desk can move it; a default keeps the stay-length maths honest.
                        'check_in_time' => trim((string) ($data['check_in_time'] ?? '')) ?: '14:00',
                        'check_out'     => $data['check_out'],
                        'guest_count'   => $data['guest_count'] ?? null,
                        'adults'        => $data['adults'] ?? null,
                        'child_ages'    => $data['child_ages'] ?? [],
                        // The channel, not a user. Front Desk can see at a glance which stays came
                        // in from the portfolio rather than across the desk.
                        'booked_by'     => 'Online · ' . $guestName,
                        'notes'         => $data['notes'] ?? null,
                        'promo_id'      => $data['promo_id'] ?? null,
                    ],
                    null,
                    $data['addons'] ?? []
                );

                // Priced off the stay just written, so a guest can never record more than it costs.
                $amount = min((float) ($data['payment']['amount_paid'] ?? 0), $booking->totalDue());
                if ($amount > 0) {
                    HotelBookingDesk::addPayment($booking, [
                        'amount_paid' => $amount,
                        'type'        => $amount < $booking->totalDue() ? 'Partial' : 'Full',
                        'payer_name'  => $guestName,
                    ] + array_intersect_key($data['payment'], array_flip(['method', 'reference'])));
                }

                return $booking;
            });
        } catch (\RuntimeException $e) {
            // The party is bigger than the room sleeps, or the promo is not running then.
            return response()->json(['message' => $e->getMessage()], 422);
        }

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
