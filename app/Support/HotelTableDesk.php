<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Group;
use App\Models\HotelCustomer;
use App\Models\HotelDineInTable;
use App\Models\StudentGroup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
                    ]);
                if (!$freed) {
                    return;
                }

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
     * $auth is HotelSimulationAuth::current() for a customer.
     */
    public static function reserveForGuest(HotelDineInTable $table, array $auth, int $partySize, string $reservedFor): HotelDineInTable
    {
        $name = trim((string) ($auth['name'] ?? '')) ?: 'Guest';

        return self::reserve($table, [
            'guest_name'   => $name,
            'contact_no'   => HotelCustomer::find($auth['customer_id'] ?? null)?->contact_number,
            'party_size'   => $partySize,
            // reserve() refuses a time already gone.
            'reserved_for' => $reservedFor,
        ], null, 'Online · ' . $name);
    }
}
