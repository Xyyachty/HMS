@extends('dean.layout.app')

@section('page_title', 'Students')
@section('students_active', 'active')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <!-- Header -->
    <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-50/50">
        <div>
            <h3 class="text-lg font-bold text-slate-800">Student Roster</h3>
            <p class="text-sm text-slate-500 mt-1">All enrolled students across departments.</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative flex-1 md:w-64">
                <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:magnify"></span>
                <input type="text" placeholder="Search students..." class="w-full pl-10 pr-4 h-10 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition bg-white">
            </div>
            <button class="bg-brand text-white px-5 h-10 rounded-xl text-sm font-bold hover:scale-105 transition shadow-md shadow-brand/20 flex items-center gap-2 shrink-0">
                <span class="iconify" data-icon="mdi:plus"></span> Add Student
            </button>
        </div>
    </div>

    <!-- Department Filter Tabs -->
    <div class="px-6 pt-5 flex gap-2 border-b border-slate-100 overflow-x-auto">
        <button class="px-4 py-2.5 text-xs font-bold text-white bg-brand rounded-t-xl border border-b-0 border-brand shadow-sm shrink-0">All <span class="ml-1 bg-white/20 px-1.5 py-0.5 rounded text-[10px]">1,240</span></button>
        <button class="px-4 py-2.5 text-xs font-semibold text-slate-500 hover:text-brand hover:bg-brand-soft rounded-t-xl transition shrink-0">Room Mgmt</button>
        <button class="px-4 py-2.5 text-xs font-semibold text-slate-500 hover:text-rose-accent hover:bg-rose-50 rounded-t-xl transition shrink-0">Front Desk</button>
        <button class="px-4 py-2.5 text-xs font-semibold text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-t-xl transition shrink-0">Restaurant</button>
        <button class="px-4 py-2.5 text-xs font-semibold text-slate-500 hover:text-plum-accent hover:bg-purple-50 rounded-t-xl transition shrink-0">Maintenance</button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="p-5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Student Info</th>
                    <th class="p-5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">ID No.</th>
                    <th class="p-5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Course