{{-- Task groups list, rendered from $studentRoles / $myRoleTasks / $myCompletedTasks /
     $groupMembership. Included inline by students.dashboard and re-rendered as-is by the
     GET students/tasks/live poll endpoint, so a task faculty just assigned (or a
     verdict they just left) reaches this list without the student reloading the page. --}}
@php
    /*
     * Group every task (active + completed) under its numbered TASK.
     * TaskChecklist::STEP_ROLES says which role owns which step, so a task's
     * title (or failing that, its role) decides which group it falls in. A
     * row that matches neither — hand-written, or a role with no numbered
     * step, e.g. maintenance — falls into an 'other' bucket shown last.
     */
    $taskGroups = collect();
    $addToGroup = function ($task, string $rowStatus, int $rowPercent) use (&$taskGroups) {
        $step = \App\Support\TaskChecklist::stepForTitle($task->title);
        if ($step === null) {
            $found = array_search($task->role, \App\Support\TaskChecklist::STEP_ROLES, true);
            $step = $found === false ? null : $found;
        }
        $key = $step ?? 'other';

        if (!$taskGroups->has($key)) {
            $taskGroups->put($key, (object) ['step' => $key, 'role' => $task->role, 'rows' => collect()]);
        }
        $taskGroups->get($key)->rows->push((object) [
            'task' => $task,
            'status' => $rowStatus,
            'percent' => $rowPercent,
        ]);
    };

    foreach ($myRoleTasks as $task) {
        $activities = $task->activityList();
        $done  = $task->activitiesDoneCount();
        $total = count($activities);

        if ($task->needs_revision) {
            $rowStatus  = 'revision';
            $rowPercent = $total > 0 ? (int) round(($done / $total) * 100) : 50;
        } elseif ($total > 0 && $done > 0) {
            $rowStatus  = 'in_progress';
            $rowPercent = (int) round(($done / $total) * 100);
        } else {
            $rowStatus  = 'not_started';
            $rowPercent = 0;
        }
        $addToGroup($task, $rowStatus, $rowPercent);
    }
    foreach ($myCompletedTasks as $task) {
        $addToGroup($task, 'completed', 100);
    }

    // Numbered steps in order, the 'other' bucket last.
    $taskGroups = $taskGroups->sortBy(fn ($g) => $g->step === 'other' ? PHP_INT_MAX : $g->step)->values();

    $homeRoleLabels = [
        'front_desk'            => 'Front Desk',
        'restaurant_management' => 'Restaurant Services',
        'room_management'       => 'Room Management',
        'maintenance'           => 'Maintenance',
        'housekeeping'          => 'Housekeeping',
    ];
    $roleIcons = [
        'front_desk'            => 'mdi:desk',
        'restaurant_management' => 'mdi:silverware-fork-knife',
        'room_management'       => 'mdi:bed-outline',
        'maintenance'           => 'mdi:broom',
        'housekeeping'          => 'mdi:sparkles',
    ];
    $homeRoleTints = [
        'front_desk'            => ['bg' => 'bg-orange-50',  'text' => 'text-orange-500',  'bar' => 'bg-orange-500'],
        'restaurant_management' => ['bg' => 'bg-amber-50',   'text' => 'text-amber-500',   'bar' => 'bg-amber-500'],
        'room_management'       => ['bg' => 'bg-pink-50',    'text' => 'text-brand',       'bar' => 'bg-brand'],
        'maintenance'           => ['bg' => 'bg-violet-50',  'text' => 'text-violet-500',  'bar' => 'bg-violet-500'],
        'housekeeping'          => ['bg' => 'bg-teal-50',    'text' => 'text-teal-500',    'bar' => 'bg-teal-500'],
    ];
    $homeTint = fn($role, $key) => $homeRoleTints[$role][$key] ?? ($key === 'bar' ? 'bg-slate-400' : ($key === 'text' ? 'text-slate-400' : 'bg-slate-50'));

    $groupStatusMeta = [
        'not_started' => ['label' => 'Not Started',    'badge' => 'bg-slate-100 text-slate-500', 'icon' => 'mdi:circle-outline'],
        'in_progress' => ['label' => 'In Progress',    'badge' => 'bg-brand-soft text-brand',    'icon' => 'mdi:progress-clock'],
        'revision'    => ['label' => 'Needs Revision', 'badge' => 'bg-amber-50 text-amber-700',  'icon' => 'mdi:message-alert-outline'],
        'completed'   => ['label' => 'Completed',      'badge' => 'bg-emerald-50 text-emerald-600', 'icon' => 'mdi:check-decagram-outline'],
    ];

    // Each task card is labelled by its role's initials and its place in
    // the group — 'FD TASK 1'. A role with no entry falls back to the
    // first two letters of its key.
    $roleTaskCodes = [
        'front_desk'            => 'FD',
        'room_management'       => 'RM',
        'restaurant_management' => 'RS',
        'housekeeping'          => 'HK',
        'maintenance'           => 'MT',
    ];
