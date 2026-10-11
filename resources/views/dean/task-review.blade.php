@extends('dean.layouts.app')

@section('page_title', 'Task Review')
@section('page_subtitle', 'A submitted task, read-only. Its faculty approves it or sends it back.')

@push('styles')
<style>
    /* Laid out here rather than with utilities: public/css/app.css is a frozen
       build missing most responsive and spacing classes. */
    .dtr-grid { display: grid; gap: .75rem; grid-template-columns: minmax(0, 1fr) 320px; align-items: start; }
    @media (max-width: 1023px) { .dtr-grid { grid-template-columns: minmax(0, 1fr); } }
    .dtr-card { background: #fff; border: 1px solid #E4E2E0; border-radius: 1rem; padding: 1.25rem; }
    .dtr-card + .dtr-card { margin-top: .75rem; }
    .dtr-label { font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #8A817A; }
    .dtr-value { font-size: 14px; font-weight: 600; color: #181818; margin-top: .125rem; }
    .dtr-facts { display: grid; gap: .875rem; grid-template-columns: repeat(2, minmax(0, 1fr)); margin-top: 1rem; }
    .dtr-pill { display: inline-flex; align-items: center; gap: .375rem; padding: .25rem .625rem; border-radius: 999px; font-size: 11px; font-weight: 700; border: 1px solid #E4E2E0; color: #4A4643; background: #F3F2F1; }
    .dtr-list { list-style: none; margin: .75rem 0 0; padding: 0; }
    .dtr-list li { display: flex; gap: .5rem; align-items: flex-start; padding: .5rem 0; border-top: 1px solid #F3F2F1; font-size: 13px; color: #4A4643; }
    .dtr-list li:first-child { border-top: 0; }
    .dtr-list .iconify { flex-shrink: 0; margin-top: 2px; }
    .dtr-toggle { display: inline-flex; border: 1px solid #E4E2E0; border-radius: .75rem; overflow: hidden; }
    .dtr-toggle button { padding: .375rem .875rem; font-size: 12px; font-weight: 700; color: #4A4643; background: #fff; }
    .dtr-toggle button.is-on { background: #4A4643; color: #fff; }
    .dtr-frame { width: 100%; height: 70vh; border: 1px solid #E4E2E0; border-radius: .75rem; margin-top: .75rem; background: #fff; }
    .dtr-empty { font-size: 13px; color: #8A817A; margin-top: .75rem; }
</style>
@endpush

@section('content')
@php
    $statusLabel = match (true) {
        $review['awaiting_review'] => 'Awaiting faculty review',
        $review['needs_revision'] => 'Sent back for changes',
        $review['status'] === 'archived' && $review['feedback_at'] => 'Approved',
        default => 'In progress',
    };
@endphp

<a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dean.dashboard') }}"
   class="inline-flex items-center gap-1 text-xs font-bold text-slate-500 hover:text-brand mb-3">
    <span class="iconify" data-icon="mdi:arrow-left"></span> Back
</a>

<div class="dtr-grid">
    <div>
        <div class="dtr-card">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="dtr-label">{{ $review['role_label'] }}</p>
                    <h3 class="text-lg font-bold text-slate-900">{{ $review['title'] }}</h3>
                </div>
                <span class="dtr-pill">{{ $statusLabel }}</span>
            </div>
            @if($review['description'])
                <p class="text-sm text-slate-500 mt-2">{{ $review['description'] }}</p>
            @endif

            <div class="dtr-facts">
                <div>
                    <p class="dtr-label">Student</p>
                    <p class="dtr-value">{{ $review['student_name'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="dtr-label">Team</p>
                    <p class="dtr-value">{{ $review['group_name'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="dtr-label">Submitted</p>
                    {{-- Stored in UTC; read in the campus's own time. --}}
                    <p class="dtr-value">{{ $review['submitted_at'] ? \Illuminate\Support\Carbon::parse($review['submitted_at'], 'UTC')->timezone('Asia/Manila')->format('M d, Y g:i A') : 'Not submitted' }}</p>
                </div>
                <div>
                    <p class="dtr-label">Revisions</p>
                    <p class="dtr-value">{{ $review['revision_count'] }}</p>
                </div>
            </div>
        </div>

        @if($review['preview_url'])
            <div class="dtr-card">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-bold text-slate-800">The team's site</p>
                    @if($review['before_preview_url'])
                        <div class="dtr-toggle" role="group" aria-label="Which version">
                            <button type="button" class="is-on" data-frame-src="{{ $review['preview_url'] }}">After</button>
                            <button type="button" data-frame-src="{{ $review['before_preview_url'] }}">Before</button>
                        </div>
                    @endif
                </div>
                <iframe id="dtrFrame" class="dtr-frame" src="{{ $review['preview_url'] }}" title="Team site preview"></iframe>
            </div>
        @endif
    </div>

    <div>
        <div class="dtr-card">
            <p class="text-sm font-bold text-slate-800">Steps</p>
            <p class="text-xs text-slate-400">{{ $review['activities_done'] }} of {{ $review['activities_total'] }} done</p>
            @if($review['activities'])
                <ul class="dtr-list">
                    @foreach($review['activities'] as $activity)
                        <li>
                            <span class="iconify" data-icon="{{ $activity['done'] ? 'mdi:check-circle' : 'mdi:checkbox-blank-circle-outline' }}"></span>
                            <span>{{ $activity['text'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="dtr-empty">This task has no steps.</p>
            @endif
        </div>

        <div class="dtr-card">
            <p class="text-sm font-bold text-slate-800">What changed</p>
            <p class="text-xs text-slate-400">
                {{ $review['change_summary']['added'] }} added ·
                {{ $review['change_summary']['modified'] }} changed ·
                {{ $review['change_summary']['removed'] }} removed
            </p>
            @if($review['changes'])
                <ul class="dtr-list">
                    @foreach($review['changes'] as $change)
                        <li>
                            <span class="iconify" data-icon="{{ ['added' => 'mdi:plus-circle-outline', 'removed' => 'mdi:minus-circle-outline'][$change['type']] ?? 'mdi:pencil-circle-outline' }}"></span>
                            <span>{{ $change['label'] ?? 'Change' }}@if(!empty($change['page'])) <span class="text-slate-400">· {{ ucfirst($change['page']) }}</span>@endif</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="dtr-empty">No recorded changes to compare.</p>
            @endif
        </div>

        @if($review['feedback'])
            <div class="dtr-card">
                <p class="text-sm font-bold text-slate-800">Faculty feedback</p>
                <p class="text-sm text-slate-600 mt-2">{{ $review['feedback'] }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ $review['feedback_by'] }}</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-frame-src]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('dtrFrame').src = btn.dataset.frameSrc;
            document.querySelectorAll('[data-frame-src]').forEach(function (b) { b.classList.toggle('is-on', b === btn); });
        });
    });
</script>
@endpush
