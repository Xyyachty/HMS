<?php

namespace App\Support;

use App\Models\HotelBooking;
use App\Models\HotelComplaint;
use App\Models\HotelRoom;
use App\Models\HotelRoomInspection;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Every write to a post-checkout inspection goes through here — the same rule
 * HotelBookingDesk applies to a stay. An inspection touches the room's own status and,
 * when Housekeeping finds damage, a hotel_complaints row too; keeping that in one place
 * is what stops the inspection and the room status from drifting apart.
 */
class HotelHousekeepingDesk
{
    /**
     * Opens the post-checkout pass. Called from inside HotelBookingDesk::checkOut()'s
     * own transaction, so a room can never go Cleaning without a queue entry for it.
     *
     * No-op when the room already has one open — a second check-out call for the same
     * room (HotelBookingDesk::applyRoomStatus() can reach checkOut() from the room
     * status picker too) must not stack duplicate passes.
     */
    public static function openInspection(HotelBooking $booking): ?HotelRoomInspection
    {
        $room = $booking->room;
        if (!$room) {
            return null;
        }

        $existing = HotelRoomInspection::where('hotel_room_id', $room->hotel_room_id)
            ->open()
            ->first();
        if ($existing) {
            return $existing;
        }

        $inspection = HotelRoomInspection::create([
            'group_name'       => $booking->group_name,
            'faculty_id'       => $booking->faculty_id,
            'group_id'         => $booking->group_id,
            'hotel_room_id'    => $room->hotel_room_id,
            'hotel_booking_id' => $booking->hotel_booking_id,
            'room_name'        => $room->name,
            'guest_name'       => $booking->guest?->full_name,
            'checked_out_at'   => $booking->checked_out_at,
            'status'           => 'Pending',
        ]);

        Notifier::roomAwaitingInspection($inspection);

        return $inspection;
    }

    /** A housekeeper opened the room to look it over. */
    public static function startInspection(HotelRoomInspection $inspection, ?string $by): HotelRoomInspection
    {
        if (in_array($inspection->status, ['Pending'], true)) {
            $inspection->status = 'Inspecting';
        }
        $inspection->inspected_by = self::clean($by) ?? $inspection->inspected_by;
        $inspection->inspected_at = $inspection->inspected_at ?? now();
        $inspection->save();

        return $inspection;
    }

    /** Records what the housekeeper found. Cleaning Only leaves the room ready to complete. */
    public static function recordFinding(
        HotelRoomInspection $inspection,
        string $finding,
        ?string $notes,
        ?string $by
    ): HotelRoomInspection {
        $inspection->finding = HotelRoomInspection::normalizeFinding($finding);
        $inspection->notes = self::clean($notes);
        $inspection->inspected_by = self::clean($by) ?? $inspection->inspected_by;
        $inspection->inspected_at = $inspection->inspected_at ?? now();
        if ($inspection->status === 'Pending') {
            $inspection->status = 'Inspecting';
        }
        $inspection->save();

        return $inspection;
    }

    /**
     * Damage, a repair need, or a missing item. Files it as a maintenance complaint
     * rather than a new table — that queue, its statuses and its notifications already
     * exist — and moves the room out of Housekeeping's hands until Maintenance clears it.
     *
     * $issue keys: category (optional, defaults off the finding), department (optional,
     * defaults off the category), details.
     */
    public static function reportIssue(HotelRoomInspection $inspection, array $issue, ?User $actor): HotelComplaint
    {
        return DB::transaction(function () use ($inspection, $issue, $actor) {
            $category = HotelComplaint::normalizeCategory(
                $issue['category'] ?? HotelRoomInspection::FINDINGS[$inspection->finding] ?? null
            );
            $department = empty($issue['department'])
                ? HotelComplaint::departmentForCategory($category)
                : HotelComplaint::normalizeDepartment($issue['department']);

            $complaint = HotelComplaint::create([
                'group_name'               => $inspection->group_name,
                'faculty_id'               => $inspection->faculty_id,
                'group_id'                 => $inspection->group_id,
                'hotel_room_inspection_id' => $inspection->hotel_room_inspection_id,
                'room_number'              => $inspection->room_name,
                'guest_name'               => $inspection->guest_name,
                'category'                 => $category,
                'department'               => $department,
                'details'                  => trim((string) ($issue['details'] ?? '')),
                'status'                   => 'Open',
                'filed_by'                 => $actor?->name,
            ]);

            $inspection->status = 'Awaiting Repair';
            $inspection->save();

            // Housekeeping's job pauses here — the room is not theirs to clear until
            // Maintenance closes every issue this inspection raised.
            self::setRoomStatus($inspection->room, 'Maintenance');

            Notifier::complaintFiled($actor, $complaint);
            ActivityLog::record(
                $actor,
                ActivityLog::ROOM_INSPECTED,
                'Reported ' . $complaint->category . ' in room ' . $inspection->room_name
                    . ' during inspection and sent it to ' . $complaint->departmentLabel() . '.'
            );

            if ($inspection->finding === 'Missing Items') {
                Notifier::inspectionItemsMissing($actor, $inspection, $complaint);
            }

            return $complaint;
        });
    }

    /**
     * A department closed a complaint this inspection raised. Once none are left open,
     * the pass comes back to Housekeeping for the final look — see PROCEDURES.md's
     * "conduct a final inspection after repair".
     */
    public static function onIssueClosed(HotelComplaint $complaint, ?User $actor): void
    {
        $inspectionId = $complaint->hotel_room_inspection_id;
        if (!$inspectionId) {
            return;
        }

        $inspection = HotelRoomInspection::find($inspectionId);
        if (!$inspection || $inspection->status !== 'Awaiting Repair') {
            return;
        }

        if ($inspection->hasOpenIssues()) {
            return;
        }

        $inspection->status = 'Awaiting Re-inspection';
        $inspection->save();

        Notifier::roomAwaitingReinspection($inspection);
    }

    /**
     * The room is sellable again. Refuses while any issue this inspection raised is
     * still open — Housekeeping cannot clear a room Maintenance hasn't finished with.
     */
    public static function complete(HotelRoomInspection $inspection, ?string $by, ?User $actor): HotelRoomInspection
    {
        if ($inspection->hasOpenIssues()) {
            throw new \RuntimeException('This room still has an open maintenance issue. It cannot be marked ready yet.');
        }

        return DB::transaction(function () use ($inspection, $by, $actor) {
            $inspection->status = 'Completed';
            $inspection->completed_by = self::clean($by) ?? $actor?->name;
            $inspection->completed_at = now();
            $inspection->save();

            // Written directly, not through HotelBookingDesk::applyRoomStatus() — that
            // method's job is closing an open booking, and the stay behind this room
            // ended when the inspection was opened. There is nothing left to close.
            self::setRoomStatus($inspection->room, 'Available');

            Notifier::roomReadyForNextGuest($inspection);
            ActivityLog::record(
                $actor,
                ActivityLog::ROOM_READY,
                'Marked room ' . $inspection->room_name . ' ready after inspection.'
            );

            return $inspection;
        });
    }

    private static function setRoomStatus(?HotelRoom $room, string $status): void
    {
        if (!$room) {
            return;
        }

        $room->status = HotelRoom::normalizeStatus($status);
        $room->save();
    }

    private static function clean($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