@endphp

@if(!empty($studentRoles))
    @foreach($taskGroups as $taskGroup)
        @php
            // Active work first (the concept task sorts first among those
            // via conceptFirst()), completed work after.
            $groupRows = $taskGroup->rows->sortBy(fn ($r) => $r->status === 'completed' ? 1 : 0)->values();

            $groupTotal       = $groupRows->count();
            $groupDone        = $groupRows->where('status', 'completed')->count();
            $groupHasRevision = $groupRows->contains(fn ($r) => $r->status === 'revision');
            $groupPercent     = $groupTotal > 0 ? (int) round(($groupDone / $groupTotal) * 100) : 0;

            if ($groupTotal > 0 && $groupDone === $groupTotal) {
                $groupStatus = 'completed';
            } elseif ($groupHasRevision) {
                $groupStatus = 'revision';
            } elseif ($groupRows->every(fn ($r) => $r->status === 'not_started')) {
                $groupStatus = 'not_started';
            } else {
                $groupStatus = 'in_progress';
            }

            $groupDue = $groupRows->pluck('task.due_date')->filter()->max();

            $groupLabel     = $taskGroup->step === 'other' ? 'OTHER TASKS' : ('TASK ' . str_pad($taskGroup->step + 1, 2, '0', STR_PAD_LEFT));
            $groupRole      = is_int($taskGroup->step) ? (\App\Support\TaskChecklist::STEP_ROLES[$taskGroup->step] ?? $taskGroup->role) : $taskGroup->role;
            $groupRoleLabel = $homeRoleLabels[$groupRole] ?? ucfirst(str_replace('_', ' ', $groupRole));
            $groupMeta      = $groupStatusMeta[$groupStatus];

            // Ring geometry: only the dash offset varies with progress.
            $ringRadius        = 26;
            $ringCircumference = round(2 * M_PI * $ringRadius, 2);
            $ringOffset        = round($ringCircumference * (1 - $groupPercent / 100), 2);

            $taskRowIndex = 0;
        @endphp
        <section data-task-group data-group-status="{{ $groupStatus }}" class="rounded-3xl border border-pink-100 bg-white overflow-hidden mb-6">
            {{-- One rounded card per TASK group, with the summary band and the cards
                 it counts inside the same border. Held apart they read as two
                 unrelated blocks, and with several groups on the page it is not
                 obvious which cards a summary is summarising. --}}
            <div class="bg-gradient-to-r from-brand-soft to-white border-b border-pink-100 px-5 sm:px-7 py-6">
                <div class="grid grid-cols-1 lg:grid-cols-[1.7fr_1fr_1fr_1fr] gap-6 lg:gap-0">
                    <div class="flex items-center lg:pr-6">
                        <div class="min-w-0">
                            <p class="text-lg font-extrabold text-slate-900 tracking-wide">{{ $groupLabel }}</p>
                            <span class="inline-flex items-center px-2.5 py-1 mt-1 rounded-lg text-[11px] font-bold {{ $homeTint($groupRole, 'bg') }} {{ $homeTint($groupRole, 'text') }}">
                                {{ $groupRoleLabel }}
                            </span>
                            <p class="text-[12px] text-slate-500 mt-2">Complete all the tasks below to fulfill the requirements for {{ $groupLabel }}.</p>
                        </div>
                    </div>
                    <div class="lg:border-l lg:border-pink-100 lg:pl-6 flex flex-col justify-center">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Progress %</p>
                        <div class="flex items-center gap-3 mt-1.5">
                            <svg width="56" height="56" viewBox="0 0 60 60" class="shrink-0 -rotate-90">
                                <circle cx="30" cy="30" r="{{ $ringRadius }}" fill="none" stroke="#F2E9E7" stroke-width="6"></circle>
                                <circle data-summary-ring cx="30" cy="30" r="{{ $ringRadius }}" fill="none" stroke="#7B1730" stroke-width="6"
                                        stroke-linecap="round" stroke-dasharray="{{ $ringCircumference }}" stroke-dashoffset="{{ $ringOffset }}"></circle>
                            </svg>
                            <div>
                                <p class="text-2xl font-extrabold text-slate-900 leading-none" data-summary-percent>{{ $groupPercent }}%</p>
                                <p class="text-[12px] text-slate-500 mt-1" data-summary-count>{{ $groupDone }} of {{ $groupTotal }} tasks completed</p>
                            </div>
                        </div>
                    </div>
                    <div class="lg:border-l lg:border-pink-100 lg:pl-6 flex flex-col justify-center">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Status</p>
                        <span data-summary-status
                              class="inline-flex items-center gap-1.5 mt-2 w-fit px-3 py-1.5 rounded-lg text-[12px] font-bold {{ $groupMeta['badge'] }}">
                            <span class="iconify text-sm" data-icon="{{ $groupMeta['icon'] }}"></span>
                            {{ $groupMeta['label'] }}
                        </span>
                    </div>
                    <div class="lg:border-l lg:border-pink-100 lg:pl-6 flex flex-col justify-center">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Due Date</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="iconify text-slate-400 text-base" data-icon="mdi:calendar-blank-outline"></span>
                            @if($groupDue)
                                <p class="text-[13px] font-semibold text-slate-700">{{ $groupDue->format('M j, Y') }}</p>
                            @else
                                <p class="text-[13px] text-slate-400">No due date set</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- A card grid, not a table: one card per task, three up on desktop
                 and stacked below that. Both the grid and the empty state are
                 rendered, and one of them is hidden — faculty can close the
                 concept task while this page is open, and the poller has to be
                 able to swap them without a reload. --}}
            {{-- The page's own background, kept behind the cards now that they sit
                 inside the group rather than on the page, so white cards still read
                 as cards instead of dissolving into a white panel. --}}
            <div class="bg-surface px-5 sm:px-7 py-5">
                {{-- Also the drop target settleConceptTaskRow() appends the
                     settled concept card to, so it lands in the same grid. --}}
                <div data-completed-list class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
                    @foreach($groupRows as $row)
                        @php
                            $task = $row->task;
                            $taskRowIndex++;
                            $isOverdue   = $task->due_date && $task->due_date->isPast();
                            $isCompleted = $row->status === 'completed';
                            /* Cards are per member; the submit button belongs on this
                               student's own card, on an unclaimed one, and on a card whose
                               named student has since given the role up — the team was
                               reshuffled and the work would otherwise be unreachable by
                               anybody. The submit route asks the same question. */
                            $isMine        = !$isCompleted && \App\Support\TaskClaim::mayWork($task, auth()->user(), $groupMembership ?? null);
                            $needsRevision = $row->status === 'revision';
                            $rowMeta       = $groupStatusMeta[$row->status];
                            $rowModule     = \App\Support\HotelTemplateBuilder::modulesForRoles([$task->role])[0] ?? null;
                            $rowDetailId   = 'taskDetail' . $task->task_id;
                            // The instructions dialog reads its heading off the drawer it
                            // shows, so the card carries its own copy: the live poller
                            // reprints this markup wholesale, and anything JS held apart
                            // from it would go stale against the reprint.
                            $rowRoleLabel  = $homeRoleLabels[$task->role] ?? $task->role_label;
                            $rowDueLabel   = $task->due_date
                                                ? $task->due_date->format('M j, Y') . ' · ' . $task->due_date->format('g:i A')
                                                : 'No due date';
                            // 'FD TASK 1' — the role's initials plus the task's place in
                            // this group, which is what the card is known by.
                            $rowCode       = ($roleTaskCodes[$task->role] ?? strtoupper(substr($task->role, 0, 2)))
                                             . ' TASK ' . $taskRowIndex;
                        @endphp
                        {{-- The concept task is not a one-line tick: the whole proposal is
                             written on it, so its card keeps #conceptPanelCard —
                             paintTeamHeaderConcept() hides the card by that id once faculty
                             has chosen, and settleConceptTaskRow() removes it outright. --}}
                        <div @if($task->is_hotel_concept) id="conceptPanelCard" data-task-title="{{ $task->title }}" @endif
                             data-task-card
                             data-task-id="{{ $task->task_id }}"
                             data-task-status="{{ $row->status }}"
                             data-task-role="{{ $task->role }}"
                             class="task-card bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                            <div class="flex flex-wrap items-start gap-3 px-4 sm:px-5 pt-4 pb-3">
                                <div class="min-w-0 flex-1 basis-[140px]">
                                    <p class="text-[14px] font-bold leading-snug {{ $isCompleted ? 'text-slate-400' : 'text-slate-800' }}">{{ $task->title }}</p>
                                    <p data-task-code class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mt-1">{{ $rowCode }}</p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0 ml-auto">
                                    <span data-row-status-badge class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold whitespace-nowrap {{ $rowMeta['badge'] }}">
                                        {{ $rowMeta['label'] }}
                                    </span>
                                    {{-- Proceed opens the task itself: a website task opens the
                                         editor, an operations task opens Simulation, and the
                                         concept task goes to My Team, where the proposals are
                                         written. A task with no area of its own opens its own
                                         details, which is all there is to go to. --}}
                                    @if($task->is_hotel_concept)
                                        <button type="button" onclick="showSection('group')"
                                                title="Write the concepts in My Team"
                                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl brand-gradient text-white text-[12px] font-bold shadow-md shadow-brand/20 hover:opacity-90 transition whitespace-nowrap">
                                            Proceed
                                            <span class="iconify text-sm" data-icon="mdi:arrow-right"></span>
                                        </button>
                                    @elseif($rowModule)
                                        <a href="{{ $rowModule['editable'] ? $rowModule['customize_url'] : $rowModule['simulation_url'] }}"
                                           title="Open {{ $rowModule['label'] }}"
                                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl brand-gradient text-white text-[12px] font-bold shadow-md shadow-brand/20 hover:opacity-90 transition whitespace-nowrap">
                                            Proceed
                                            <span class="iconify text-sm" data-icon="mdi:arrow-right"></span>
                                        </a>
                                    @else
                                        {{-- Nowhere of its own to open, so it opens what it has:
                                             the same instructions dialog as the eye. --}}
                                        <button type="button" onclick="openTaskInstructions('{{ $rowDetailId }}')"
                                                title="Open {{ $task->title }}"
                                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl brand-gradient text-white text-[12px] font-bold shadow-md shadow-brand/20 hover:opacity-90 transition whitespace-nowrap">
                                            Proceed
                                            <span class="iconify text-sm" data-icon="mdi:arrow-right"></span>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            {{-- The rest of what the table columns used to carry: due date,
                                 the instructions button and the submit button. A per-task
                                 percent used to sit here too; the group summary card above
                                 is the only progress figure now. --}}
                            <div class="flex items-center justify-between gap-3 px-4 sm:px-5 pb-3.5">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="iconify text-slate-300 text-sm shrink-0" data-icon="mdi:calendar-blank-outline"></span>
                                    @if($task->due_date)
                                        <p class="text-[11px] font-semibold truncate {{ $isOverdue && !$isCompleted ? 'text-red-500' : 'text-slate-500' }}">
                                            {{ $task->due_date->format('M j, Y') }} &middot; {{ $task->due_date->format('g:i A') }}
                                        </p>
                                    @else
                                        <p class="text-[11px] font-semibold text-slate-300">No due date</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" onclick="openTaskInstructions('{{ $rowDetailId }}')"
                                            title="View instructions" aria-label="View instructions for {{ $task->title }}"
                                            class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-400 hover:text-brand hover:border-brand/40 transition-colors">
                                        <span class="iconify text-base" data-icon="mdi:eye-outline"></span>
                                    </button>
                                    @if($isCompleted)
                                        <span class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center">
                                            <span class="iconify text-base" data-icon="mdi:check"></span>
                                        </span>
                                    @elseif($task->is_hotel_concept)
                                        {{-- Nothing to tick here: the verdict closes this task, and
                                             Proceed above is the only way into it. --}}
                                    @elseif($isMine)
                                        {{-- The real affordance: marks this task done, or resubmits
                                             it when faculty has sent it back. --}}
                                        <form method="POST" action="{{ route('students.tasks.complete', $task) }}" class="shrink-0 leading-none">
                                            @csrf
                                            <button type="submit"
                                                    title="{{ $needsRevision ? 'Resubmit' : 'Mark as done' }}"
                                                    aria-label="{{ $needsRevision ? 'Resubmit' : 'Mark' }} {{ $task->title }}"
                                                    class="w-8 h-8 rounded-full flex items-center justify-center transition
                                                        {{ $needsRevision
                                                            ? 'text-amber-700 bg-amber-50 border border-amber-200 hover:bg-amber-100'
                                                            : 'text-brand bg-brand-soft border border-brand/10 hover:bg-brand/10' }}">
                                                <span class="iconify text-base" data-icon="{{ $needsRevision ? 'mdi:send-outline' : 'mdi:check' }}"></span>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[10px] font-semibold text-slate-300 whitespace-nowrap"
                                              title="Assigned to {{ $task->assignedTo?->name ?? 'a teammate' }}">{{ $task->assignedTo?->first_name ?? "Teammate's" }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- The body of the instructions dialog: description, the Steps
                                 checklist and any faculty feedback. It is parked in the card
                                 rather than in the dialog because the checklist's change
                                 handler finds the card it belongs to by walking up from the
                                 panel, and because the poller reprints it with fresh state.
                                 openTaskInstructions() lifts this node into the dialog and
                                 puts it back on close, so it stays hidden here either way. --}}
                            <div class="task-detail hidden px-4 sm:px-5 py-4 bg-slate-50/60 border-t border-slate-100"
                                 id="{{ $rowDetailId }}"
                                 data-detail-name="{{ $task->title }}"
                                 data-detail-code="{{ $rowCode }}"
                                 data-detail-role="{{ $rowRoleLabel }}"
                                 data-detail-status="{{ $rowMeta['label'] }}"
                                 data-detail-due="{{ $rowDueLabel }}">
                                <div class="space-y-3">
                                    @if($task->description)
                                        {{-- pre-line: the description carries the task's four activities as numbered
                                             lines, and they have to survive as lines. --}}
                                        <p class="text-[13px] text-slate-500 leading-relaxed whitespace-pre-line">{{ $task->description }}</p>
                                    @endif
                                    @php $activities = $task->activityList(); @endphp
                                    @if($activities)
                                        {{-- This role's own activity, broken into the four steps it
                                             is done in. Ticked as the student works, and all four
                                             have to be ticked before it can be handed in - the
                                             submit route checks the same thing the button does. --}}
                                        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5"
                                             data-activity-panel data-task="{{ $task->task_id }}">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1">
                                                    <span class="iconify text-xs" data-icon="mdi:format-list-checks"></span>
                                                    Steps
                                                </p>
                                                <p class="text-[10px] font-bold text-slate-400" data-activity-count>
                                                    {{ $task->activitiesDoneCount() }} of {{ count($activities) }} done
                                                </p>
                                            </div>
                                            <ul class="mt-2 space-y-1.5">
                                                @foreach($activities as $i => $activity)
                                                    <li class="flex items-start gap-2">
                                                        <input type="checkbox"
                                                               class="mt-0.5 rounded border-slate-300 text-brand focus:ring-brand/30 disabled:opacity-50"
                                                               data-activity-check
                                                               data-index="{{ $i }}"
                                                               @checked($activity['done'])
                                                               @disabled($task->status !== 'active')>
                                                        <span class="text-[13px] leading-relaxed {{ $activity['done'] ? 'text-slate-400 line-through' : 'text-slate-600' }}"
                                                              data-activity-text>{{ $i + 1 }}. {{ $activity['text'] }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            @if($task->status === 'active')
                                                <p class="text-[11px] text-slate-400 mt-2" data-activity-hint>
                                                    {{ $task->activitiesComplete()
                                                        ? 'All four done - you can submit this task.'
                                                        : 'Tick each step as you finish it. All four are needed before you can submit.' }}
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                    @if($needsRevision)
                                        {{-- Sent back by faculty: active again, but carrying feedback. --}}
                                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700 flex items-center gap-1">
                                                <span class="iconify text-xs" data-icon="mdi:message-alert-outline"></span>
                                                Faculty feedback{{ $task->revision_count > 1 ? ' · revision ' . $task->revision_count : '' }}
                                            </p>
                                            <p class="text-xs text-amber-800 mt-1 whitespace-pre-line">{{ $task->feedback }}</p>
                                            @if($task->feedback_at)
                                                <p class="text-[10px] text-amber-600 mt-1">{{ $task->feedback_at->diffForHumans() }}</p>
                                            @endif
                                        </div>
                                    @endif
                                    @if($task->is_hotel_concept)
                                        {{-- The panel itself is in My Team, where the concepts are
                                             written; two containers with the same id would leave
                                             paintHotelConcepts() painting only whichever the browser
                                             matched first. --}}
                                        <button type="button" onclick="showSection('group')"
                                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-[13px] font-bold text-slate-700 hover:bg-slate-50 hover:text-brand transition-colors">
                                            <span class="iconify text-base" data-icon="mdi:lightbulb-outline"></span>
                                            Write the concepts in My Team
                                        </button>
                                    @elseif(!$task->description && !$needsRevision)
                                        <p class="text-[13px] text-slate-400">No extra details on this task.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="text-[13px] text-slate-500 mt-4">
                    Showing <span data-group-visible>{{ $groupTotal }}</span> of {{ $groupTotal }} tasks under {{ $groupLabel }}
                </p>
            </div>
        </section>
    @endforeach

    <div id="taskFilterEmpty" class="bg-white rounded-2xl border border-slate-100 px-6 py-10 text-center hidden">
        <p class="text-sm font-bold text-slate-500">No tasks match this filter</p>
        <p class="text-xs text-slate-400 mt-1">Pick a different status.</p>
    </div>

    <div id="activeTasksEmpty" class="bg-white rounded-2xl border border-slate-100 px-6 py-12 text-center{{ $myRoleTasks->count() > 0 ? ' hidden' : '' }}">
        <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span class="iconify text-emerald-400 text-3xl" data-icon="mdi:check-decagram-outline"></span>
        </div>
        <p class="text-base font-bold text-slate-600">All tasks completed!</p>
        <p class="text-sm text-slate-400 mt-1">Great job — no pending tasks for your role.</p>
    </div>
@else
    <div class="bg-white rounded-2xl border border-slate-100 px-6 py-12 text-center">
        <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span class="iconify text-slate-300 text-3xl" data-icon="mdi:account-question-outline"></span>
        </div>
        <p class="text-base font-bold text-slate-500">No role assigned</p>
        <p class="text-sm text-slate-400 mt-1">Once your faculty assigns a role, your tasks will appear here.</p>
    </div>
@endif
