<?php

namespace Tests\Unit;

use App\Models\Task;
use Illuminate\Support\Carbon;
use App\Support\HotelMenuAccess;
use PHPUnit\Framework\TestCase;

/**
 * When the menu is closed to design changes.
 *
 * 'archived' is this app's "submitted", and feedback_at is what separates a
 * submission nobody has answered from one that has been. Pure logic against
 * unsaved rows — resolving which task belongs to a team needs the database.
 */
class HotelMenuAccessTest extends TestCase
{
    /* A Carbon rather than now() or a string: both writing a cast date and reading
       one back from a string ask the connection for its date format, and there is no
       connection here. A date object is handed straight back. */
    private function answered(): Carbon
    {
        return Carbon::create(2026, 9, 12, 9, 0, 0);
    }

    private function task(string $status, $feedbackAt = null): Task
    {
        $task = new Task(['title' => HotelMenuAccess::TASK_TITLE, 'status' => $status]);
        $task->setRawAttributes(['status' => $status, 'feedback_at' => $feedbackAt], true);

        return $task;
    }

    public function test_a_submitted_menu_is_under_review(): void
    {
        $this->assertTrue(HotelMenuAccess::taskIsUnderReview($this->task('archived')));
    }

    public function test_a_menu_still_being_worked_on_is_not(): void
    {
        $this->assertFalse(HotelMenuAccess::taskIsUnderReview($this->task('active')));
    }

    /** Sent back for changes: 'active' again, carrying feedback. The menu reopens. */
    public function test_a_menu_sent_back_for_revision_is_not_under_review(): void
    {
        $this->assertFalse(HotelMenuAccess::taskIsUnderReview($this->task('active', $this->answered())));
    }

    /** Approved: still archived, but answered — it stays closed. */
    public function test_an_approved_menu_is_not_awaiting_a_verdict(): void
    {
        $this->assertFalse(HotelMenuAccess::taskIsUnderReview($this->task('archived', $this->answered())));
    }

    /** No task handed out yet must never lock the menu — the amenities list was. */
    public function test_no_task_leaves_the_menu_open(): void
    {
        $this->assertFalse(HotelMenuAccess::taskIsUnderReview(null));
    }

    public function test_the_lock_message_says_what_reopens_it(): void
    {
        $this->assertStringContainsString('review', HotelMenuAccess::lockMessage());
        $this->assertStringContainsString('send it back', HotelMenuAccess::lockMessage());
    }
}
