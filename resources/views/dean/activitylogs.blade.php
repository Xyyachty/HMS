@extends('dean.layouts.app')

@section('page_title', 'Activity Logs')
@section('page_subtitle', 'Tasks the teams have finished, grouped by the faculty who gave them.')
@section('activity_active', 'active')

@section('content')
@php
    $roleLabels = [
        'front_desk' => 'Front Desk',
        'restaurant_management' => 'Restaurant Management',
        'room_management' => 'Room Management',
        'maintenance' => 'Maintenance',
        'housekeeping' => 'Housekeeping',
    ];
@endphp

<div class="al-page space-y-4">
    {{-- One plain sentence in place of a stat strip. --}}
    <div class="al-summary">
        <span class="al-summary-icon"><span class="iconify" data-icon="mdi:check-all"></span></span>
        <p>
            <b>{{ $tasks->count() }} {{ Str::plural('task', $tasks->count()) }}</b> finished,
            given by <b>{{ $byFaculty->count() }} {{ Str::plural('faculty member', $byFaculty->count()) }}</b>.
            <span class="al-muted">Click a name to see the tasks.</span>
        </p>
    </div>

    @forelse($byFaculty as $facultyTasks)
        @php
            $facultyUser = $facultyTasks->first()->faculty?->user;
            $facultyName = trim(implode(' ', array_filter([
                $facultyUser?->first_name,
                $facultyUser?->last_name,
            ]))) ?: ($facultyUser?->name ?? 'Faculty');
            $teamCount = $facultyTasks->pluck('group_name')->filter()->unique()->count();
            $lastDone = $facultyTasks->first()->updated_at;
        @endphp

        {{-- Native <details>: opens and closes with no script. The first faculty
             starts open so the page never looks empty. --}}
        <details class="al-faculty" @if($loop->first) open @endif>
            <summary>
                <img src="{{ $facultyUser?->avatar_url ?? asset('images/default-avatar.svg') }}" alt="" class="al-avatar">
                <span class="al-who">
                    <span class="al-name">{{ $facultyName }}</span>
                    <span class="al-meta">
                        {{ $facultyTasks->count() }} {{ Str::plural('task', $facultyTasks->count()) }} finished
                        @if($teamCount) · {{ $teamCount }} {{ Str::plural('team', $teamCount) }} @endif
                        @if($lastDone) · last on {{ $lastDone->format('M j, Y') }} @endif
                    </span>
                </span>
                <span class="al-chevron iconify" data-icon="mdi:chevron-down"></span>
            </summary>

            <ul class="al-list">
                @foreach($facultyTasks as $task)
                    <li class="al-row">
                        <span class="al-check"><span class="iconify" data-icon="mdi:check"></span></span>
                        <span class="al-task">
                            <span class="al-title">{{ $task->title }}</span>
                            <span class="al-tags">
                                @if($task->group_name)<span class="al-tag">{{ $task->group_name }}</span>@endif
                                <span class="al-tag">{{ $roleLabels[$task->role] ?? Str::headline((string) $task->role) }}</span>
                            </span>
                        </span>
                        <span class="al-date">{{ optional($task->updated_at)->format('M j, Y') }}</span>
                    </li>
                @endforeach
            </ul>
        </details>
    @empty
        <div class="al-empty">
            <span class="al-empty-icon"><span class="iconify" data-icon="mdi:clipboard-check-outline"></span></span>
            <p class="al-empty-title">No finished tasks yet</p>
            <p class="al-muted">When a team finishes a task and its faculty approves it, it shows up here.</p>
        </div>
    @endforelse
</div>
@endsection

