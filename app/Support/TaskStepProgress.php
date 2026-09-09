<?php

namespace App\Support;

/**
 * How far a team is through a task, counted the way a task is actually shaped.
 *
 * A task — Task 01, Task 02 — is one shared step of the project, and the
 * activities inside it are one role's share each: Front Desk proposes the hotel
 * concepts while Room Management sets up its categories and Restaurant builds
 * its menu. Every activity is its own row, submitted and reviewed on its own, so
 * "is Task 01 done?" is a question about the whole step rather than about any
 * one of them: it is done when every role given an activity in it has handed
 * theirs in.
 *
 * Rows are matched back to their step by title, because that is all a saved row
 * keeps — it is a copy of a checklist entry made when it was assigned.
 */
class TaskStepProgress
{
    /**
     * Which step each activity title belongs to, as a lowercase title => index
     * map. Built once: the checklist does not change inside a request.
     *
     * @return array<string, int>
     */
    private static function stepByTitle(): array
    {
        static $map = null;

        if ($map === null) {
            $map = [];
            foreach (TaskChecklist::allByStep() as $step => $tasks) {
                foreach ($tasks as $task) {
                    $map[mb_strtolower($task['title'])] = $step;
                }
            }
        }

        return $map;
    }

    /**
     * One team's progress, from the task rows the faculty screens already built.
     *
     * Each row needs a title and a status; anything else on it is ignored.
     * 'archived' is this app's "submitted" (see the tasks.complete route), so an
     * activity counts as finished once it is in with faculty — being sent back
     * puts it to 'active' again and the step reopens with it.
     *
     * @param  iterable<array{title?: string, status?: string}>  $rows
     * @return array{steps: array<int, array{done: int, total: int}>, finished: int, total: int}
     */
    public static function forRows(iterable $rows): array
    {
        $byStep = self::stepByTitle();
        $steps = [];

        foreach ($rows as $row) {
            $title = mb_strtolower((string) ($row['title'] ?? ''));
            if (!isset($byStep[$title])) {
                // A row whose title is not on the checklist — a one-off task a
                // faculty wrote by hand — belongs to no step and is counted in
                // none rather than inventing one for it.
                continue;
            }

            $step = $byStep[$title];
            $steps[$step] ??= ['done' => 0, 'total' => 0];
            $steps[$step]['total']++;

            if (($row['status'] ?? null) === 'archived') {
                $steps[$step]['done']++;
            }
        }

        ksort($steps);

        $finished = count(array_filter(
            $steps,
            fn (array $step) => $step['total'] > 0 && $step['done'] === $step['total']
        ));

        return [
            'steps' => $steps,
            'finished' => $finished,
            'total' => count($steps),
        ];
    }
}
