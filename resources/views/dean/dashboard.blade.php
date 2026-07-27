@extends('dean.layouts.app')

@section('page_title', 'Dean Dashboard')
@section('dashboard_active', 'active')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-3 gap-3 mb-4">
    <!-- Total Students -->
    <a href="{{ route('dean.users') }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300 block">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 bg-rose-50 rounded-xl flex items-center justify-center">
                <span class="iconify text-rose-500 text-xl" data-icon="mdi:account-group-outline"></span>
            </div>
            @if($studentTrend !== 0)
                <span class="text-[10px] font-bold uppercase tracking-wider {{ $studentTrend > 0 ? 'text-emerald-600 bg-emerald-50' : 'text-rose-600 bg-rose-50' }} px-2 py-0.5 rounded-full flex items-center gap-0.5">
                    <span class="iconify text-xs" data-icon="{{ $studentTrend > 0 ? 'mdi:trending-up' : 'mdi:trending-down' }}"></span>
                    {{ $studentTrend > 0 ? '+' : '' }}{{ $studentTrend }}%
                </span>
            @else
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 bg-slate-50 px-2 py-0.5 rounded-full">This month</span>
            @endif
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ number_format($totalStudents) }}</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Total Students</p>
    </a>

    <!-- Total Faculty -->
    <a href="{{ route('dean.users') }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300 block">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 bg-rose-50 rounded-xl flex items-center justify-center">
                <span class="iconify text-rose-500 text-xl" data-icon="mdi:school-outline"></span>
            </div>
            @if($pendingFaculty > 0)
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">{{ $pendingFaculty }} Pending</span>
            @else
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">All clear</span>
            @endif
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ number_format($totalFaculty) }}</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Total Faculty</p>
    </a>

    <!-- Teams -->
    <a href="{{ route('dean.faculties') }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300 block">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 bg-rose-50 rounded-xl flex items-center justify-center">
                <span class="iconify text-rose-500 text-xl" data-icon="mdi:account-multiple-plus-outline"></span>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full flex items-center gap-0.5">
                <span class="iconify text-xs" data-icon="mdi:trending-up"></span> +{{ $teamsThisMonth }}
            </span>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ number_format($totalTeams) }}</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Teams</p>
    </a>
</div>

<!-- Recent Activity + Recent Students -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60 flex items-center gap-2">
            <span class="iconify text-rose-500 text-lg" data-icon="mdi:history"></span>
            <p class="text-sm font-bold text-slate-800">Recent Activities</p>
        </div>
        <div class="divide-y divide-slate-50 max-h-[420px] overflow-y-auto">
            @forelse(($recentActivity ?? collect()) as $task)
                @php
                    $facultyUser = $task->faculty?->user;
                    $facultyName = trim(implode(' ', array_filter([
                        $facultyUser?->first_name,
                        $facultyUser?->last_name,
                    ]))) ?: ($facultyUser?->name ?? 'Faculty');
                    $actor = $task->assignedTo ?? $task->student?->user;
                    $actorName = $actor
                        ? (trim(implode(' ', array_filter([$actor->first_name, $actor->last_name]))) ?: ($actor->name ?? 'Student'))
                        : null;
                @endphp
                <div class="px-5 py-3.5 flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $task->status === 'archived' ? 'bg-emerald-50 text-emerald-500' : 'bg-blue-50 text-blue-500' }}">
                        <span class="iconify text-lg" data-icon="{{ $task->status === 'archived' ? 'mdi:check-circle-outline' : 'mdi:clipboard-plus-outline' }}"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-slate-800 truncate">
                            {{ $task->status === 'archived' ? 'Completed' : 'Assigned' }}: {{ $task->title }}
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5 truncate">
                            {{ $roleLabels[$task->role] ?? $task->role }}
                            · {{ $facultyName }}
                            @if($actorName)
                                · {{ $actorName }}
                            @endif
                            · {{ optional($task->updated_at)->diffForHumans() }}
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

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60 flex items-center gap-2">
            <span class="iconify text-rose-500 text-lg" data-icon="mdi:account-plus-outline"></span>
            <p class="text-sm font-bold text-slate-800">Recent Students</p>
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
                    $initials = strtoupper(substr($name, 0, 1));
                    $sp = strpos($name, ' ');
                    if ($sp !== false) {
                        $initials .= strtoupper(substr($name, $sp + 1, 1));
                    }
                @endphp
                <div class="px-5 py-3.5 flex items-center gap-3 hover:bg-slate-50/70 transition">
                    <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center shrink-0 text-xs font-bold">
                        {{ $initials }}
                    </div>
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
</div>
@endsection
