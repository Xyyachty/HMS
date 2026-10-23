{{--
    Header Help button: replays the dashboard's guided tour (partials.guided-tour).
    On the dashboard it starts in place; elsewhere it opens the dashboard with ?tour=1.
    Styled with its own CSS because the Faculty layout runs on a frozen Tailwind
    build. The label hides on narrow screens, leaving the icon.
--}}
@php $tourDashboard = ['dean' => 'dean.dashboard', 'faculty' => 'faculty.dashboard', 'student' => 'students.dashboard'][auth()->user()?->portal()] ?? null; @endphp
@if($tourDashboard)
<style>
    .hms-tour-btn { display: inline-flex; align-items: center; gap: 0.375rem; height: 2.5rem; padding: 0 0.625rem; border-radius: 0.75rem; color: #64748b; font-size: 0.8125rem; font-weight: 600; text-decoration: none; white-space: nowrap; transition: background-color .15s ease; }
    .hms-tour-btn:hover { background: #f1f5f9; }
    .hms-tour-btn .iconify { font-size: 1.25rem; }
    @media (max-width: 767px) { .hms-tour-btn span:last-child { display: none; } }
</style>
<a href="{{ route($tourDashboard, ['tour' => 1]) }}" class="hms-tour-btn" data-tour="help"
   title="Guided Tour" aria-label="Guided Tour"
   onclick="if (window.startGuidedTour) return startGuidedTour();">
    <span class="iconify" data-icon="lucide:circle-help"></span>
    <span>Guided Tour</span>
</a>
@endif
