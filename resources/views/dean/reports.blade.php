@extends('dean.layouts.app')

@section('page_title', 'Reports')
@section('reports_active', 'active')

@section('content')
@php
    $roleLabels = [
        'front_desk' => 'Front Desk',
        'restaurant_management' => 'Restaurant',
        'room_management' => 'Room Mgmt',
        'maintenance' => 'Maintenance',
        'housekeeping' => 'Housekeeping',
    ];
@endphp

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-800">Completed Assignments</h3>
        <span class="text-[11px] font-semibold text-slate-400">{{ $completedTasks->total() }} total</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm" style="table-layout:fixed">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:20%">Faculty</th>
                    <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:28%">Task</th>
                    <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:14%">Role</th>
                    <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:12%">Priority</th>
                    <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:14%">Completed By</th>
                    <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:12%">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($completedTasks as $task)
                    @php
                        $fUser = $task->faculty?->user;
                        $facultyName = trim(implode(' ', array_filter([$fUser?->first_name, $fUser?->last_name]))) ?: ($fUser?->name ?? 'Faculty');
                        $doneBy = $task->assignedTo?->name ?? $task->student?->user?->name ?? '—';
                        $p = strtolower($task->priority ?? 'medium');
                    @endphp
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-4 py-3 font-semibold text-slate-800 truncate" title="{{ $facultyName }}">{{ $facultyName }}</td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-slate-800 truncate" title="{{ $task->title }}">{{ $task->title }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs font-semibold text-slate-600 whitespace-nowrap">{{ $roleLabels[$task->role] ?? $task->role }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold
                                {{ $p === 'high' ? 'bg-red-50 text-red-700 border border-red-100' : ($p === 'low' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-amber-50 text-amber-700 border border-amber-100') }}">
                                {{ ucfirst($p) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500 truncate" title="{{ $doneBy }}">{{ $doneBy }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ optional($task->updated_at)->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-sm text-slate-400 font-semibold">No completed assignments to report.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($completedTasks->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $completedTasks->links() }}
        </div>
    @endif
</div>
@endsection
