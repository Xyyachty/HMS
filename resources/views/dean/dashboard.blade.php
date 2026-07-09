@extends('dean.layouts.app')

@section('page_title', 'Dean Dashboard')
@section('dashboard_active', 'active')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-3 gap-3 mb-4">
    <!-- Total Students -->
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 bg-rose-50 rounded-xl flex items-center justify-center">
                <span class="iconify text-rose-500 text-xl" data-icon="mdi:account-group-outline"></span>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full flex items-center gap-0.5">
                <span class="iconify text-xs" data-icon="mdi:trending-up"></span> +12%
            </span>
        </div>
        <p class="text-2xl font-bold text-slate-900">1,240</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Total Students</p>
    </div>

    <!-- Total Faculty -->
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 bg-rose-50 rounded-xl flex items-center justify-center">
                <span class="iconify text-rose-500 text-xl" data-icon="mdi:school-outline"></span>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">3 Pending</span>
        </div>
        <p class="text-2xl font-bold text-slate-900">58</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Total Faculty</p>
    </div>

    <!-- Teams -->
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 bg-rose-50 rounded-xl flex items-center justify-center">
                <span class="iconify text-rose-500 text-xl" data-icon="mdi:account-multiple-plus-outline"></span>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full flex items-center gap-0.5">
                <span class="iconify text-xs" data-icon="mdi:trending-up"></span> +5
            </span>
        </div>
        <p class="text-2xl font-bold text-slate-900">36</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Teams</p>
    </div>
</div>

<!-- Quick Actions (Centered) -->
<div class="mt-4 mb-4 flex justify-center">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden w-full">
        <div class="px-6 py-4 border-b border-slate-100 text-center">
            <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Quick Actions</h3>
        </div>
        <div class="p-4 grid grid-cols-3 gap-3">
            <a href="#" class="flex flex-col items-center gap-2 p-4 bg-rose-50 rounded-2xl border border-rose-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-200">
                <div class="w-12 h-12 bg-rose-500 rounded-xl flex items-center justify-center shadow-md">
                    <span class="iconify text-white text-2xl" data-icon="mdi:account-tie"></span>
                </div>
                <span class="text-xs font-bold text-rose-600">Add Faculty</span>
            </a>
            <a href="#" class="flex flex-col items-center gap-2 p-4 bg-rose-50 rounded-2xl border border-rose-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-200">
                <div class="w-12 h-12 bg-rose-500 rounded-xl flex items-center justify-center shadow-md">
                    <span class="iconify text-white text-2xl" data-icon="mdi:account-multiple-outline"></span>
                </div>
                <span class="text-xs font-bold text-rose-600">Teams</span>
            </a>
            <a href="#" class="flex flex-col items-center gap-2 p-4 bg-rose-50 rounded-2xl border border-rose-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-200">
                <div class="w-12 h-12 bg-rose-500 rounded-xl flex items-center justify-center shadow-md">
                    <span class="iconify text-white text-2xl" data-icon="mdi:file-chart-outline"></span>
                </div>
                <span class="text-xs font-bold text-rose-600">Reports</span>
            </a>
        </div>
    </div>
</div>

<!-- Main Grid -->
<div class="grid lg:grid-cols-3 gap-4">

    <!-- ===== LEFT: Tables (2 cols) ===== -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Recent Students -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Recent Students TEST</h3>
                    <p class="text-xs text-slate-400 font-light">Latest enrollments & status updates</p>
                </div>
                <a href="#" class="text-xs text-rose-500 font-semibold hover:underline">View All</a>
            </div>
            <div class="divide-y divide-slate-50">
                <div class="px-6 py-4 flex items-center gap-4 hover:bg-rose-50/30 transition-colors">
                    <img src="https://ui-avatars.com/api/?name=John+D&background=F43F5E&color=fff&size=40&font-size=0.4" class="w-10 h-10 rounded-xl border-2 border-white shadow-sm">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-700">John Doe</p>
                        <p class="text-[10px] text-slate-400">BSHM · #2024-001</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200">Active</span>
                </div>
                <div class="px-6 py-4 flex items-center gap-4 hover:bg-rose-50/30 transition-colors">
                    <img src="https://ui-avatars.com/api/?name=Jane+S&background=F43F5E&color=fff&size=40&font-size=0.4" class="w-10 h-10 rounded-xl border-2 border-white shadow-sm">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-700">Jane Smith</p>
                        <p class="text-[10px] text-slate-400">BSTM · #2024-002</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-200">Pending</span>
                </div>
                <div class="px-6 py-4 flex items-center gap-4 hover:bg-rose-50/30 transition-colors">
                    <img src="https://ui-avatars.com/api/?name=Mike+J&background=F43F5E&color=fff&size=40&font-size=0.4" class="w-10 h-10 rounded-xl border-2 border-white shadow-sm">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-700">Mike Johnson</p>
                        <p class="text-[10px] text-slate-400">BSHM · #2024-003</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200">Active</span>
                </div>
                <div class="px-6 py-4 flex items-center gap-4 hover:bg-rose-50/30 transition-colors">
                    <img src="https://ui-avatars.com/api/?name=Sarah+L&background=F43F5E&color=fff&size=40&font-size=0.4" class="w-10 h-10 rounded-xl border-2 border-white shadow-sm">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-700">Sarah Lee</p>
                        <p class="text-[10px] text-slate-400">BSHM · #2024-004</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">Inactive</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== RIGHT: Sidebar Widget ===== -->
    <div class="space-y-6">

        <!-- Activity Logs -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Activity Logs</h3>
                    <p class="text-xs text-slate-400 font-light">Latest system events</p>
                </div>
                <a href="#" class="text-xs text-rose-500 font-semibold hover:underline">View All</a>
            </div>
            <div class="divide-y divide-slate-100">
                <div class="px-6 py-4 flex items-start gap-3">
                    <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                        <span class="iconify text-emerald-600 text-sm" data-icon="mdi:check"></span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-700">Prof. Alcantara approved 5 students</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Room Management · 10 min ago</p>
                    </div>
                </div>
                <div class="px-6 py-4 flex items-start gap-3">
                    <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                        <span class="iconify text-rose-500 text-sm" data-icon="mdi:account-plus"></span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-700">12 new students registered</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">System · 1 hour ago</p>
                    </div>
                </div>
                <div class="px-6 py-4 flex items-start gap-3">
                    <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                        <span class="iconify text-amber-600 text-sm" data-icon="mdi:clock-outline"></span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-700">Prof. Villanueva pending approval</p>
                        <p class="text-[10px] text-amber-600 mt-0.5 font-medium">Action needed · 3 hours ago</p>
                    </div>
                </div>
                <div class="px-6 py-4 flex items-start gap-3">
                    <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                        <span class="iconify text-rose-500 text-sm" data-icon="mdi:account-multiple-plus"></span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-700">New team "Group Alpha" created</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Team Management · 5 hours ago</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection