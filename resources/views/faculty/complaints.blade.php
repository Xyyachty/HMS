@extends('faculty.layout.app')

@section('page_title', 'Guest Complaints')

@section('content')
@php
    $departments = \App\Models\HotelComplaint::DEPARTMENTS;
    $statuses = \App\Models\HotelComplaint::STATUSES;
    $statusTones = [
        'Pending'     => 'bg-rose-50 text-rose-700',
        'In Progress' => 'bg-amber-50 text-amber-700',
        'Resolved'    => 'bg-emerald-50 text-emerald-700',
        'Closed'      => 'bg-slate-100 text-slate-700',
        'Cancelled'   => 'bg-slate-100 text-slate-400',
    ];
    $selectClass = 'h-10 px-3 rounded-xl text-sm font-semibold bg-white text-slate-600 border border-slate-200';
@endphp

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-5">
    <div class="px-4 py-3">
        <p class="text-sm text-slate-500">
            Every guest complaint your teams' Front Desks recorded, which department it went to, and how it was handled.
            Read-only: students update these from their own Simulation pages.
        </p>
    </div>
</div>

<form method="GET" action="{{ route('faculty.complaints') }}" class="flex flex-wrap items-center gap-2 mb-5">
    <select name="team" class="{{ $selectClass }}" onchange="this.form.submit()" aria-label="Team">
        <option value="">All teams</option>
        @foreach($teams as $team)
            <option value="{{ $team }}" @selected($filters['team'] === $team)>{{ $team }}</option>
        @endforeach
    </select>
    <select name="department" class="{{ $selectClass }}" onchange="this.form.submit()" aria-label="Department">
        <option value="">All Departments</option>
        @foreach($departments as $key => $label)
            <option value="{{ $key }}" @selected($filters['department'] === $key)>{{ $label }}</option>
        @endforeach
    </select>
    <select name="kind" class="{{ $selectClass }}" onchange="this.form.submit()" aria-label="Complaint type">
        <option value="">Facility and staff service</option>
        <option value="facility" @selected($filters['kind'] === 'facility')>Facility problems only</option>
        <option value="service" @selected($filters['kind'] === 'service')>Poor staff service only</option>
    </select>
    <select name="status" class="{{ $selectClass }}" onchange="this.form.submit()" aria-label="Status">
        <option value="">All statuses</option>
        @foreach($statuses as $status)
            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
        @endforeach
    </select>
    <noscript><button type="submit" class="{{ $selectClass }}">Filter</button></noscript>
</form>

@if($complaints->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <p class="text-sm text-slate-500">No complaints match these filters.</p>
    </div>
@else
    <div class="space-y-3">
        @foreach($complaints as $complaint)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-base font-bold text-slate-800">
                            {{ $complaint->room_number !== '' ? 'Room ' . $complaint->room_number : ($complaint->guest_name ?: 'Guest') }}
                            <span class="text-sm font-semibold text-slate-400">· Team {{ $complaint->group_name }}</span>
                        </p>
                        <p class="text-sm text-slate-500 mt-1">
                            {{ $complaint->room_number !== '' ? ($complaint->guest_name ?: 'Guest name not given') . ' · ' : '' }}{{ $complaint->category }}
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <span class="px-2 py-1 rounded-full text-xs font-bold {{ $statusTones[$complaint->status] ?? 'bg-slate-100 text-slate-600' }}">{{ $complaint->status }}</span>
                        <span class="px-2 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">{{ $complaint->departmentLabel() }}</span>
                        @if($complaint->isServiceComplaint())
                            <span class="px-2 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700">Staff service</span>
                        @endif
                        @if($complaint->isInternal())
                            <span class="px-2 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">Staff report</span>
                        @endif
                    </div>
                </div>

                <p class="text-sm text-slate-700 mt-2" style="white-space: pre-line; overflow-wrap: anywhere;">{{ $complaint->details }}</p>

                <p class="text-xs text-slate-500 mt-2">
                    Reported {{ optional($complaint->created_at)->format('M j, Y g:i A') }} by {{ $complaint->filed_by ?: 'Front Desk' }}
                    @if($complaint->handled_by) · Handled by {{ $complaint->handled_by }} @endif
                    @if($complaint->resolved_at) · {{ $complaint->status === 'Cancelled' ? 'Cancelled' : 'Resolved' }} {{ $complaint->resolved_at->format('M j, Y g:i A') }} @endif
                </p>

                <p class="text-sm text-slate-700 mt-2">
                    <span class="font-semibold">Resolution notes:</span>
                    {{ $complaint->resolution_note ?: 'None yet.' }}
                </p>

                @if(!empty($complaint->history))
                    <details class="mt-2">
                        <summary class="text-xs font-bold text-slate-600" style="cursor: pointer;">History ({{ count($complaint->history) }})</summary>
                        <ol class="mt-2 space-y-1 pl-3" style="border-left: 2px solid #e2e8f0;">
                            @foreach($complaint->history as $entry)
                                <li class="text-xs text-slate-500">
                                    <span class="font-semibold text-slate-700">{{ $entry['event'] ?? '' }}</span>
                                    · {{ isset($entry['at']) ? \Illuminate\Support\Carbon::parse($entry['at'])->format('M j, Y g:i A') : '' }}
                                    @if(!empty($entry['by'])) · {{ $entry['by'] }} @endif
                                    @if(!empty($entry['note']))<br><span class="text-slate-700">{{ $entry['note'] }}</span>@endif
                                </li>
                            @endforeach
                        </ol>
                    </details>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-5">
        {{ $complaints->links() }}
    </div>
@endif
@endsection
