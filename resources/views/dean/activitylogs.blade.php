@extends('dean.layouts.app')

@section('page_title', 'Activity Logs')
@section('activity_active', 'active')

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

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div>
            <p class="text-sm text-slate-500">Faculty completed tasks across the system.</p>
        </div>
        <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                {{ $logs->total() }} completed
            </span>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="table-layout:fixed">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:24%">Faculty</th>
                        <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:32%">Task</th>
                        <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:16%">Role</th>
                        <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:14%">Status</th>
                        <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:14%">Completed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $task)
                        @php
                            $facultyUser = $task->faculty?->user;
                            $facultyName = trim(implode(' ', array_filter([
                                $facultyUser?->first_name,
                                $facultyUser?->last_name,
                            ]))) ?: ($facultyUser?->name ?? 'Faculty');
                        @endphp
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-800 truncate" title="{{ $facultyName }}">{{ $facultyName }}</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ $facultyUser?->email }}</p>
                            </td>
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
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 text-[11px] font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Completed
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">
                                {{ optional($task->updated_at)->format('M d, Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-14 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                    <span class="iconify text-2xl text-slate-300" data-icon="mdi:history"></span>
                                </div>
                                <p class="text-sm font-semibold text-slate-400">No completed faculty tasks yet</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
