<?php

namespace Tests\Unit;

use App\Support\DesignTaskChain;
use App\Support\HotelConceptDesk;
use App\Support\TaskChecklist;
use PHPUnit\Framework\TestCase;

/**
 * Tasks are website customization, one numbered task per department, in
 * TaskChecklist::STEP_ROLES order. No simulation task survives on the
 * checklist, and no department outside STEP_ROLES gets a task. Pure arrays —
 * no app, no DB.
 */
class TaskChecklistStepsTest extends TestCase
{
    public function test_there_are_exactly_four_tasks_one_per_step_role(): void
    {
        $steps = TaskChecklist::allByStep();

        $this->assertCount(4, $steps);
        $this->assertSame(TaskChecklist::STEP_ROLES, array_map(
            fn ($stepData) => $stepData['role'],
            $steps
        ));
    }

    public function test_task_01_is_front_desk_and_holds_the_hotel_concept_first(): void
    {
        $steps = TaskChecklist::allByStep();

        $this->assertSame('front_desk', $steps[0]['role']);
        $firstTask = $steps[0]['tasks'][0];
        $this->assertSame(HotelConceptDesk::TASK_TITLE, $firstTask['title']);
    }

    public function test_no_legacy_simulation_title_remains_on_the_checklist(): void
    {
        foreach (TaskChecklist::all() as $role => $tasks) {
            foreach ($tasks as $task) {
                $this->assertFalse(
                    TaskChecklist::isSimulationTitle($task['title']),
                    "{$task['title']} under {$role} is a retired simulation task."
                );
            }
        }
    }

    public function test_maintenance_has_no_task(): void
    {
        $all = TaskChecklist::all();

        $this->assertArrayHasKey('maintenance', $all, 'all() lists every role from HotelTemplateBuilder::ROLES.');
        $this->assertSame([], $all['maintenance'], 'Maintenance owns no page, so it has no customization task.');
        $this->assertNotContains('maintenance', TaskChecklist::STEP_ROLES);
    }

    /** Moving to one-task-per-role must not lose or duplicate anything. */
    public function test_every_checklist_entry_lands_in_exactly_one_step(): void
    {
        $placed = [];
        foreach (TaskChecklist::allByStep() as $step => $stepData) {
            foreach ($stepData['tasks'] as $task) {
                $placed[] = $stepData['role'] . '|' . $task['title'];
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

    /**
     * Task 01's step is 0, which is falsy — a caller has to reject on null, not
     * on truthiness, or Task 01 reads as "no step" everywhere a falsy check is
     * used to test for one.
     */
    public function test_the_concept_step_is_zero_and_not_null(): void
    {
        $step = TaskChecklist::stepForTitle(HotelConceptDesk::TASK_TITLE);

        $this->assertSame(0, $step);
        $this->assertNotNull($step);
        $this->assertFalse((bool) $step, 'Step 0 is falsy: held-title checks must reject on null, never on truthiness.');
    }

    public function test_an_unknown_title_has_no_step(): void
    {
        $this->assertNull(TaskChecklist::stepForTitle('Something A Faculty Typed By Hand'));
    }

    public function test_a_retired_simulation_title_has_no_step(): void
    {
        $this->assertNull(TaskChecklist::stepForTitle('Check a Guest Out'));
    }

    public function test_step_lookup_ignores_title_case(): void
    {
        $this->assertSame(
            TaskChecklist::stepForTitle('Build Your Menu'),
            TaskChecklist::stepForTitle('build your menu')
        );
    }

    /** The chained follow-up is Room Management's work, so it carries Room Management's step. */
    public function test_the_chained_room_category_follow_up_carries_room_managements_step(): void
    {
        $roomStep = array_search('room_management', TaskChecklist::STEP_ROLES, true);

        $this->assertSame(
            $roomStep,
            TaskChecklist::stepForTitle(DesignTaskChain::NEXT['room_management']['title'])
        );
    }

    public function test_is_site_title_is_true_for_every_checklist_entry(): void
    {
        foreach (TaskChecklist::all() as $tasks) {
            foreach ($tasks as $task) {
                $this->assertTrue(TaskChecklist::isSiteTitle($task['title']));
            }
        }
    }

    public function test_is_site_title_is_false_for_a_retired_simulation_task(): void
    {
        $this->assertFalse(TaskChecklist::isSiteTitle('Check a Guest Out'));
        $this->assertTrue(TaskChecklist::isSimulationTitle('check a guest out'));
    }

    public function test_every_activity_opens_a_page_its_role_can_edit_except_the_concept(): void
    {
        foreach (TaskChecklist::all() as $role => $tasks) {
            foreach ($tasks as $task) {
                $area = TaskChecklist::areaFor($task['title'], $role);

                if (TaskChecklist::isConceptTitle($task['title'])) {
                    $this->assertNull($area, 'The concept is written on the dashboard, not in the template.');
                    continue;
                }

                $this->assertNotNull($area, "{$task['title']} has no template area.");
                $this->assertContains(
                    $area['page'],
                    \App\Support\HotelTemplateBuilder::editablePagesForRole($role),
                    "{$task['title']} opens {$area['page']}, a page {$role} cannot edit."
                );
            }
        }
    }

    public function test_task_areas_point_at_the_right_sections(): void
    {
        $this->assertSame(['page' => 'home', 'section' => 'header'], TaskChecklist::areaFor('Brand Your Hotel', 'front_desk'));
        $this->assertSame(['page' => 'rooms', 'section' => null], TaskChecklist::areaFor('Build Your Room Types', 'room_management'));
        $this->assertSame(['page' => 'restaurant', 'section' => null], TaskChecklist::areaFor('Build Your Menu', 'restaurant_management'));
        $this->assertSame(['page' => 'amenities', 'section' => null], TaskChecklist::areaFor('Build the Amenities Page', 'housekeeping'));
        $this->assertSame(['page' => 'experience', 'section' => null], TaskChecklist::areaFor('Write the Experience Page', 'housekeeping'));
    }

    public function test_step_label_names_each_department_and_nothing_past_the_last(): void
    {
        $this->assertSame('Front Desk Customization', TaskChecklist::stepLabel(0));
        $this->assertSame('Housekeeping Customization', TaskChecklist::stepLabel(3));
        $this->assertNull(TaskChecklist::stepLabel(4));
    }
}