@push('styles')
<style>
    .glass-header h2,
    .glass-header h2 + p { color: #111; }

    /* Neutral grays only: the dean palette tints Tailwind's colours toward
       wine, so this page sets its own. */
    .al-page { color: #1F2937; }
    .al-muted { color: #6B7280; }

    .al-summary {
        display: flex; align-items: center; gap: .75rem;
        background: #fff; border: 1px solid #E5E7EB; border-radius: 1rem;
        padding: .9rem 1.1rem; font-size: 14px;
    }
    .al-summary b { color: #111827; }
    .al-summary-icon {
        flex-shrink: 0; width: 2.25rem; height: 2.25rem; border-radius: .75rem;
        display: flex; align-items: center; justify-content: center;
        background: #F3F4F6; color: #374151; font-size: 1.15rem;
    }

    .al-faculty {
        background: #fff; border: 1px solid #E5E7EB; border-radius: 1rem; overflow: hidden;
        box-shadow: 0 10px 30px -18px rgba(17, 24, 39, .15);
    }
    .al-faculty summary {
        list-style: none; cursor: pointer;
        display: flex; align-items: center; gap: .9rem; padding: 1rem 1.1rem;
        transition: background-color .2s cubic-bezier(.16, 1, .3, 1);
    }
    .al-faculty summary::-webkit-details-marker { display: none; }
    .al-faculty summary:hover { background: #F9FAFB; }
    .al-faculty summary:focus-visible { outline: 2px solid #374151; outline-offset: -2px; }
    .al-faculty[open] summary { border-bottom: 1px solid #F3F4F6; }

    .al-avatar {
        flex-shrink: 0; width: 2.75rem; height: 2.75rem; border-radius: 9999px; object-fit: cover;
        display: flex; align-items: center; justify-content: center;
        background: #374151; color: #fff; font-size: 14px; font-weight: 800; letter-spacing: .02em;
    }
    .al-who { min-width: 0; flex: 1 1 auto; display: flex; flex-direction: column; }
    .al-name { font-size: 15px; font-weight: 800; color: #111827; }
    .al-meta { font-size: 12.5px; color: #6B7280; margin-top: .1rem; }
    .al-chevron {
        flex-shrink: 0; font-size: 1.4rem; color: #6B7280;
        transition: transform .3s cubic-bezier(.16, 1, .3, 1);
    }
    .al-faculty[open] .al-chevron { transform: rotate(180deg); }

    .al-list { margin: 0; padding: 0; list-style: none; }
    .al-row {
        display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: .85rem;
        padding: .8rem 1.1rem; border-top: 1px solid #F3F4F6;
    }
    .al-row:first-child { border-top: 0; }
    .al-row:hover { background: #FAFAFA; }
    .al-check {
        width: 1.75rem; height: 1.75rem; border-radius: 9999px;
        display: flex; align-items: center; justify-content: center;
        background: #DCFCE7; color: #15803D; font-size: .95rem;
    }
    .al-task { min-width: 0; display: flex; flex-direction: column; gap: .3rem; }
    .al-title { font-size: 14px; font-weight: 700; color: #111827; }
    .al-tags { display: flex; flex-wrap: wrap; gap: .35rem; }
    .al-tag {
        font-size: 11.5px; font-weight: 600; color: #374151;
        background: #F3F4F6; border: 1px solid #E5E7EB; border-radius: .5rem; padding: .1rem .5rem;
    }
    .al-date { font-size: 12.5px; color: #6B7280; white-space: nowrap; }

    /* Phones: the date drops under the task instead of squeezing it. */
    @media (max-width: 640px) {
        .al-row { grid-template-columns: auto minmax(0, 1fr); }
        .al-date { grid-column: 2; }
    }

    .al-empty {
        background: #fff; border: 1px dashed #D1D5DB; border-radius: 1rem;
        padding: 3rem 1rem; text-align: center; font-size: 13px;
    }
    .al-empty-icon {
        width: 3rem; height: 3rem; border-radius: 1rem; margin: 0 auto .75rem;
        display: flex; align-items: center; justify-content: center;
        background: #F3F4F6; color: #6B7280; font-size: 1.5rem;
    }
    .al-empty-title { font-size: 15px; font-weight: 800; color: #111827; margin-bottom: .25rem; }
</style>
@endpush
