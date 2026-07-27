@extends('faculty.layout.app')

@section('page_title', 'Faculty Dashboard')
@section('dashboard_active', 'active')

@section('content')

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">

    <a href="{{ route('faculty.students') }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300 block">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 bg-rose-50 rounded-xl flex items-center justify-center">
                <span class="iconify text-rose-500 text-xl" data-icon="mdi:account-group-outline"></span>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                Total
            </span>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ number_format($totalStudents) }}</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Total Students</p>
    </a>

    <a href="{{ route('faculty.role') }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300 block">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 bg-rose-50 rounded-xl flex items-center justify-center">
                <span class="iconify text-rose-500 text-xl" data-icon="mdi:account-multiple-outline"></span>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                Active
            </span>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ number_format($totalTeams) }}</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Teams</p>
    </a>

    <a href="{{ route('faculty.activity') }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300 block">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 bg-rose-50 rounded-xl flex items-center justify-center">
                <span class="iconify text-rose-500 text-xl" data-icon="mdi:clipboard-list-outline"></span>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">
                Assigned
            </span>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ number_format($assignedTasks) }}</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Assigned Tasks</p>
    </a>

</div>

<!-- Recent Activities -->
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60 flex items-center gap-2">
        <span class="iconify text-rose-500 text-lg" data-icon="mdi:history"></span>
        <p class="text-sm font-bold text-slate-800">Recent Activities</p>
    </div>
    <div class="divide-y divide-slate-50">
        @forelse(($recentActivity ?? collect()) as $task)
            @php
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
                <p class="text-xs text-slate-300 mt-1">Assigned and completed tasks will appear here.</p>
            </div>
        @endforelse
    </div>
</div>

@endsection
