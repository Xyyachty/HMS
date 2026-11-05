<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Group;
use App\Models\HotelCustomer;
use App\Models\HotelDineInReservation;
use App\Models\HotelDineInTable;
use App\Models\StudentGroup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Reserving a dining table: Front Desk at the Dine-in desk, and a guest signed into
 * the hotel's website choosing a table for themselves. Both land the same way, as a
 * Reserved table the restaurant seats when the customer turns up.
 *
 * Failures throw RuntimeException with the HTTP status as the code: 409 when another
 * desk took the table first, 422 for everything the customer can fix.
 */
class HotelTableDesk
{
    /** How late a reserved customer can be before the table is released as a no-show. */
    public const NO_SHOW_GRACE_MINUTES = 30;

    /**
     * The dining room every team starts with, as name => seats: tables for two,
     * for four, for six and one for eight. Restaurant Management adds, renames,
     * resizes and removes them like any other.
     */
    public const DEFAULT_TABLES = [
        'Table 1'  => 2,
        'Table 2'  => 2,
        'Table 3'  => 2,
        'Table 4'  => 4,
        'Table 5'  => 4,
        'Table 6'  => 4,
        'Table 7'  => 4,
        'Table 8'  => 6,
        'Table 9'  => 6,
        'Table 10' => 8,
    ];

    /**
     * Hands a team the starting tables, once. The team's groups row records that it
     * happened, so a restaurant that removes every table is not given them back, and
     * a team that already set up tables of its own is only marked.
     */
    public static function ensureDefaultsFor(StudentGroup $membership): void
    {
        $group = Group::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->first();

        if (!$group || $group->tables_seeded_at) {
            return;
        }

        DB::transaction(function () use ($group) {
            // Two screens loading at once must not seed twenty.
            $group = Group::whereKey($group->group_id)->lockForUpdate()->first();
            if ($group->tables_seeded_at) {
                return;
            }
            self::seed($group);
        });
    }

    /** Writes the starting tables for a team with none, and marks the team either way. */
    public static function seed(Group $group): void
    {
        $exists = HotelDineInTable::where('group_name', $group->group_name)
            ->where('faculty_id', $group->faculty_id)
            ->exists();

        if (!$exists) {
            $now = now();
            HotelDineInTable::insert(collect(self::DEFAULT_TABLES)->map(fn ($seats, $name) => [
                'group_name' => $group->group_name,
                'faculty_id' => $group->faculty_id,
                'group_id'   => $group->group_id,
                'name'       => $name,
                'capacity'   => $seats,
                'status'     => 'Available',
                'created_at' => $now,
                'updated_at' => $now,
            ])->values()->all());
        }

        $group->forceFill(['tables_seeded_at' => now()])->save();
    }

    /**
     * $data keys: guest_name, contact_no, party_size, reserved_for.
     * $seatNow is a walk-in: they are here, so the table goes straight to Occupied.
     */
    public static function reserve(
        HotelDineInTable $table,
        array $data,
        ?User $actor,
        ?string $assignedBy,
        bool $seatNow = false
    ): HotelDineInTable {
        if ($table->status !== 'Available') {
            throw new RuntimeException($table->status === 'Reserved'
                ? 'This table is already reserved for someone else.'
                : 'This table already has a customer at it.', 422);
        }
        $partySize = (int) ($data['party_size'] ?? 0);
        if ($partySize < 1) {
            throw new RuntimeException('Enter how many guests are in the party.', 422);
        }
        if ($partySize > $table->capacity) {
            throw new RuntimeException("This table seats {$table->capacity} — the party is too large for it.", 422);
        }

        // reserved_for is the Manila wall-clock time the customer is due (the app runs
        // on UTC); releaseNoShows() and toTemplateArray() read it back that way.
        $due = $seatNow
            ? now('Asia/Manila')
            : (!empty($data['reserved_for']) ? Carbon::parse($data['reserved_for'], 'Asia/Manila') : null);
        // A time already gone would be released as a no-show on the next refresh.
        if (!$seatNow && $due && $due->lt(now('Asia/Manila')->subMinutes(5))) {
            throw new RuntimeException('That time has already passed. Pick a later time.', 422);
        }

        // Reserved, not Occupied: the desk is holding the table, and nobody is
        // sitting at it until they walk in. A walk-in is already standing there.
        $table->status = $seatNow ? 'Occupied' : 'Reserved';
        $table->guest_name = isset($data['guest_name']) ? trim($data['guest_name']) : null;
        $table->contact_no = isset($data['contact_no']) ? trim($data['contact_no']) : null;
        $table->party_size = $partySize;
        // assigned_at is when the desk wrote this down; reserved_for is when the
        // customer is due, which is the one the restaurant reads off the floor.
        $table->reserved_for = $due;
        $table->assigned_by = $assignedBy;
        $table->assigned_at = now();
        if (HotelDineInTable::partySupported()) {
            $table->party_ref = $data['party_ref'] ?? null;
        }

        /* Written only if the table is still Available. The check above read it
           without a lock, so two desks taking the same table at once both passed
           it and the second silently replaced the first desk's guest. */
        $taken = HotelDineInTable::where('hotel_dine_in_table_id', $table->hotel_dine_in_table_id)
            ->where('status', 'Available')
            ->update($table->getDirty() + ['updated_at' => now()]);
        if ($taken === 0) {
            throw new RuntimeException('Someone just took ' . $table->name . ' at another desk. Pick another table.', 409);
        }
        $table->syncOriginal();

        Notifier::tableAssigned($actor, $table);

        ActivityLog::record(
            $actor,
            ActivityLog::TABLE_ASSIGNED,
            ($table->status === 'Occupied' ? 'Seated walk-in ' : 'Reserved ' . $table->name . ' for ')
                . ($table->guest_name ?: 'a guest') . ' (party of ' . $table->party_size . ')'
                . ($table->status === 'Occupied' ? ' at ' . $table->name : '') . '.'
        );

        return $table;
    }

    /**
     * Several tables for one guest, reserved (or seated) together: one party_ref on
     * all of them, so they are seated, billed and closed as one. All or nothing — if
     * any table was taken meanwhile, none is kept.
     *
     * $data['party_size'] is the whole party. It is shared out so that no table holds
     * more than it seats and every table holds at least one guest.
     *
     * @param  list<HotelDineInTable>  $tables  in the order the desk picked them
     * @return list<HotelDineInTable>
     */
    public static function reserveMany(
        array $tables,
        array $data,
        ?User $actor,
        ?string $assignedBy,
        bool $seatNow = false
    ): array {
        if (count($tables) === 1) {
            return [self::reserve($tables[0], $data, $actor, $assignedBy, $seatNow)];
        }
        if (!HotelDineInTable::partySupported()) {
            throw new RuntimeException('Reserving several tables together is not available yet. Reserve one table.', 422);
        }

        $party = (int) ($data['party_size'] ?? 0);
        if ($party < 1) {
            throw new RuntimeException('Enter how many guests are in the party.', 422);
        }
        if ($party < count($tables)) {
            throw new RuntimeException('There are more tables than guests. Pick fewer tables.', 422);
        }
        $seats = array_sum(array_map(fn (HotelDineInTable $t) => (int) $t->capacity, $tables));
        if ($party > $seats) {
            throw new RuntimeException("These tables seat {$seats} — the party of {$party} is too large for them.", 422);
        }

        $ref = (string) Str::uuid();

        return DB::transaction(function () use ($tables, $data, $actor, $assignedBy, $seatNow, $party, $ref) {
            $left = $party;
            $reserved = [];
            foreach (array_values($tables) as $i => $table) {
                $after = count($tables) - $i - 1;
                $share = min((int) $table->capacity, $left - $after);
                $reserved[] = self::reserve($table, ['party_size' => $share, 'party_ref' => $ref] + $data, $actor, $assignedBy, $seatNow);
                $left -= $share;
            }

            return $reserved;
        });
    }

