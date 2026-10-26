<?php

namespace Tests\Unit;

use App\Models\HotelConcept;
use App\Support\HotelConceptDesk;
use PHPUnit\Framework\TestCase;

/**
 * Who may write a concept, status by status. Pure logic against an unsaved
 * model — no DB needed, since canEdit/canSubmit only ever read ->status.
 */
class HotelConceptDeskTest extends TestCase
{
    private function conceptWithStatus(string $status): HotelConcept
    {
        return new HotelConcept(['status' => $status]);
    }

    public function test_any_team_member_can_edit_a_draft(): void
    {
        $concept = $this->conceptWithStatus(HotelConceptDesk::STATUS_DRAFT);

        $this->assertTrue(HotelConceptDesk::canEdit($concept, true));
    }

    public function test_a_non_team_member_can_never_edit(): void
    {
        $concept = $this->conceptWithStatus(HotelConceptDesk::STATUS_DRAFT);

        $this->assertFalse(HotelConceptDesk::canEdit($concept, false));
        $this->assertFalse(HotelConceptDesk::canEdit(null, false));
    }

    public function test_a_submitted_concept_locks_immediately_for_the_whole_team(): void
    {
        $concept = $this->conceptWithStatus(HotelConceptDesk::STATUS_SUBMITTED);

        $this->assertFalse(HotelConceptDesk::canEdit($concept, true));
        $this->assertStringContainsString(
            'awaiting faculty review',
            HotelConceptDesk::editRefusal($concept, true)
        );
    }

    public function test_revision_required_reopens_editing(): void
    {
        $concept = $this->conceptWithStatus(HotelConceptDesk::STATUS_NEEDS_REVISION);

        $this->assertTrue(HotelConceptDesk::canEdit($concept, true));
    }

    public function test_an_approved_concept_stays_locked_for_good(): void
    {
        $concept = $this->conceptWithStatus(HotelConceptDesk::STATUS_APPROVED);

        $this->assertFalse(HotelConceptDesk::canEdit($concept, true));
        $this->assertTrue($concept->isLocked());
    }

    public function test_an_empty_slot_is_writable_by_any_team_member(): void
    {
        $this->assertTrue(HotelConceptDesk::canEdit(null, true));
    }

    public function test_submit_is_refused_once_both_concepts_are_already_under_review(): void
    {
        $concepts = collect([
            $this->conceptWithStatus(HotelConceptDesk::STATUS_SUBMITTED)->forceFill(['slot' => 1]),
            $this->conceptWithStatus(HotelConceptDesk::STATUS_SUBMITTED)->forceFill(['slot' => 2]),
        ]);

        $this->assertFalse(HotelConceptDesk::canSubmit($concepts, true));
    }

    public function test_front_desk_can_submit_two_ready_concepts(): void
    {
        $concepts = collect([
            $this->conceptWithStatus(HotelConceptDesk::STATUS_DRAFT)->forceFill(['slot' => 1]),
            $this->conceptWithStatus(HotelConceptDesk::STATUS_DRAFT)->forceFill(['slot' => 2]),
        ]);

        $this->assertTrue(HotelConceptDesk::canSubmit($concepts, true));
    }

    /**
     * A teammate without the role may have written every word of both
     * concepts — editing is open to the whole team — but handing them to
     * faculty stays Front Desk's call.
     */
    public function test_a_teammate_without_front_desk_cannot_submit_even_with_two_ready_concepts(): void
    {
        $concepts = collect([
            $this->conceptWithStatus(HotelConceptDesk::STATUS_DRAFT)->forceFill(['slot' => 1]),
            $this->conceptWithStatus(HotelConceptDesk::STATUS_DRAFT)->forceFill(['slot' => 2]),
        ]);

        $this->assertFalse(HotelConceptDesk::canSubmit($concepts, false));
        $this->assertStringContainsString(
            'Front Desk',
            HotelConceptDesk::submitRefusal($concepts, false)
        );
    }
}
