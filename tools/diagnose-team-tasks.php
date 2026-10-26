<?php

/*
 * Read-only: why a team's students do not see the tasks faculty set.
 *
 *   php artisan tinker tools/diagnose-team-tasks.php
 *
 * Set TEAM below (and FACULTY_EMAIL if more than one faculty has a team of
 * that name). Prints the team's memberships and roles, any other team each
 * student also sits in, and the team's task rows. Writes nothing.
 */

$TEAM = 'Team 3';
$FACULTY_EMAIL = null;

$facultyIds = \App\Models\StudentGroup::where('group_name', $TEAM)->distinct()->pluck('faculty_id');
if ($FACULTY_EMAIL) {
    $facultyIds = $facultyIds->intersect([\App\Models\User::where('email', $FACULTY_EMAIL)->first()?->faculty?->user_information_id]);
}

foreach ($facultyIds as $facultyId) {
    echo "=== {$TEAM} under faculty {$facultyId}\n";
    $members = \App\Models\StudentGroup::with(['roles', 'student.user'])
        ->where('group_name', $TEAM)->where('faculty_id', $facultyId)->get();

    foreach ($members as $m) {
        $all = \App\Models\StudentGroup::where('student_id', $m->student_id)->orderBy('student_group_id')->get(['student_group_id', 'group_name', 'faculty_id']);
        echo sprintf(
            "member student_id=%s user_id=%s name=%s roles=%s legacy_role=%s student.faculty_id=%s memberships=%s\n",
            $m->student_id,
            $m->student?->user_id ?? 'NULL',
            $m->student?->user?->name ?? '(no student row)',
            json_encode($m->roles->pluck('role')),
            $m->role ?? 'NULL',
            $m->student?->faculty_id ?? 'NULL',
            json_encode($all->map(fn ($g) => $g->student_group_id . ':' . $g->group_name . '@' . $g->faculty_id))
        );
    }

    $tasks = \App\Models\Task::where('group_name', $TEAM)->where('faculty_id', $facultyId)->orderBy('task_id')->get();
    echo 'tasks: ' . $tasks->count() . "\n";
    foreach ($tasks as $t) {
        echo sprintf("  task %s role=%s student_id=%s assigned_to=%s status=%s title=%s\n",
            $t->task_id, $t->role, $t->student_id ?? 'NULL', $t->assigned_to ?? 'NULL', $t->status, $t->title);
    }

    // Set Task opens the customization tasks only once a concept is past draft.
    $concepts = \App\Models\HotelConcept::where('group_name', $TEAM)->where('faculty_id', $facultyId)->orderBy('slot')->get();
    echo 'concepts: ' . json_encode($concepts->map(fn ($k) => 'slot' . $k->slot . '=' . ($k->status ?: 'draft') . ' name="' . $k->group_name . '"')) . "\n";
    echo 'Set Task unlocked: ' . (\App\Support\HotelConceptDesk::hasSubmittedConcept($TEAM, (int) $facultyId) ? 'yes' : 'NO - concept still draft') . "\n";

    $elsewhere =\App\Models\Task::where('group_name', $TEAM)->where('faculty_id', '!=', $facultyId)->count();
    echo "same-name tasks under other faculty: {$elsewhere}\n";
}
