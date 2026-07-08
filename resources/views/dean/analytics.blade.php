@extends('dean.layouts.app')

@section('page_title', 'Analytics')
@section('analytics_active', 'active')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h3 class="text-lg font-bold text-slate-800">Analytics</h3>
            <p class="text-sm text-slate-500 font-light">System-wide performance overview.</p>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-brand-soft p-8 rounded-2xl border-2 border-dashed border-brand/20 flex flex-col items-center justify-center h-72">
            <div class="w-16 h-16 bg-brand/10 rounded-2xl flex items-center justify-center mb-4">
                <span class="iconify text-brand text-4xl" data-icon="mdi:chart-bar"></span>
            </div>
            <p class="text-brand font-bold text-sm">Enrollment Analytics</p>
            <p class="text-xs text-slate-400 mt-1">Integration pending</p>
        </div>
        <div class="bg-purple-50 p-8 rounded-2xl border-2 border-dashed border-plum-accent/20 flex flex-col items-center justify-center h-72">
            <div class="w-16 h-16 bg-plum-accent/10 rounded-2xl flex items-center justify-center mb-4">
                <span class="iconify text-plum-accent text-4xl" data-icon="mdi:chart-pie"></span>
            </div>
            <p class="text-plum-accent font-bold text-sm">Role Distribution</p>
            <p class="text-xs text-slate-400 mt-1">Integration pending</p>
        </div>
    </div>
</div>
@endsection
