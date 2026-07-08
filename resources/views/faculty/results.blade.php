@extends('faculty.layout.app')

@section('page_title', 'Student Results & Outputs')
@section('results_active', 'active')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    
    <!-- Header Section -->
    <div class="p-6 border-b border-slate-100 bg-slate-50/50">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Student Outputs</h3>
                <p class="text-sm text-slate-500 mt-1">Review and grade the web pages created by students based on their assigned roles.</p>
            </div>
            
            <div class="relative">
                <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:magnify"></span>
                <input type="text" placeholder="Search student output..." class="pl-10 pr-4 h-10 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 w-64 transition">
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="mt-6 flex gap-2 border-b border-slate-200 -mb-6 pb-0 relative z-10 overflow-x-auto">
            <button class="px-5 py-3 text-xs font-bold text-white bg-brand rounded-t-xl border border-b-0 border-brand shadow-sm relative shrink-0">
                All Outputs
                <span class="absolute -top-2 -right-2 w-5 h-5 bg-slate-900 text-white text-[10px] rounded-full flex items-center justify-center border-2 border-white">24</span>
            </button>
            <button class="px-5 py-3 text-xs font-semibold text-slate-500 hover:text-brand hover:bg-brand-soft rounded-t-xl transition shrink-0">Room Mgmt</button>
            <button class="px-5 py-3 text-xs font-semibold text-slate-500 hover:text-rose-accent hover:bg-rose-50 rounded-t-xl transition shrink-0">Front Desk</button>
            <button class="px-5 py-3 text-xs font-semibold text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-t-xl transition shrink-0">Restaurant</button>
            <button class="px-5 py-3 text-xs font-semibold text-slate-500 hover:text-plum-accent hover:bg-purple-50 rounded-t-xl transition shrink-0">Maintenance</button>
        </div>
    </div>

    <!-- Results List -->
    <div class="divide-y divide-slate-100">

        <!-- Item 1: Room Management -->
        <div class="p-6 hover:bg-brand-soft/20 transition flex flex-col md:flex-row gap-6 items-start md:items-center">
            <div class="flex items-center gap-4 w-full md:w-1/4">
                <img src="https://ui-avatars.com/api/?name=Orao+S&background=DB2777&color=fff&size=40&font-size=0.4" class="w-12 h-12 rounded-xl shadow-md border-2 border-white">
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">Orao, Student</h4>
                    <p class="text-xs text-slate-400 font-mono">#2024-001</p>
                </div>
            </div>

            <div class="flex-1 w-full md:w-2/4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-brand-soft text-brand border border-brand/10">Room Management</span>
                    <span class="text-[10px] text-slate-400">Submitted: Oct 24, 2023</span>
                </div>
                <h5 class="font-semibold text-slate-700 text-sm">Room Availability Dashboard</h5>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">A custom page featuring a visual grid for room status (Vacant/Occupied) and booking forms.</p>
            </div>

            <div class="w-full md:w-1/4 flex flex-col items-end gap-3">
                <div class="text-right">
                    <span class="text-2xl font-bold text-brand">95%</span>
                    <span class="block text-[10px] text-slate-400 uppercase font-bold">Excellent</span>
                </div>
                <div class="flex gap-2">
                    <button class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50 hover:border-brand transition flex items-center gap-1.5">
                        <span class="iconify" data-icon="mdi:eye-outline"></span> View
                    </button>
                    <button class="px-4 py-2 bg-brand text-white rounded-xl text-xs font-bold hover:scale-105 transition shadow-sm shadow-brand/20 flex items-center gap-1.5">
                        <span class="iconify" data-icon="mdi:check"></span> Grade
                    </button>
                </div>
            </div>
        </div>

        <!-- Item 2: Front Desk -->
        <div class="p-6 hover:bg-rose-50/30 transition flex flex-col md:flex-row gap-6 items-start md:items-center">
            <div class="flex items-center gap-4 w-full md:w-1/4">
                <img src="https://ui-avatars.com/api/?name=Jane+S&background=F472B6&color=fff&size=40&font-size=0.4" class="w-12 h-12 rounded-xl shadow-md border-2 border-white">
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">Smith, Jane</h4>
                    <p class="text-xs text-slate-400 font-mono">#2024-002</p>
                </div>
            </div>

            <div class="flex-1 w-full md:w-2/4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-accent border border-rose-accent/10">Front Desk</span>
                    <span class="text-[10px] text-slate-400">Submitted: Oct 25, 2023</span>
                </div>
                <h5 class="font-semibold text-slate-700 text-sm">Guest Check-In Kiosk Interface</h5>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">A user-friendly landing page with keycard generation simulation and guest profile creation.</p>
            </div>

            <div class="w-full md:w-1/4 flex flex-col items-end gap-3">
                <div class="text-right">
                    <span class="text-2xl font-bold text-amber-500">88%</span>
                    <span class="block text-[10px] text-slate-400 uppercase font-bold">Good</span>
                </div>
                <div class="flex gap-2">
                    <button class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50 hover:border-rose-accent transition flex items-center gap-1.5">
                        <span class="iconify" data-icon="mdi:eye-outline"></span> View
                    </button>
                    <button class="px-4 py-2 bg-brand text-white rounded-xl text-xs font-bold hover:scale-105 transition shadow-sm shadow-brand/20 flex items-center gap-1.5">
                        <span class="iconify" data-icon="mdi:check"></span> Grade
                    </button>
                </div>
            </div>
        </div>

        <!-- Item 3: Restaurant (Pending) -->
        <div class="p-6 hover:bg-amber-50/30 transition flex flex-col md:flex-row gap-6 items-start md:items-center bg-amber-50/20">
            <div class="flex items-center gap-4 w-full md:w-1/4">
                <img src="https://ui-avatars.com/api/?name=Mike+J&background=A855F7&color=fff&size=40&font-size=0.4" class="w-12 h-12 rounded-xl shadow-md border-2 border-white">
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">Johnson, Mike</h4>
                    <p class="text-xs text-slate-400 font-mono">#2024-003</p>
                </div>
            </div>

            <div class="flex-1 w-full md:w-2/4">
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-400/10">Restaurant</span>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200 animate-pulse">Needs Review</span>
                </div>
                <h5 class="font-semibold text-slate-700 text-sm">Digital Menu & Reservation System</h5>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Interactive menu with filtering options and a simple table reservation form.</p>
            </div>

            <div class="w-full md:w-1/4 flex flex-col items-end gap-3">
                <div class="text-right">
                    <span class="text-2xl font-bold text-slate-300">--</span>
                    <span class="block text-[10px] text-amber-600 uppercase font-bold">Ungraded</span>
                </div>
                <div class="flex gap-2">
                    <button class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50 hover:border-amber-500 transition flex items-center gap-1.5">
                        <span class="iconify" data-icon="mdi:eye-outline"></span> View
                    </button>
                    <button class="px-4 py-2 bg-brand text-white rounded-xl text-xs font-bold hover:scale-105 transition shadow-sm shadow-brand/20 flex items-center gap-1.5">
                        <span class="iconify" data-icon="mdi:check"></span> Grade
                    </button>
                </div>
            </div>
        </div>

        <!-- Item 4: Maintenance -->
        <div class="p-6 hover:bg-purple-50/30 transition flex flex-col md:flex-row gap-6 items-start md:items-center">
            <div class="flex items-center gap-4 w-full md:w-1/4">
                <img src="https://ui-avatars.com/api/?name=Sarah+L&background=F59E0B&color=fff&size=40&font-size=0.4" class="w-12 h-12 rounded-xl shadow-md border-2 border-white">
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">Lee, Sarah</h4>
                    <p class="text-xs text-slate-400 font-mono">#2024-004</p>
                </div>
            </div>

            <div class="flex-1 w-full md:w-2/4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-plum-accent border border-plum-accent/10">Maintenance</span>
                    <span class="text-[10px] text-slate-400">Submitted: Oct 23, 2023</span>
                </div>
                <h5 class="font-semibold text-slate-700 text-sm">Maintenance Request Portal</h5>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">A system for tracking room cleanliness status and reporting maintenance issues.</p>
            </div>

            <div class="w-full md:w-1/4 flex flex-col items-end gap-3">
                <div class="text-right">
                    <span class="text-2xl font-bold text-green-600">100%</span>
                    <span class="block text-[10px] text-slate-400 uppercase font-bold">Perfect</span>
                </div>
                <div class="flex gap-2">
                    <button class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50 transition flex items-center gap-1.5">
                        <span class="iconify" data-icon="mdi:eye-outline"></span> View
                    </button>
                    <button class="px-4 py-2 bg-slate-100 text-slate-400 rounded-xl text-xs font-bold cursor-default flex items-center gap-1.5">
                        <span class="iconify" data-icon="mdi:lock-outline"></span> Locked
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Pagination -->
    <div class="p-6 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-center gap-4">
        <span class="text-xs text-slate-500 font-medium">Showing 1 to 4 of 24 entries</span>
        <div class="flex gap-1.5">
            <button class="w-9 h-9 flex items-center justify-center border border-slate-200 rounded-xl text-slate-400 hover:bg-white text-xs"><span class="iconify" data-icon="mdi:chevron-left"></span></button>
            <button class="w-9 h-9 flex items-center justify-center bg-brand text-white rounded-xl text-xs font-bold shadow-sm shadow-brand/20">1</button>
            <button class="w-9 h-9 flex items-center justify-center border border-slate-200 rounded-xl text-slate-600 hover:bg-white text-xs font-medium">2</button>
            <button class="w-9 h-9 flex items-center justify-center border border-slate-200 rounded-xl text-slate-600 hover:bg-white text-xs"><span class="iconify" data-icon="mdi:chevron-right"></span></button>
        </div>
    </div>
</div>
@endsection