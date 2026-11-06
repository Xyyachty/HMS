<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\HotelBooking;
use App\Models\HotelComplaint;
use App\Models\HotelServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Housekeeping requests during a stay. A request is made for a guest who is staying
 * (Checked In), by the guest on their My Stay page or by the Front Desk, and goes to
 * Housekeeping. Housekeeping moves it forward one step at a time, Pending > Accepted >
 * In Progress > Completed; Pending or Accepted ones can be cancelled. A defect found
 * on the job goes to Maintenance as a complaint, the queue that already does repairs.
 *
 * Failures throw RuntimeException with a message the person can act on.
 */
class HotelServiceRequestDesk
{
    /** $source: 'Guest' or 'Front Desk'. */
    public static function create(HotelBooking $booking, array $data, string $source, ?string $by, ?User $actor): HotelServiceRequest
    {
        if (!HotelServiceRequest::supported()) {
            throw new RuntimeException('Housekeeping requests are not available yet. Try again in a minute.');
        }
        if ($booking->status !== 'Checked In') {
            throw new RuntimeException('Housekeeping requests are for a guest who is staying (checked in).');
        }
        $type = HotelServiceRequest::normalizeType($data['type'] ?? null);
        if (!$type) {
            throw new RuntimeException('Pick what the guest needs.');
        }
        $notes = trim((string) ($data['notes'] ?? ''));
        if ($type === 'Other Housekeeping Help' && $notes === '') {
            throw new RuntimeException('Say what the guest needs.');
        }
        // One of each kind at a time: a second tap is the same request again.
        $duplicate = HotelServiceRequest::where('hotel_booking_id', $booking->hotel_booking_id)
            ->where('type', $type)
            ->whereIn('status', HotelServiceRequest::OPEN_STATUSES)
            ->exists();
        if ($duplicate && $type !== 'Other Housekeeping Help') {
            throw new RuntimeException('There is already an open ' . $type . ' request for this room.');
        }

        $request = HotelServiceRequest::create([
            'group_name'       => $booking->group_name,
            'faculty_id'       => $booking->faculty_id,
            'group_id'         => $booking->group_id,
            'hotel_booking_id' => $booking->hotel_booking_id,
            'room_name'        => $booking->room?->name,
            'guest_name'       => $booking->guest?->full_name,
            'type'             => $type,
            'preferred_time'   => mb_substr(trim((string) ($data['preferred_time'] ?? '')), 0, 60) ?: null,
            'notes'            => $notes !== '' ? mb_substr($notes, 0, 1000) : null,
            'source'           => $source === 'Guest' ? 'Guest' : 'Front Desk',
            'status'           => 'Pending',
            'requested_by'     => $by,
        ]);

        Notifier::serviceRequested($actor, $request);
        ActivityLog::record(
            $actor,
            ActivityLog::ROOM_INSPECTED,
            ($source === 'Guest' ? 'Guest ' . ($request->guest_name ?: '') . ' asked' : 'Front Desk asked')
                . ' Housekeeping for ' . $type . ' in ' . ($request->room_name ?: 'a room') . '.'
        );

        return $request;
    }

    /**
     * One step forward along FLOW, or Cancelled from Pending or Accepted. Skipping a
     * step is allowed forward (Pending straight to In Progress), going back is not.
     */
    public static function move(HotelServiceRequest $request, string $to, ?string $by, ?string $notes, ?User $actor): HotelServiceRequest
    {
        if (!in_array($to, HotelServiceRequest::STATUSES, true)) {
            throw new RuntimeException('That is not a request status.');
        }

        return DB::transaction(function () use ($request, $to, $by, $notes, $actor) {
            $request = HotelServiceRequest::whereKey($request->getKey())->lockForUpdate()->first();
            if (!$request->isOpen()) {
                throw new RuntimeException('This request is already ' . strtolower($request->status) . '.');
            }
            if ($to === 'Cancelled') {
                if ($request->status === 'In Progress') {
                    throw new RuntimeException('Housekeeping has started this one. Finish it instead of cancelling.');
                }
                $request->cancelled_at = now();
            } else {
                $from = array_search($request->status, HotelServiceRequest::FLOW, true);
                $next = array_search($to, HotelServiceRequest::FLOW, true);
                if ($next === false || $next <= $from) {
                    throw new RuntimeException($request->status . ' cannot go back to ' . $to . '.');
                }
                if ($next >= 1 && !$request->accepted_at) {
                    $request->accepted_at = now();
                }
                if ($next >= 2 && !$request->started_at) {
                    $request->started_at = now();
                }
                if ($to === 'Completed') {
                    $request->completed_at = now();
                    $notes = trim((string) $notes);
                    if ($notes !== '') {
                        $request->completion_notes = mb_substr($notes, 0, 1000);
                    }
                }
            }
            $request->status = $to;
            if ($by && $to !== 'Cancelled') {
                $request->handled_by = $by;
            }
            $request->save();

            ActivityLog::record(
                $actor,
                ActivityLog::ROOM_INSPECTED,
                'Housekeeping request #' . $request->hotel_service_request_id . ' (' . $request->type . ', '
                    . ($request->room_name ?: 'a room') . ') is now ' . $to . '.'
            );

            return $request;
        });
    }

    /**
     * A defect found while doing the request (a broken light, a leaking tap): filed
     * as a Maintenance complaint for the room, linked to the request. $unusable also
     * takes the room out of order until Maintenance clears it.
     */
    public static function reportDefect(HotelServiceRequest $request, array $issue, bool $unusable, ?User $actor): HotelComplaint
    {
        return DB::transaction(function () use ($request, $issue, $unusable, $actor) {
            $category = HotelComplaint::normalizeCategory($issue['category'] ?? 'Other Maintenance Problems');
            $complaint = HotelComplaint::create([
                'group_name'  => $request->group_name,
                'faculty_id'  => $request->faculty_id,
                'group_id'    => $request->group_id,
                'room_number' => $request->room_name,
                'guest_name'  => $request->guest_name,
                'category'    => $category,
                'department'  => HotelComplaint::departmentForCategory($category),
                'details'     => trim((string) ($issue['details'] ?? '')) ?: ('Found during ' . $request->type . '.'),
                'status'      => 'Pending',
                'filed_by'    => $actor?->name,
            ]);
            $request->hotel_complaint_id = $complaint->hotel_complaint_id;
            $request->save();

            $room = $request->booking?->room;
            if ($unusable && $room) {
                $room->status = 'Maintenance';
                $room->save();
            }

            Notifier::complaintFiled($actor, $complaint);
            ActivityLog::record(
                $actor,
                ActivityLog::ROOM_INSPECTED,
                'Reported ' . $category . ' in ' . ($request->room_name ?: 'a room') . ' during a housekeeping request and sent it to '
                    . $complaint->departmentLabel() . ($unusable ? ' (room out of order).' : '.')
            );

            return $complaint;
        });
    }
}
