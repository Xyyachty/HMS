@extends('faculty.layout.app')

@section('page_title', 'Faculty Dashboard')
@section('dashboard_active', 'active')

@section('content')

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">

    <a href="{{ route('faculty.students') }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300 block">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 bg-rose-50 rounded-xl flex items-center justify-center">
                <span class="iconify text-rose-500 text-xl" data-icon="mdi:account-group-outline"></span>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                Total
            </span>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ number_format($totalStudents) }}</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Total Students</p>
    </a>

    <a href="{{ route('faculty.role') }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300 block">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 bg-rose-50 rounded-xl flex items-center justify-center">
                <span class="iconify text-rose-500 text-xl" data-icon="mdi:account-multiple-outline"></span>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                Active
            </span>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ number_format($totalTeams) }}</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Teams</p>
    </a>

    <a href="{{ route('faculty.activity') }}" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300 block">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 bg-rose-50 rounded-xl flex items-center justify-center">
                <span class="iconify text-rose-500 text-xl" data-icon="mdi:clipboard-list-outline"></span>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">
                Assigned
            </span>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ number_format($assignedTasks) }}</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Assigned Tasks</p>
    </a>

</div>

@endsection
