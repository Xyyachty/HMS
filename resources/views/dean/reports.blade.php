@extends('dean.layouts.app')

@section('page_title', 'Reports')
@section('page_subtitle', 'View and analyze the performance of students and teams in the hotel simulation.')
@section('reports_active', 'active')

@section('content')
<div id="deanReports" class="ink-all">

{{-- The page's name and what it is for are the header bar's job (page_title and
     page_subtitle above); printing them again here only pushed the reports down. --}}
<div class="flex justify-end mb-4">
    <nav class="flex items-center gap-1.5 text-[12px] font-semibold text-slate-400" aria-label="Breadcrumb">
        <a href="{{ route('dean.dashboard') }}" class="hover:text-brand transition">Home</a>
        <span class="iconify text-sm" data-icon="mdi:chevron-right"></span>
        <span class="text-brand">Reports</span>
    </nav>
</div>

{{-- The same report faculty see, over every faculty's teams. --}}
@include('partials.reports-body', [
    'showFaculty' => true,
    // No maroon on the dean's Reports: bars and role colours are grays.
    'barColor'    => '#4A4643',
    'roleColors'  => [
        'front_desk'            => '#2F2C2A',
        'restaurant_management' => '#8A817A',
        'room_management'       => '#5F5A55',
        'maintenance'           => '#B5AFAA',
        'housekeeping'          => '#D4D0CD',
    ],
])
</div>

@push('styles')
<style>
    /* Black text across the dean's Reports. Beats every text-* colour utility and
       the report's own tab and filter colours; white text on filled buttons and
       the active tab, and icon colours, are left alone. */
    .ink-all,
    .ink-all [class*="text-"]:not(.text-white):not(.iconify),
    .ink-all [class*="text-"]:not(.text-white):not(.iconify):hover,
    .ink-all .rp-tab:not(.active),
    .ink-all .rp-filter.rp-filter,
    .ink-all .rp-filter-clear.rp-filter-clear,
    .glass-header h2,
    .glass-header h2 + p { color: #111; }

    /* No maroon or soft fills: stat tiles, table heads, avatars, notices, the
       Team Details pop-up and buttons are white and keep their borders; the
       wine-tinted borders, track and row hover go neutral gray. An id outranks
       the .gray-accents fills. The active tab and a set filter keep their gray
       so they still read as selected. */
    #deanReports :is(.bg-slate-50, .bg-slate-50\/50, .bg-slate-50\/60, .bg-slate-50\/70, .bg-slate-50\/80, .bg-slate-100, .bg-brand-soft, .bg-amber-50, .bg-emerald-50, .bg-blue-50, .bg-violet-50) { background-color: #fff; }
    #deanReports .bg-brand-soft { box-shadow: inset 0 0 0 1px #E4E2E0; }
    #deanReports :is(.hover\:bg-slate-50, .hover\:bg-slate-50\/80, .hover\:bg-brand\/10):hover { background-color: #F3F2F1; }
    #deanReports :is(.rp-tab, .rp-filter, .rp-filter-clear) { border-color: #E4E2E0; }
    #deanReports :is(.rp-tab, .rp-filter, .rp-filter-clear):is(:hover, :focus, .is-set) { border-color: #8A817A; }
    #deanReports .rp-tab.active { border-color: transparent; }
    #deanReports .rp-track { background: #E4E2E0; }
    #deanReports :is(.border-brand\/10, .border-slate-100, .border-slate-200) { border-color: #E4E2E0; }
    #deanReports .hover\:border-brand\/40:hover { border-color: #8A817A; }
</style>
@endpush

@endsection
