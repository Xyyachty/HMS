@extends('dean.layouts.app')

@section('page_title', 'Dean Dashboard')
@section('dashboard_active', 'active')

@section('content')
@php
    // Role tints shared by the Task Overview and Recent rows: gold on cream for
    // every role, the same as the faculty dashboard. The blue, violet and teal
    // this used to read all resolve to one crimson that looks red.
    $roleTints = [
        'front_desk'            => ['bg' => 'bg-amber-50', 'text' => 'text-amber-500'],
        'restaurant_management' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-500'],
        'room_management'       => ['bg' => 'bg-amber-50', 'text' => 'text-amber-500'],
        'maintenance'           => ['bg' => 'bg-amber-50', 'text' => 'text-amber-500'],
        'housekeeping'          => ['bg' => 'bg-amber-50', 'text' => 'text-amber-500'],
    ];
    $roleIcons = [
        'front_desk'            => 'mdi:desk',
        'restaurant_management' => 'mdi:silverware-fork-knife',
        'room_management'       => 'mdi:bed-outline',
        'maintenance'           => 'mdi:broom',
        'housekeeping'          => 'mdi:sparkles',
    ];
    $tint = fn($role, $key) => $roleTints[$role][$key] ?? ($key === 'text' ? 'text-slate-400' : 'bg-slate-100');
@endphp

