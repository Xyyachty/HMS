@extends('dean.layouts.app')

@section('page_title', 'Dean Dashboard')
@section('dashboard_active', 'active')

@section('content')
@php
    // Role tints shared by the activity and deadline rows.
    $roleTints = [
        'front_desk'            => ['bg' => 'bg-blue-50',   'text' => 'text-blue-500'],
        'restaurant_management' => ['bg' => 'bg-amber-50',  'text' => 'text-amber-500'],
        'room_management'       => ['bg' => 'bg-pink-50',   'text' => 'text-brand'],
        'maintenance'           => ['bg' => 'bg-violet-50', 'text' => 'text-violet-500'],
        'housekeeping'          => ['bg' => 'bg-teal-50',   'text' => 'text-teal-500'],
    ];
    $roleIcons = [
        'front_desk'            => 'mdi:desk',
        'restaurant_management' => 'mdi:silverware-fork-knife',
        'room_management'       => 'mdi:bed-outline',
        'maintenance'           => 'mdi:broom',
        'housekeeping'          => 'mdi:sparkles',
    ];
    $tint = fn($role, $key) => $roleTints[$role][$key] ?? ($key === 'text' ? 'text-slate-400' : 'bg-slate-100');
    // Cycled across team rows so neighbouring teams stay distinguishable.
    $teamTints = ['bg-rose-50 text-rose-500', 'bg-amber-50 text-amber-500', 'bg-violet-50 text-violet-500', 'bg-teal-50 text-teal-500', 'bg-blue-50 text-blue-500'];
    $teamBars  = ['bg-rose-500', 'bg-amber-500', 'bg-violet-500', 'bg-teal-500', 'bg-blue-500'];
@endphp

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">

    <!-- Total Students -->
    <a href="{{ route('dean.users') }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300 block">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-full bg-rose-500 flex items-center justify-center shrink-0">
                <span class="iconify text-white text-xl" data-icon="mdi:account-group-outline"></span>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400">Total Students</p>
                <p class="text-3xl font-extrabold text-slate-900 leading-tight">{{ number_format($totalStudents) }}</p>
                <p class="text-[11px] text-slate-400 font-medium truncate">Enrolled learners</p>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-slate-50 flex items-center gap-1.5">
            @if($studentTrend !== 0)
                <span class="iconify text-sm {{ $studentTrend > 0 ? 'text-emerald-500' : 'text-rose-500' }}"
                      data-icon="{{ $studentTrend > 0 ? 'mdi:trending-up' : 'mdi:trending-down' }}"></span>
                <span class="text-[11px] font-bold {{ $studentTrend > 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $studentTrend > 0 ? '+' : '' }}{{ $studentTrend }}%</span>
                <span class="text-[11px] text-slate-400 font-medium">vs last month</span>
            @else
                <span class="iconify text-slate-300 text-sm" data-icon="mdi:trending-neutral"></span>
                <span class="text-[11px] font-semibold text-slate-500">No change vs last month</span>
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
                <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400">Total Faculty</p>
                <p class="text-3xl font-extrabold text-slate-900 leading-tight">{{ number_format($totalFaculty) }}</p>
                <p class="text-[11px] text-slate-400 font-medium truncate">Registered instructors</p>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-slate-50 flex items-center gap-1.5">
            @if($pendingFaculty > 0)
                <span class="iconify text-amber-500 text-sm" data-icon="mdi:clock-alert-outline"></span>
                <span class="text-[11px] font-bold text-amber-600">{{ $pendingFaculty }} awaiting approval</span>
            @else
                <span class="iconify text-emerald-500 text-sm" data-icon="mdi:check-circle-outline"></span>
                <span class="text-[11px] font-semibold text-emerald-600">All accounts approved</span>
            @endif
        </div>
    </a>

    <!-- Teams -->
    <a href="{{ route('dean.faculties') }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300 block">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-full bg-emerald-500 flex items-center justify-center shrink-0">
                <span class="iconify text-white text-xl" data-icon="mdi:account-multiple-plus-outline"></span>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400">Teams</p>
                <p class="text-3xl font-extrabold text-slate-900 leading-tight">{{ number_format($totalTeams) }}</p>
                <p class="text-[11px] text-slate-400 font-medium truncate">Active simulation teams</p>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-slate-50 flex items-center gap-1.5">
            <span class="iconify text-slate-300 text-sm" data-icon="mdi:chart-donut"></span>
            <span class="text-[11px] font-semibold text-slate-500 truncate">
                {{ $completionRate }}% of tasks completed
                @if($teamsThisMonth > 0)
                    · +{{ $teamsThisMonth }} new
                @endif
            </span>
        </div>
    </a>
</div>

