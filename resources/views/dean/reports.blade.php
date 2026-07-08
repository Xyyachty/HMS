@extends('dean.layouts.app')

@section('page_title', 'Reports')
@section('reports_active', 'active')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="p-6 border-b border-slate-100 bg-slate-50/50">
        <h3 class="text-lg font-bold text-slate-800">Reports</h3>
        <p class="text-sm text-slate-500 mt-1">Overview of system activity and performance.</p>
    </div>
    <div class="p-12 text-center">
        <div class="w-16 h-16 bg-brand-soft rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span class="iconify text-brand text-3xl" data-icon="mdi:file-chart-outline"></span>
        </div>
        <p class="text-sm font-semibold text-slate-400">Reports coming soon.</p>
        <p class="text-xs text-slate-300 mt-1">This section will display system reports and summaries.</p>
    </div>
</div>
@endsection
