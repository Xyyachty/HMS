@extends('dean.layouts.app')

@section('page_title', 'Reports')
@section('page_subtitle', 'View and analyze the performance of students and teams in the hotel simulation.')
@section('reports_active', 'active')

@section('content')

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
@include('partials.reports-body', ['showFaculty' => true])

@endsection
