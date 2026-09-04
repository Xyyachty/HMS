@extends('faculty.layout.app')

@section('page_title', 'Faculty Dashboard')
@section('dashboard_active', 'active')

@section('content')

@php
    // Role tints shared by every row icon below. Only classes present in the
    // compiled public/css/app.css build are used here.
    $roleTints = [
        'front_desk'            => ['bg' => 'bg-blue-50',   'text' => 'text-blue-500',   'bar' => 'bg-blue-500'],
        'restaurant_management' => ['bg' => 'bg-amber-50',  'text' => 'text-amber-500',  'bar' => 'bg-amber-500'],
        'room_management'       => ['bg' => 'bg-pink-50',   'text' => 'text-rose-500',   'bar' => 'bg-rose-500'],
        'maintenance'           => ['bg' => 'bg-violet-50', 'text' => 'text-violet-500', 'bar' => 'bg-brand'],
        'housekeeping'          => ['bg' => 'bg-teal-50',   'text' => 'text-teal-500',   'bar' => 'bg-emerald-500'],
    ];
    $roleIcons = [
        'front_desk'            => 'mdi:desk',
        'restaurant_management' => 'mdi:silverware-fork-knife',
        'room_management'       => 'mdi:bed-outline',
        'maintenance'           => 'mdi:broom',
        'housekeeping'          => 'mdi:sparkles',
    ];
    $tint = fn($role, $key) => $roleTints[$role][$key] ?? ($key === 'bar' ? 'bg-rose-500' : ($key === 'text' ? 'text-slate-400' : 'bg-slate-100'));
    // Cycled across the team rows so neighbouring teams stay distinguishable.
    $teamTints = ['bg-rose-50 text-rose-500', 'bg-amber-50 text-amber-500', 'bg-violet-50 text-violet-500', 'bg-teal-50 text-teal-500', 'bg-blue-50 text-blue-500'];
    $teamBars  = ['bg-rose-500', 'bg-amber-500', 'bg-brand', 'bg-emerald-500', 'bg-blue-500'];
@endphp

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">

    <a href="{{ route('faculty.students') }}" class="stat-card bg-white rounded-2xl p-5 border border-slate-100 shadow-sm block">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-rose-500 flex items-center justify-center shrink-0">
                <span class="iconify text-white text-xl" data-icon="mdi:account-group-outline"></span>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Students</p>
                <p class="text-3xl font-extrabold text-slate-900 leading-tight">{{ number_format($totalStudents) }}</p>
                <p class="text-[11px] text-slate-400 font-medium truncate">Enrolled learners</p>
            </div>
        </div>
        <div class="mt-3 pt-2 border-t border-slate-100 flex items-center gap-1.5">
            <span class="iconify text-slate-300 text-sm" data-icon="mdi:account-multiple-outline"></span>
            <span class="text-[11px] font-semibold text-slate-500 truncate">Across {{ number_format($totalTeams) }} team{{ $totalTeams === 1 ? '' : 's' }}</span>
        </div>
    </a>

    <a href="{{ route('faculty.role') }}" class="stat-card bg-white rounded-2xl p-5 border border-slate-100 shadow-sm block">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-amber-500 flex items-center justify-center shrink-0">
                <span class="iconify text-white text-xl" data-icon="mdi:clipboard-text-outline"></span>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Active Tasks</p>
                <p class="text-3xl font-extrabold text-slate-900 leading-tight">{{ number_format($assignedTasks) }}</p>
                <p class="text-[11px] text-slate-400 font-medium truncate">Currently in progress</p>
            </div>
        </div>
        <div class="mt-3 pt-2 border-t border-slate-100 flex items-center gap-1.5">
            @if($overdueTasks > 0)
                <span class="iconify text-red-400 text-sm" data-icon="mdi:alert-circle-outline"></span>
                <span class="text-[11px] font-semibold text-red-500 truncate">{{ number_format($overdueTasks) }} past due date</span>
            @else
                <span class="iconify text-emerald-500 text-sm" data-icon="mdi:check-circle-outline"></span>
                <span class="text-[11px] font-semibold text-emerald-600 truncate">Nothing overdue</span>
            @endif
        </div>
    </a>

    <a href="{{ route('faculty.activity') }}" class="stat-card bg-white rounded-2xl p-5 border border-slate-100 shadow-sm block">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-emerald-500 flex items-center justify-center shrink-0">
                <span class="iconify text-white text-xl" data-icon="mdi:trending-up"></span>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Completion Rate</p>
                <p class="text-3xl font-extrabold text-slate-900 leading-tight">{{ $completionRate }}%</p>
                <p class="text-[11px] text-slate-400 font-medium truncate">Average completion</p>
            </div>
        </div>
        <div class="mt-3 pt-2 border-t border-slate-100 flex items-center gap-1.5">
            <span class="iconify text-slate-300 text-sm" data-icon="mdi:check-all"></span>
            <span class="text-[11px] font-semibold text-slate-500 truncate">{{ number_format($completedTasks) }} task{{ $completedTasks === 1 ? '' : 's' }} completed</span>
        </div>
    </a>