    /**
     * Frees every table of a team whose customer is more than NO_SHOW_GRACE_MINUTES
     * late, keeping the reservation in hotel_dine_in_no_shows. Run on every table
     * read, so Front Desk, Restaurant Management and the website all see it freed
     * without a scheduler. Only Reserved tables: a seated customer is never touched.
     */
    public static function releaseNoShows(string $groupName, int $facultyId): void
    {
        $cutoff = now('Asia/Manila')->subMinutes(self::NO_SHOW_GRACE_MINUTES)->format('Y-m-d H:i:s');

        $late = HotelDineInTable::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->where('status', 'Reserved')
            ->where('reserved_for', '<', $cutoff)
            ->get();

        foreach ($late as $table) {
            DB::transaction(function () use ($table) {
                // Still Reserved: the customer may have been seated, or another
                // screen freed it, since the read above.
                $freed = HotelDineInTable::whereKey($table->getKey())
                    ->where('status', 'Reserved')
                    ->update([
                        'status'       => 'Available',
                        'guest_name'   => null,
                        'contact_no'   => null,
                        'party_size'   => null,
                        'reserved_for' => null,
                        'assigned_by'  => null,
                        'assigned_at'  => null,
                        'updated_at'   => now(),
                    ] + (HotelDineInTable::partySupported() ? ['party_ref' => null] : []));
                if (!$freed) {
                    return;
                }
                self::endReservation($table, 'No-Show');

                DB::table('hotel_dine_in_no_shows')->insert([
                    'group_name'             => $table->group_name,
                    'faculty_id'             => $table->faculty_id,
                    'group_id'               => $table->group_id,
                    'hotel_dine_in_table_id' => $table->hotel_dine_in_table_id,
                    'table_name'             => $table->name,
                    'guest_name'             => $table->guest_name,
                    'contact_no'             => $table->contact_no,
                    'party_size'             => $table->party_size,
                    'reserved_for'           => $table->getRawOriginal('reserved_for'),
                    'assigned_by'            => $table->assigned_by,
                    'assigned_at'            => $table->getRawOriginal('assigned_at'),
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ]);
            });
        }
    }

    /** A team's latest no-shows, newest first, for the Front Desk's No-Show list. */
    public static function noShows(string $groupName, int $facultyId): array
    {
        return DB::table('hotel_dine_in_no_shows')
            ->where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->orderByDesc('hotel_dine_in_no_show_id')
            ->limit(50)
            ->get()
            ->map(fn ($row) => [
                'id'          => $row->hotel_dine_in_no_show_id,
                'tableName'   => $row->table_name,
                'guestName'   => $row->guest_name ?? '',
                'contactNo'   => $row->contact_no ?? '',
                'partySize'   => $row->party_size,
                'reservedFor' => $row->reserved_for ? Carbon::parse($row->reserved_for, 'Asia/Manila')->toIso8601String() : null,
                'assignedBy'  => $row->assigned_by ?? '',
                'markedAt'    => Carbon::parse($row->created_at)->toIso8601String(),
            ])
            ->all();
    }

    /**
     * A guest signed into the hotel's website reserving for themselves. Who they are
     * comes off their account, never off the request, and they can only book ahead:
     * a table for a time already gone is refused, and nobody is seated from home.
     *
     * Also writes the guest's own record and private link. The token is returned
     * once, here, and only its hash is kept.
     *
     * $auth is HotelSimulationAuth::current() for a customer. $tables may hold several
     * tables for one large party: they are reserved together (reserveMany) under one
     * reservation, which sits on the first table and names them all.
     *
     * @param  HotelDineInTable|list<HotelDineInTable>  $tables
     * @return array{0: HotelDineInTable, 1: ?HotelDineInReservation, 2: ?string}
     */
    public static function reserveForGuest(HotelDineInTable|array $tables, array $auth, int $partySize, string $reservedFor): array
    {
        $tables = is_array($tables) ? array_values($tables) : [$tables];
        $name = trim((string) ($auth['name'] ?? '')) ?: 'Guest';
        $customer = HotelCustomer::find($auth['customer_id'] ?? null);

        return DB::transaction(function () use ($tables, $name, $customer, $partySize, $reservedFor) {
            $all = self::reserveMany($tables, [
                'guest_name'   => $name,
                'contact_no'   => $customer?->contact_number,
                'party_size'   => $partySize,
                // reserve() refuses a time already gone.
                'reserved_for' => $reservedFor,
            ], null, 'Online · ' . $name);
            $table = $all[0];

            if (!HotelDineInReservation::supported()) {
                return [$table, null, null];
            }

            $token = Str::random(48);
            $reservation = HotelDineInReservation::create([
                'group_name'             => $table->group_name,
                'faculty_id'             => $table->faculty_id,
                'group_id'               => $table->group_id,
                'hotel_dine_in_table_id' => $table->hotel_dine_in_table_id,
                'table_name'             => HotelDineInTable::joinNames($all),
                'hotel_customer_id'      => $customer?->getKey(),
                'guest_name'             => $table->guest_name,
                // The whole party, not the first table's share of it.
                'party_size'             => $partySize,
                'reserved_for'           => $table->reserved_for,
                'token_hash'             => HotelDineInReservation::hashToken($token),
            ] + (count($all) > 1 ? ['party_ref' => $table->party_ref] : []));

            return [$table, $reservation, $token];
        });
    }

