<?php

namespace App\Support;

use App\Models\StudentGroup;
use App\Models\Task;

/**
 * This student's own assigned tasks, as the Template Editor sidebar lists them.
 *
 * Read straight off the Task rows faculty's Set Task writes — there is no task
 * store of the editor's own. Set Task fans one activity out into a row per
 * member holding the role, so the rows are folded back to one entry per title;
 * where this student holds one of those copies, theirs is the one shown, since
 * its status is the one they are answerable for.
 *
 * Scoped to the roles this student holds, the same rule the dashboard's Task
 * panel applies. Retired simulation titles are left out
 * (Task::withoutSimulation), also as on the dashboard.
 */
class AssignedTaskList
{
    /**
     * @return list<array{
     *     id: int, step: ?int, step_label: ?string, title: string, role: string,
     *     role_label: string, due: ?string, status: string, status_label: string,
     *     page: ?string, section: ?string, is_concept: bool, url: ?string
     * }>
     */
    public static function forMember(?StudentGroup $membership): array
    {
        if (!$membership || !$membership->faculty_id) {
            return [];
        }

        /* Only this student's own work. The sidebar listed the whole team's
           tasks, so a Front Desk student was shown Housekeeping and Restaurant
           rows that were never theirs — and could not act on them either, since
           the card's url is null for a role they do not hold, leaving it inert
           on click. The dashboard's Task panel has always filtered this way.

           Expanded through rolesForPhase rather than matched against the stored
           seat keys directly: in the customization phase the housekeeping seat
           also covers maintenance, and those tasks really are this student's to
           edit. It is the same set $editorUrls is built from below, so every
           card that survives this filter has an editor to jump to. */
        $myRoles = $membership->roles->pluck('role')->all();
        $myCustomizationRoles = HotelTemplateBuilder::rolesForPhase(
            $myRoles,
            HotelTemplateBuilder::PHASE_CUSTOMIZATION
        );

        // Completed (archived) work drops off the sidebar — it is done, so it is
        // no longer something to jump to and customize. The dashboard's Task
        // panel is where finished work still shows.
        $rows = Task::where('faculty_id', $membership->faculty_id)
            ->forTeam($membership->group_name)
            ->whereIn('role', $myCustomizationRoles)
            ->where('status', 'active')
            ->withoutSimulation()
            ->orderBy('task_id')
            ->get();

        $studentId = (int) $membership->student_id;

        // Editors this student can open, by role — a task in a module they hold
        // jumps there so it opens editable rather than read-only in this one.
        $editorUrls = collect(HotelTemplateBuilder::modulesForRoles($myRoles))
            ->filter(fn ($module) => $module['editable'])
            ->mapWithKeys(fn ($module) => [$module['role'] => $module['customize_url']])
            ->all();

        $picked = $rows
            ->groupBy(fn (Task $task) => mb_strtolower(trim((string) $task->title)))
            ->map(fn ($copies) => $copies->first(fn (Task $task) => (int) $task->student_id === $studentId)
                ?? $copies->first());

        // Checklist position within a role, so a department's activities list in
        // the order faculty see them on Set Task rather than in creation order.
        $orderInRole = [];
        foreach (TaskChecklist::all() as $role => $entries) {
            foreach (array_values($entries) as $i => $entry) {
                $orderInRole[mb_strtolower($entry['title'])] = $i;
            }
        }

        return $picked
            ->map(function (Task $task) use ($editorUrls) {
                $title = (string) $task->title;
                $step = TaskChecklist::stepForTitle($title);
                $isConcept = TaskChecklist::isConceptTitle($title) || $task->is_hotel_concept;
                $area = $isConcept ? null : TaskChecklist::areaFor($title, (string) $task->role);

                [$status, $statusLabel] = match (true) {
                    $task->status === 'archived' => ['completed', 'Completed'],
                    $task->needs_revision => ['needs_revision', 'Needs Revision'],
                    default => ['in_progress', 'In Progress'],
                };

                $url = null;
                if ($isConcept) {
                    // The concepts are written on the dashboard's My Team section.
                    $url = route('students.dashboard', ['section' => 'group']);
                } elseif (isset($editorUrls[$task->role])) {
                    $url = $editorUrls[$task->role] . '?focus=' . $task->task_id;
                }

                return [
                    'id' => (int) $task->task_id,
                    'step' => $step,
                    'step_label' => $step === null ? null : 'TASK ' . str_pad((string) ($step + 1), 2, '0', STR_PAD_LEFT),
                    'title' => $title,
                    'role' => (string) $task->role,
                    'role_label' => $task->role_label,
                    'due' => $task->due_date?->format('M j'),
                    'status' => $status,
                    'status_label' => $statusLabel,
                    'page' => $area['page'] ?? null,
                    'section' => $area['section'] ?? null,
                    'is_concept' => $isConcept,
                    'url' => $url,
                ];
            })
            ->sortBy(fn (array $item) => [
                $item['step'] ?? PHP_INT_MAX,
                $orderInRole[mb_strtolower($item['title'])] ?? PHP_INT_MAX,
                $item['id'],
            ])
            ->values()
            ->all();
    }
}