<!-- Activity / progress / deadlines -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-4">

    <!-- Recent Activity -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between gap-2">
            <p class="text-sm font-bold text-slate-800">Recent Activity</p>
            <a href="{{ route('dean.activity') }}" class="text-[11px] font-bold text-brand hover:underline">View All</a>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse(($recentActivity ?? collect())->take(5) as $task)
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
                        <p class="text-[13px] font-bold text-slate-800 leading-snug line-clamp-2">{{ $task->title }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5 truncate">
                            {{ $task->group_name ?: ($roleLabels[$task->role] ?? $task->role) }} · {{ $facultyName }}
                        </p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-[10px] text-slate-400 font-medium whitespace-nowrap">{{ optional($task->updated_at)->diffForHumans(null, true) }}</p>
                        <p class="text-[10px] font-bold mt-1 flex items-center justify-end gap-1 {{ $isDone ? 'text-emerald-600' : 'text-blue-500' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $isDone ? 'bg-emerald-500' : 'bg-blue-500' }}"></span>
                            {{ $isDone ? 'Completed' : 'Assigned' }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                        <span class="iconify text-2xl text-slate-300" data-icon="mdi:history"></span>
                    </div>
                    <p class="text-sm font-semibold text-slate-400">No recent activity</p>
                    <p class="text-xs text-slate-300 mt-1">Task assignments and completions will appear here.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Task Progress Overview — every team in the system, so a long roster
         scrolls inside the panel rather than stretching the row of three. --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between gap-2">
            <p class="text-sm font-bold text-slate-800">Task Progress Overview</p>
            <a href="{{ route('dean.reports') }}" class="text-[11px] font-bold text-brand hover:underline">View Report</a>
        </div>
        <div class="divide-y divide-slate-50 max-h-[340px] overflow-y-auto">
            @forelse(($teamProgress ?? collect()) as $i => $team)
                <div class="px-5 py-3 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $teamTints[$i % count($teamTints)] }}">
                        <span class="iconify text-lg" data-icon="mdi:account-group-outline"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] font-bold text-slate-800 truncate">{{ $team['name'] }}</p>
                        <p class="text-[10px] text-slate-400 font-medium mt-0.5 truncate">{{ $team['faculty'] }} · {{ $team['done'] }}/{{ $team['total'] }}</p>
                    </div>
                    <div class="w-16 h-1.5 rounded-full bg-slate-100 overflow-hidden shrink-0">
                        <div class="h-full rounded-full {{ $teamBars[$i % count($teamBars)] }}" style="width: {{ $team['percent'] }}%"></div>
                    </div>
                    <p class="text-[13px] font-extrabold text-slate-700 w-10 text-right shrink-0">{{ $team['percent'] }}%</p>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                        <span class="iconify text-2xl text-slate-300" data-icon="mdi:chart-timeline-variant"></span>
                    </div>
                    <p class="text-sm font-semibold text-slate-400">No teams yet</p>
                    <p class="text-xs text-slate-300 mt-1">Progress appears once faculty create teams.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Upcoming Deadlines -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between gap-2">
            <p class="text-sm font-bold text-slate-800">Upcoming Deadlines</p>
            <a href="{{ route('dean.activity') }}" class="text-[11px] font-bold text-brand hover:underline">View All</a>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse(($upcomingDeadlines ?? collect()) as $task)
                @php $isLate = $task->due_date && $task->due_date->isPast(); @endphp
                <div class="px-5 py-3 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $tint($task->role, 'bg') }} {{ $tint($task->role, 'text') }}">
                        <span class="iconify text-lg" data-icon="{{ $roleIcons[$task->role] ?? 'mdi:clipboard-text-outline' }}"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] font-bold text-slate-800 truncate">{{ $task->title }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5 truncate">
                            {{ $task->group_name ?: ($roleLabels[$task->role] ?? $task->role) }}
                            @if($task->faculty_name)
                                · {{ $task->faculty_name }}
                            @endif
                        </p>
                    </div>
                    <div class="shrink-0 w-11 rounded-lg border py-1 text-center {{ $isLate ? 'border-red-100 bg-red-50' : 'border-pink-100 bg-brand-soft' }}">
                        <p class="text-[9px] font-bold uppercase tracking-wide {{ $isLate ? 'text-red-400' : 'text-brand-light' }}">{{ $task->due_date->format('M') }}</p>
                        <p class="text-[13px] font-extrabold leading-tight {{ $isLate ? 'text-red-500' : 'text-brand' }}">{{ $task->due_date->format('j') }}</p>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                        <span class="iconify text-2xl text-slate-300" data-icon="mdi:calendar-blank-outline"></span>
                    </div>
                    <p class="text-sm font-semibold text-slate-400">No upcoming deadlines</p>
                    <p class="text-xs text-slate-300 mt-1">Tasks with a due date will show up here.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Recent Students -->
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between gap-2">
        <p class="text-sm font-bold text-slate-800">Recent Students</p>
        <a href="{{ route('dean.users') }}" class="text-[11px] font-bold text-brand hover:underline">View All</a>
    </div>
    <div class="divide-y divide-slate-50 max-h-[420px] overflow-y-auto">
        @forelse(($recentStudents ?? collect()) as $student)
            @php
                $user = $student->user;
                $name = trim(implode(' ', array_filter([
                    $user?->last_name,
                    $user?->first_name,
                    $user?->middle_name,
                ]))) ?: ($user?->name ?? 'Student');
            @endphp
            <div class="px-5 py-3.5 flex items-center gap-3 hover:bg-slate-50/70 transition">
                @include('partials.user-avatar', [
                    'user'         => $user,
                    'name'         => $name,
                    'size'         => 'w-9 h-9',
                    'rounded'      => 'rounded-xl',
                    'extraClasses' => 'bg-rose-50 text-rose-500 text-xs font-bold',
                ])
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-slate-800 truncate">{{ $name }}</p>
                    <p class="text-xs text-slate-400 truncate">
                        {{ $user?->email ?? '—' }}
                        · Joined {{ optional($student->created_at)->diffForHumans() }}
                    </p>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full {{ ($user?->status ?? 'active') === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                    {{ $user?->status ?? 'active' }}
                </span>
            </div>
        @empty
            <div class="px-5 py-12 text-center">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                    <span class="iconify text-2xl text-slate-300" data-icon="mdi:account-group-outline"></span>
                </div>
                <p class="text-sm font-semibold text-slate-400">No students yet</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