    /**
     * Records how a guest's reservation ended, just before (or as) its table is
     * freed: Completed, Cancelled or No-Show. A table reserved at the desk has no
     * guest record, so there is nothing to write for it.
     */
    public static function endReservation(HotelDineInTable $table, string $outcome): void
    {
        if (!HotelDineInReservation::supported()) {
            return;
        }

        HotelDineInReservation::where('hotel_dine_in_table_id', $table->hotel_dine_in_table_id)
            ->whereNull('outcome')
            ->update(['outcome' => $outcome, 'ended_at' => now(), 'updated_at' => now()]);
    }

    /**
     * Moves a reservation that has not been seated yet to another free table, with
     * the guest's record following it. Front Desk only; a seated table has orders
     * and a bill on it, so it stays where it is.
     */
    public static function move(HotelDineInTable $from, HotelDineInTable $to, ?User $actor): HotelDineInTable
    {
        if ($from->status !== 'Reserved') {
            throw new RuntimeException('Only a reservation that has not been seated yet can be moved.', 422);
        }
        if ($to->is($from)) {
            throw new RuntimeException('Pick a different table.', 422);
        }
        if ($to->status !== 'Available') {
            throw new RuntimeException($to->name . ' is not free.', 409);
        }
        if ((int) $from->party_size > (int) $to->capacity) {
            throw new RuntimeException("{$to->name} seats {$to->capacity} — the party of {$from->party_size} will not fit.", 422);
        }

        $fields = ['guest_name', 'contact_no', 'party_size', 'reserved_for', 'assigned_by', 'assigned_at'];
        // A table of a party keeps its party when it moves.
        if (HotelDineInTable::partySupported()) {
            $fields[] = 'party_ref';
        }

        return DB::transaction(function () use ($from, $to, $fields, $actor) {
            // Same guard as reserve(): written only if each table is still what it was.
            $taken = HotelDineInTable::whereKey($to->getKey())->where('status', 'Available')
                ->update(['status' => 'Reserved', 'updated_at' => now()]
                    + collect($fields)->mapWithKeys(fn ($f) => [$f => $from->getRawOriginal($f)])->all());
            $freed = HotelDineInTable::whereKey($from->getKey())->where('status', 'Reserved')
                ->update(['status' => 'Available', 'updated_at' => now()]
                    + array_fill_keys($fields, null));
            if (!$taken || !$freed) {
                throw new RuntimeException('One of these tables just changed at another desk. Refresh and try again.', 409);
            }

            if (HotelDineInReservation::supported()) {
                HotelDineInReservation::where('hotel_dine_in_table_id', $from->getKey())
                    ->whereNull('outcome')
                    ->update(['hotel_dine_in_table_id' => $to->getKey(), 'updated_at' => now(),
                        // A party's reservation names all its tables, the moved one now included.
                        'table_name' => $from->party_ref ? HotelDineInTable::joinNames($to->fresh()->partyTables()) : $to->name]);
            }

            ActivityLog::record(
                $actor,
                ActivityLog::TABLE_ASSIGNED,
                'Moved ' . ($from->guest_name ?: 'a guest') . "'s reservation from {$from->name} to {$to->name}."
            );

            return $to->fresh();
        });
    }
}
