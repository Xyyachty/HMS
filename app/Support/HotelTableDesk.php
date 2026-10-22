<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\HotelCustomer;
use App\Models\HotelDineInTable;
use App\Models\User;
use Carbon\Carbon;
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

        // Reserved, not Occupied: the desk is holding the table, and nobody is
        // sitting at it until they walk in. A walk-in is already standing there.
        $table->status = $seatNow ? 'Occupied' : 'Reserved';
        $table->guest_name = isset($data['guest_name']) ? trim($data['guest_name']) : null;
        $table->contact_no = isset($data['contact_no']) ? trim($data['contact_no']) : null;
        $table->party_size = $partySize;
        // assigned_at is when the desk wrote this down; reserved_for is when the
        // customer is due, which is the one the restaurant reads off the floor.
        $table->reserved_for = $seatNow
            ? now()
            : (!empty($data['reserved_for']) ? Carbon::parse($data['reserved_for']) : null);
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
     * A guest signed into the hotel's website reserving for themselves. Who they are
     * comes off their account, never off the request, and they can only book ahead:
     * a table for a time already gone is refused, and nobody is seated from home.
     *
     * $auth is HotelSimulationAuth::current() for a customer.
     */
    public static function reserveForGuest(HotelDineInTable $table, array $auth, int $partySize, string $reservedFor): HotelDineInTable
    {
        // Users are in Manila while the app runs on UTC.
        $due = Carbon::parse($reservedFor, 'Asia/Manila');
        if ($due->lt(now('Asia/Manila')->subMinutes(5))) {
            throw new RuntimeException('That time has already passed. Pick a later time.', 422);
        }

        $name = trim((string) ($auth['name'] ?? '')) ?: 'Guest';

        return self::reserve($table, [
            'guest_name'   => $name,
            'contact_no'   => HotelCustomer::find($auth['customer_id'] ?? null)?->contact_number,
            'party_size'   => $partySize,
            // Stored as the Manila wall-clock time the guest picked, like the desk's.
            'reserved_for' => $due->format('Y-m-d H:i:s'),
        ], null, 'Online · ' . $name);
    }
}
