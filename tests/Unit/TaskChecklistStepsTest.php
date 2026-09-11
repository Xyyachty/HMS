<?php

namespace Tests\Unit;

use App\Support\HotelConceptDesk;
use App\Support\TaskChecklist;
use PHPUnit\Framework\TestCase;

/**
 * Task 01 is the hotel concept and nothing else, and every other checklist
 * entry sits in exactly one later step. Pure arrays — no app, no DB.
 */
class TaskChecklistStepsTest extends TestCase
{
    public function test_task_01_holds_the_hotel_concept_alone(): void
    {
        $steps = TaskChecklist::allByStep();

        $this->assertArrayHasKey(0, $steps);
        $this->assertCount(1, $steps[0], 'Task 01 must hold one activity, not one per role.');
        $this->assertArrayHasKey(HotelConceptDesk::OWNING_ROLE, $steps[0]);
        $this->assertSame(HotelConceptDesk::TASK_TITLE, $steps[0][HotelConceptDesk::OWNING_ROLE]['title']);
    }

    public function test_no_other_role_has_work_in_task_01(): void
    {
        $steps = TaskChecklist::allByStep();

        $this->assertSame([HotelConceptDesk::OWNING_ROLE], array_keys($steps[0]));
    }

    public function test_the_concept_appears_in_no_other_step(): void
    {
        foreach (TaskChecklist::allByStep() as $step => $tasks) {
            foreach ($tasks as $role => $task) {
                if ($step === 0) {
                    continue;
                }
                $this->assertFalse(
                    TaskChecklist::isConceptTitle($task['title']),
                    "The hotel concept also appears in step {$step} under {$role}."
                );
            }
        }
    }

    /** Moving the concept out of the shared step must not lose or copy anything. */
    public function test_every_checklist_entry_lands_in_exactly_one_step(): void
    {
        $placed = [];
        foreach (TaskChecklist::allByStep() as $step => $tasks) {
            foreach ($tasks as $role => $task) {
                $placed[] = $role . '|' . $task['title'];
            }
        }

        $expected = [];
        foreach (TaskChecklist::all() as $role => $tasks) {
            foreach ($tasks as $task) {
                $expected[] = $role . '|' . $task['title'];
            }
        }

        sort($placed);
        sort($expected);

        $this->assertSame($expected, $placed);
        $this->assertSame(count($placed), count(array_unique($placed)), 'A task was placed in two steps.');
    }

    /** The site build still finishes before the simulation opens, for everyone at once. */
    public function test_site_work_all_sits_ahead_of_the_first_ops_step(): void
    {
        $firstOps = null;
        $lastSite = null;

        foreach (TaskChecklist::allByStep() as $step => $tasks) {
            foreach ($tasks as $task) {
                if (($task['scope'] ?? TaskChecklist::SCOPE_SITE) === TaskChecklist::SCOPE_OPS) {
                    $firstOps = $firstOps ?? $step;
                } else {
                    $lastSite = $step;
                }
            }
        }

        $this->assertNotNull($firstOps);
        $this->assertLessThan($firstOps, $lastSite, 'Site work must not run past the first ops step.');
    }

    public function test_every_role_after_front_desk_starts_at_task_02_or_later(): void
    {
        $steps = TaskChecklist::allByStep();
        $firstStepOf = [];

        foreach ($steps as $step => $tasks) {
            foreach ($tasks as $role => $task) {
                $firstStepOf[$role] = $firstStepOf[$role] ?? $step;
            }
        }

        foreach ($firstStepOf as $role => $step) {
            if ($role === HotelConceptDesk::OWNING_ROLE) {
                continue;
            }
            $this->assertGreaterThanOrEqual(
                TaskChecklist::CONCEPT_STEPS,
                $step,
                "{$role} still has work in Task 01."
            );
        }
    }
}