</div>

<!-- Activity / progress / deadlines -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-3">

    <!-- Recent Activity -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between gap-2">
            <p class="text-sm font-bold text-slate-800">Recent Activity</p>
            <a href="{{ route('faculty.activity') }}" class="text-[11px] font-bold text-rose-600 hover:underline">View All</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse(($recentActivity ?? collect())->take(5) as $task)
                @php
                    $actor = $task->assignedTo ?? $task->student?->user;
                    $actorName = $actor
                        ? (trim(implode(' ', array_filter([$actor->first_name, $actor->last_name]))) ?: ($actor->name ?? 'Student'))
                        : null;
                    $isDone = $task->status === 'archived';
                @endphp
                <div class="px-5 py-3 flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $tint($task->role, 'bg') }} {{ $tint($task->role, 'text') }}">
                        <span class="iconify text-lg" data-icon="{{ $roleIcons[$task->role] ?? 'mdi:clipboard-text-outline' }}"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] font-bold text-slate-800 line-clamp-2">{{ $task->title }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5 truncate">
                            {{ $roleLabels[$task->role] ?? $task->role }}
                            @if($task->group_name)
                                · {{ $task->group_name }}
                            @elseif($actorName)
                                · {{ $actorName }}
                            @endif
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
                    <p class="text-xs text-slate-300 mt-1">Assigned and completed tasks will appear here.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Task Progress Overview — one row per team, so a long roster scrolls inside
         the panel rather than stretching the row of three. --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between gap-2">
            <p class="text-sm font-bold text-slate-800">Task Progress Overview</p>
            <a href="{{ route('faculty.role') }}" class="text-[11px] font-bold text-rose-600 hover:underline">View Report</a>
        </div>
        <div class="divide-y divide-slate-100 overflow-y-auto" style="max-height: 340px">
            @forelse(($teamProgress ?? collect()) as $i => $team)
                <div class="px-5 py-3 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $teamTints[$i % count($teamTints)] }}">
                        <span class="iconify text-lg" data-icon="mdi:account-group-outline"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] font-bold text-slate-800 truncate">{{ $team['name'] }}</p>
                        <p class="text-[10px] text-slate-400 font-bold tracking-wider mt-0.5">{{ $team['label'] }} · {{ $team['done'] }}/{{ $team['total'] }}</p>
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
                    <p class="text-xs text-slate-300 mt-1">Progress appears once you create a team.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Upcoming Deadlines -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between gap-2">
            <p class="text-sm font-bold text-slate-800">Upcoming Deadlines</p>
            <a href="{{ route('faculty.activity') }}" class="text-[11px] font-bold text-rose-600 hover:underline">View All</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse(($upcomingDeadlines ?? collect()) as $task)
                @php $isLate = $task->due_date && $task->due_date->isPast(); @endphp
                <div class="px-5 py-3 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $tint($task->role, 'bg') }} {{ $tint($task->role, 'text') }}">
                        <span class="iconify text-lg" data-icon="{{ $roleIcons[$task->role] ?? 'mdi:clipboard-text-outline' }}"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] font-bold text-slate-800 truncate">{{ $task->title }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5 truncate">{{ $task->group_name ?: ($roleLabels[$task->role] ?? $task->role) }}</p>
                    </div>
                    <div class="shrink-0 w-11 rounded-lg border py-1 text-center {{ $isLate ? 'border-red-100 bg-red-50' : 'border-pink-100 bg-pink-50' }}">
                        <p class="text-[9px] font-bold uppercase tracking-wider {{ $isLate ? 'text-red-400' : 'text-rose-400' }}">{{ $task->due_date->format('M') }}</p>
                        <p class="text-[13px] font-extrabold leading-tight {{ $isLate ? 'text-red-500' : 'text-rose-600' }}">{{ $task->due_date->format('j') }}</p>
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

@endsection
