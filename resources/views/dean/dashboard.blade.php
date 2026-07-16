@extends('dean.layouts.app')

@section('page_title', 'Dean Dashboard')
@section('dashboard_active', 'active')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-3 gap-3 mb-4">
    <!-- Total Students -->
    <a href="{{ route('dean.users') }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300 block">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 bg-rose-50 rounded-xl flex items-center justify-center">
                <span class="iconify text-rose-500 text-xl" data-icon="mdi:account-group-outline"></span>
            </div>
            @if($studentTrend !== 0)
                <span class="text-[10px] font-bold uppercase tracking-wider {{ $studentTrend > 0 ? 'text-emerald-600 bg-emerald-50' : 'text-rose-600 bg-rose-50' }} px-2 py-0.5 rounded-full flex items-center gap-0.5">
                    <span class="iconify text-xs" data-icon="{{ $studentTrend > 0 ? 'mdi:trending-up' : 'mdi:trending-down' }}"></span>
                    {{ $studentTrend > 0 ? '+' : '' }}{{ $studentTrend }}%
                </span>
            @else
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 bg-slate-50 px-2 py-0.5 rounded-full">This month</span>
            @endif
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ number_format($totalStudents) }}</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Total Students</p>
    </a>

    <!-- Total Faculty -->
    <a href="{{ route('dean.users') }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300 block">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 bg-rose-50 rounded-xl flex items-center justify-center">
                <span class="iconify text-rose-500 text-xl" data-icon="mdi:school-outline"></span>
            </div>
            @if($pendingFaculty > 0)
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">{{ $pendingFaculty }} Pending</span>
            @else
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">All clear</span>
            @endif
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ number_format($totalFaculty) }}</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Total Faculty</p>
    </a>

    <!-- Teams -->
    <a href="{{ route('dean.faculties') }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300 block">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 bg-rose-50 rounded-xl flex items-center justify-center">
                <span class="iconify text-rose-500 text-xl" data-icon="mdi:account-multiple-plus-outline"></span>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full flex items-center gap-0.5">
                <span class="iconify text-xs" data-icon="mdi:trending-up"></span> +{{ $teamsThisMonth }}
            </span>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ number_format($totalTeams) }}</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Teams</p>
    </a>
</div>
@endsection