{{-- ink-all, like the faculty dashboard: row icons take the black text
     colour instead of the gold tint, so the two dashboards' icons match. --}}
<div class="ink-all">
<!-- Stats Cards -->
<div data-tour="dash-stats" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">

    <!-- Total Students -->
    <a href="{{ route('dean.users', ['tab' => 'student']) }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300 block">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-full bg-amber-500 flex items-center justify-center shrink-0">
                <span class="iconify text-white text-xl" data-icon="mdi:account-group-outline"></span>
            </div>
            <div class="min-w-0">
                <p class="text-[0.625rem] font-bold uppercase tracking-[0.12em] text-black">Total Students</p>
                <p class="text-3xl font-extrabold text-black leading-tight">{{ number_format($totalStudents) }}</p>
                <p class="text-[0.6875rem] text-black font-medium truncate">Enrolled</p>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-slate-50 flex items-center gap-1.5">
            @if($totalStudents === 0)
                <span class="iconify text-slate-300 text-sm" data-icon="mdi:account-off-outline"></span>
                <span class="text-[0.6875rem] font-semibold text-black">No students</span>
            @elseif($studentsThisMonth > 0)
                <span class="iconify text-emerald-500 text-sm" data-icon="mdi:account-plus-outline"></span>
                <span class="text-[0.6875rem] font-semibold text-black">{{ $studentsThisMonth }} new {{ Str::plural('student', $studentsThisMonth) }} this month</span>
            @else
                <span class="iconify text-slate-300 text-sm" data-icon="mdi:account-clock-outline"></span>
                <span class="text-[0.6875rem] font-semibold text-black">No new students this month</span>
            @endif
        </div>
    </a>

    <!-- Total Faculty -->
    <a href="{{ route('dean.users') }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300 block">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-full bg-amber-500 flex items-center justify-center shrink-0">
                <span class="iconify text-white text-xl" data-icon="mdi:school-outline"></span>
            </div>
            <div class="min-w-0">
                <p class="text-[0.625rem] font-bold uppercase tracking-[0.12em] text-black">Total Faculty</p>
                <p class="text-3xl font-extrabold text-black leading-tight">{{ number_format($totalFaculty) }}</p>
                <p class="text-[0.6875rem] text-black font-medium truncate">Registered instructors</p>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-slate-50 flex items-center gap-1.5">
            @if($totalFaculty === 0)
                <span class="iconify text-slate-300 text-sm" data-icon="mdi:account-off-outline"></span>
                <span class="text-[0.6875rem] font-semibold text-black">No faculty</span>
            @elseif($pendingFaculty > 0)
                <span class="iconify text-amber-500 text-sm" data-icon="mdi:clock-alert-outline"></span>
                <span class="text-[0.6875rem] font-bold text-black">{{ $pendingFaculty }} awaiting activation</span>
            @else
                <span class="iconify text-emerald-500 text-sm" data-icon="mdi:check-circle-outline"></span>
                <span class="text-[0.6875rem] font-semibold text-black">All faculty accounts active</span>
            @endif
        </div>
    </a>

    <!-- Teams -->
    <a href="{{ route('dean.faculties') }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300 block">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-full bg-amber-500 flex items-center justify-center shrink-0">
                <span class="iconify text-white text-xl" data-icon="mdi:account-multiple-plus-outline"></span>
            </div>
            <div class="min-w-0">
                <p class="text-[0.625rem] font-bold uppercase tracking-[0.12em] text-black">Teams</p>
                <p class="text-3xl font-extrabold text-black leading-tight">{{ number_format($totalTeams) }}</p>
                <p class="text-[0.6875rem] text-black font-medium truncate">Formed student teams</p>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-slate-50 flex items-center gap-1.5">
            <span class="iconify text-slate-300 text-sm" data-icon="mdi:chart-donut"></span>
            <span class="text-[0.6875rem] font-semibold text-black truncate">
                {{ $completionRate }}% of tasks completed
                @if($teamsThisMonth > 0)
                    · +{{ $teamsThisMonth }} new
                @endif
            </span>
        </div>
    </a>
</div>

<!-- Task Overview / Newly / Recent -->
<div class="dash-panels">

    <!-- Task Overview: the tasks most recently assigned or completed -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between gap-2">
            <p class="text-sm font-bold text-black">Task Overview</p>
            <a href="{{ route('dean.activity') }}" class="text-[0.6875rem] font-bold text-black hover:underline">View All</a>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse(($recentActivity ?? collect()) as $task)
                @php
                    $facultyUser = $task->faculty?->user;
                    $facultyName = trim(implode(' ', array_filter([
                        $facultyUser?->first_name,
                        $facultyUser?->last_name,
                    ]))) ?: ($facultyUser?->name ?? 'Faculty');
                    $isDone = $task->status === 'archived';
                @endphp
                <div class="px-5 py-3 flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $tint($task->role, 'bg') }} {{ $tint($task->role, 'text') }}">
                        <span class="iconify text-lg" data-icon="{{ $roleIcons[$task->role] ?? 'mdi:clipboard-text-outline' }}"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[0.8125rem] font-bold text-black leading-snug line-clamp-2">{{ $task->title }}</p>
                        <p class="text-[0.6875rem] text-black mt-0.5 truncate">
                            {{ $task->group_name ?: ($roleLabels[$task->role] ?? $task->role) }} · {{ $facultyName }}
                        </p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-[0.625rem] text-black font-medium whitespace-nowrap">{{ optional($task->updated_at)->diffForHumans(null, true) }}</p>
                        <p class="text-[0.625rem] font-bold mt-1 flex items-center justify-end gap-1 {{ $isDone ? 'status-text-completed' : 'text-black' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $isDone ? 'status-fill-completed' : 'bg-slate-900' }}"></span>
                            {{ $isDone ? 'Completed' : 'Assigned' }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                        <span class="iconify text-2xl text-slate-300" data-icon="mdi:clipboard-text-off-outline"></span>
                    </div>
                    <p class="text-sm font-semibold text-black">No tasks given</p>
                    <p class="text-xs text-black mt-1">Tasks will appear here once faculty assign them.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Newly: the student and faculty accounts added most recently -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between gap-2">
            <p class="text-sm font-bold text-black">Newly Added</p>
            <a href="{{ route('dean.users') }}" class="text-[0.6875rem] font-bold text-black hover:underline">View All</a>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse(($newAccounts ?? collect()) as $account)
                @php
                    $name = trim(implode(' ', array_filter([
                        $account->first_name,
                        $account->last_name,
                    ]))) ?: ($account->name ?? 'User');
                    $isFaculty = $account->role === 'faculty';
                @endphp
                <div class="px-5 py-3 flex items-center gap-3">
                    @include('partials.user-avatar', [
                        'user'         => $account,
                        'name'         => $name,
                        'size'         => 'w-9 h-9',
                        'rounded'      => 'rounded-xl',
                        'extraClasses' => 'bg-amber-50 text-black text-xs font-bold',
                    ])
                    <div class="min-w-0 flex-1">
                        <p class="text-[0.8125rem] font-bold text-black truncate">{{ $name }}</p>
                        <p class="text-[0.6875rem] text-black mt-0.5 truncate">{{ $account->email ?? '—' }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-[0.625rem] text-black font-medium whitespace-nowrap">{{ optional($account->created_at)->diffForHumans(null, true) }}</p>
                        <p class="text-[0.625rem] font-bold mt-1 text-black">{{ $isFaculty ? 'Faculty' : 'Student' }}</p>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                        <span class="iconify text-2xl text-slate-300" data-icon="mdi:account-plus-outline"></span>
                    </div>
                    <p class="text-sm font-semibold text-black">No new accounts</p>
                    <p class="text-xs text-black mt-1">Students and faculty you add will show up here.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Activity: the latest entries of the shared activity log -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between gap-2">
            <p class="text-sm font-bold text-black">Recent Activity</p>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse(($recentLogs ?? collect()) as $log)
                @php
                    $logUser = $log->user;
                    $logName = $logUser
                        ? (trim(implode(' ', array_filter([$logUser->first_name, $logUser->last_name]))) ?: ($logUser->name ?? 'User'))
                        : 'Deleted account';
                    $logIcon = match (true) {
                        $log->activity === 'login'                    => 'mdi:login',
                        $log->activity === 'logout'                   => 'mdi:logout',
                        str_starts_with($log->activity, 'account_')   => 'mdi:account-outline',
                        str_starts_with($log->activity, 'team_'),
                        $log->activity === 'student_assigned',
                        $log->activity === 'role_assigned'            => 'mdi:account-group-outline',
                        str_starts_with($log->activity, 'task_')      => 'mdi:clipboard-text-outline',
                        default                                       => 'mdi:history',
                    };
                @endphp
                <div class="px-5 py-3 flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-amber-50 text-amber-500">
                        <span class="iconify text-lg" data-icon="{{ $logIcon }}"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[0.8125rem] font-bold text-black truncate">{{ $log->activity_label }}</p>
                        <p class="text-[0.6875rem] text-black mt-0.5 truncate">{{ $logName }} · {{ ucfirst($log->role) }}</p>
                    </div>
                    <p class="text-[0.625rem] text-black font-medium whitespace-nowrap shrink-0">{{ optional($log->created_at)->diffForHumans(null, true) }}</p>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                        <span class="iconify text-2xl text-slate-300" data-icon="mdi:history"></span>
                    </div>
                    <p class="text-sm font-semibold text-black">No activity</p>
                    <p class="text-xs text-black mt-1">Sign-ins, sign-outs and task work will show up here.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Student Customization Progress: every team's approved customization tasks -->
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mt-3">
    <div class="px-5 py-3.5 border-b border-slate-100">
        <div class="flex items-center justify-between gap-2">
            <p class="text-sm font-bold text-black">Student Customization Progress</p>
            <a href="{{ route('dean.faculties') }}" class="text-[0.6875rem] font-bold text-black hover:underline">View Teams</a>
        </div>
        @if(($teamProgress ?? collect())->isNotEmpty())
            <div class="flex items-center gap-3 mt-2.5">
                <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div data-team-overall-bar class="h-full rounded-full bg-amber-500" style="width: {{ $customizationPercent }}%"></div>
                </div>
                <p data-team-overall-percent class="text-[0.8125rem] font-extrabold text-black shrink-0">{{ $customizationPercent }}%</p>
            </div>
            <p data-team-summary class="text-[0.6875rem] text-black mt-1">
                Average across {{ $teamProgress->count() }} {{ Str::plural('team', $teamProgress->count()) }} ·
                {{ $completedTeams }} completed · {{ $teamProgress->count() - $completedTeams }} in progress
            </p>
        @endif
    </div>
    <div class="divide-y divide-slate-50 overflow-y-auto" style="max-height: 21.25rem">
        @forelse(($teamProgress ?? collect()) as $team)
            <div data-team-key="{{ $team['key'] }}" class="px-5 py-3 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-amber-50 text-amber-500">
                    <span class="iconify text-lg" data-icon="mdi:account-group-outline"></span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 min-w-0">
                        <p class="text-[0.8125rem] font-bold text-black truncate">{{ $team['name'] }}</p>
                        <span data-team-pill class="shrink-0">@include('partials.team-completion', ['done' => $team['done'], 'total' => $team['total']])</span>
                    </div>
                    <p class="text-[0.625rem] text-black font-bold tracking-wider mt-0.5 truncate">
                        {{ collect([$team['block'], $team['faculty']])->filter()->implode(' · ') }}
                        · <span data-team-count>{{ $team['done'] }}/{{ $team['total'] }} approved</span>
                    </p>
                </div>
                <div class="w-24 h-1.5 rounded-full bg-slate-100 overflow-hidden shrink-0">
                    <div data-team-bar class="h-full rounded-full bg-amber-500" style="width: {{ $team['percent'] }}%"></div>
                </div>
                <p data-team-percent class="text-[0.8125rem] font-extrabold text-black w-10 text-right shrink-0">{{ $team['percent'] }}%</p>
            </div>
        @empty
            <div class="px-5 py-12 text-center">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                    <span class="iconify text-2xl text-slate-300" data-icon="mdi:chart-timeline-variant"></span>
                </div>
                <p class="text-sm font-semibold text-black">No teams</p>
                <p class="text-xs text-black mt-1">Progress appears once faculty create teams.</p>
            </div>
        @endforelse
    </div>
</div>
</div>
@push('scripts')
    @include('partials.team-completion-live')
@endpush
@endsection

@push('styles')
@include('partials.ink-all-styles')
@endpush
