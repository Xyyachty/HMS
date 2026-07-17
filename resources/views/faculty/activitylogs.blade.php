@extends('faculty.layout.app')

@section('page_title', 'List of Teams')

@section('content')
@php
    $roleLabels = [
        'front_desk' => 'Front Desk',
        'restaurant_management' => 'Restaurant',
        'room_management' => 'Room Mgmt',
        'maintenance' => 'Maintenance',
        'housekeeping' => 'Housekeeping',
    ];
    $filter = request('filter', 'completed');
@endphp

@php
    $classQuery = request()->filled('class') ? ['class' => request('class')] : [];
@endphp
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-5">
    <div class="px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <p class="text-sm text-slate-500">Track assigned and completed tasks for your teams.</p>
        <div class="flex items-center gap-2 shrink-0 ml-auto">
            <a href="{{ route('faculty.role', array_merge($classQuery, ['tab' => 'teams'])) }}"
               class="h-10 px-4 rounded-xl text-sm font-bold bg-white text-slate-600 border border-slate-200 hover:border-brand/40 hover:text-brand transition inline-flex items-center gap-2 whitespace-nowrap">
                <span class="iconify text-base" data-icon="mdi:account-group"></span>
                Teams
            </a>
            <a href="{{ route('faculty.role', array_merge($classQuery, ['tab' => 'create_task'])) }}"
               class="h-10 px-4 rounded-xl text-sm font-bold bg-white text-slate-600 border border-slate-200 hover:border-brand/40 hover:text-brand transition inline-flex items-center gap-2 whitespace-nowrap">
                <span class="iconify text-base" data-icon="mdi:clipboard-plus-outline"></span>
                Set Task
            </a>
            <a href="{{ route('faculty.role', array_merge($classQuery, ['tab' => 'teams', 'create' => 1])) }}"
               class="h-10 px-4 rounded-xl text-sm font-bold bg-brand text-white hover:opacity-95 transition shadow-md shadow-brand/20 inline-flex items-center gap-2 whitespace-nowrap">
                <span class="iconify text-base" data-icon="mdi:plus"></span>
                Add Team
            </a>
        </div>
    </div>
</div>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div></div>
        <div class="flex items-center gap-2">
            <a href="{{ route('faculty.activity', ['filter' => 'completed']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold border transition {{ $filter === 'completed' ? 'bg-brand text-white border-transparent' : 'bg-white text-slate-600 border-slate-200 hover:border-brand/30' }}">
                Completed ({{ $completedCount }})
            </a>
            <a href="{{ route('faculty.activity', ['filter' => 'active']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold border transition {{ $filter === 'active' ? 'bg-brand text-white border-transparent' : 'bg-white text-slate-600 border-slate-200 hover:border-brand/30' }}">
                Assigned ({{ $activeCount }})
            </a>
            <a href="{{ route('faculty.activity', ['filter' => 'all']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold border transition {{ $filter === 'all' ? 'bg-brand text-white border-transparent' : 'bg-white text-slate-600 border-slate-200 hover:border-brand/30' }}">
                All
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="table-layout:fixed">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:32%">Task</th>
                        <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:16%">Role</th>
                        <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:12%">Priority</th>
                        <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:14%">Status</th>
                        <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:14%">Due</th>
                        <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:12%">Updated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $task)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-800 truncate" title="{{ $task->title }}">{{ $task->title }}</p>
                                @if($task->description)
                                    <p class="text-[11px] text-slate-400 truncate">{{ $task->description }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs font-semibold text-slate-600 whitespace-nowrap">
                                {{ $roleLabels[$task->role] ?? $task->role }}
                            </td>
                            <td class="px-4 py-3">
                                @php $p = strtolower($task->priority ?? 'medium'); @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold
                                    {{ $p === 'high' ? 'bg-red-50 text-red-700 border border-red-100' : ($p === 'low' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-amber-50 text-amber-700 border border-amber-100') }}">
                                    {{ ucfirst($p) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($task->status === 'archived')
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 text-[11px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Completed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100 text-[11px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        Assigned
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">
                                {{ optional($task->due_date)->format('M d, Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">
                                {{ optional($task->updated_at)->format('M d, Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-14 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                    <span class="iconify text-2xl text-slate-300" data-icon="mdi:history"></span>
                                </div>
                                <p class="text-sm font-semibold text-slate-400">No activity logs found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $logs->appends(['filter' => $filter])->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
