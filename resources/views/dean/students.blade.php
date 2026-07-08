@extends('dean.layouts.app')

@section('page_title', 'Students')
@section('students_active', 'active')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <!-- Header -->
    <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-50/50">
        <div>
            <h3 class="text-lg font-bold text-slate-800">Student Roster</h3>
            <p class="text-sm text-slate-500 mt-1">All enrolled students across roles.</p>
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

    <!-- Role Filter Tabs -->
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
                    <th class="p-5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Course</th>
                    <th class="p-5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Role</th>
                    <th class="p-5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Group</th>
                    <th class="p-5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Progress</th>
                    <th class="p-5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Status</th>
                    <th class="p-5 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <!-- Student 1 -->
                <tr class="hover:bg-brand-soft/20 transition-colors">
                    <td class="p-5">
                        <div class="flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name=John+Doe&background=DB2777&color=fff&size=40&font-size=0.4" class="w-10 h-10 rounded-xl shadow-sm border-2 border-white">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">John Doe</p>
                                <p class="text-xs text-slate-400">john.doe@student.edu</p>
                            </div>
                        </div>
                    </td>
                    <td class="p-5 text-sm text-slate-500 font-mono">2024-001</td>
                    <td class="p-5 text-sm text-slate-600">BSHM</td>
                    <td class="p-5">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-brand-soft text-brand border border-brand/10">Room Mgmt</span>
                    </td>
                    <td class="p-5 text-sm text-slate-600">Grand Hotel - G3</td>
                    <td class="p-5">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-slate-200 rounded-full h-2 max-w-[100px]">
                                <div class="bg-gradient-to-r from-brand-light to-brand h-2 rounded-full" style="width: 85%"></div>
                            </div>
                            <span class="text-xs font-bold text-brand">85%</span>
                        </div>
                    </td>
                    <td class="p-5">
                        <span class="flex items-center gap-1.5 text-xs font-semibold text-green-600">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Active
                        </span>
                    </td>
                    <td class="p-5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 transition-colors">
                                <span class="iconify text-slate-400 hover:text-brand" data-icon="mdi:eye-outline"></span>
                            </button>
                            <button class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 transition-colors">
                                <span class="iconify text-slate-400 hover:text-brand" data-icon="mdi:pencil-outline"></span>
                            </button>
                        </div>
                    </td>
                </tr>

                <!-- Student 2 -->
                <tr class="hover:bg-rose-50/30 transition-colors">
                    <td class="p-5">
                        <div class="flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name=Jane+Smith&background=F472B6&color=fff&size=40&font-size=0.4" class="w-10 h-10 rounded-xl shadow-sm border-2 border-white">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Jane Smith</p>
                                <p class="text-xs text-slate-400">jane.smith@student.edu</p>
                            </div>
                        </div>
                    </td>
                    <td class="p-5 text-sm text-slate-500 font-mono">2024-002</td>
                    <td class="p-5 text-sm text-slate-600">BSTM</td>
                    <td class="p-5">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-accent border border-rose-accent/10">Front Desk</span>
                    </td>
                    <td class="p-5 text-sm text-slate-600">Grand Hotel - G3</td>
                    <td class="p-5">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-slate-200 rounded-full h-2 max-w-[100px]">
                                <div class="bg-gradient-to-r from-rose-accent to-brand h-2 rounded-full" style="width: 72%"></div>
                            </div>
                            <span class="text-xs font-bold text-rose-accent">72%</span>
                        </div>
                    </td>
                    <td class="p-5">
                        <span class="flex items-center gap-1.5 text-xs font-semibold text-green-600">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Active
                        </span>
                    </td>
                    <td class="p-5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 transition-colors">
                                <span class="iconify text-slate-400 hover:text-brand" data-icon="mdi:eye-outline"></span>
                            </button>
                            <button class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 transition-colors">
                                <span class="iconify text-slate-400 hover:text-brand" data-icon="mdi:pencil-outline"></span>
                            </button>
                        </div>
                    </td>
                </tr>

                <!-- Student 3 -->
                <tr class="hover:bg-amber-50/30 transition-colors">
                    <td class="p-5">
                        <div class="flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name=Mike+Johnson&background=F59E0B&color=fff&size=40&font-size=0.4" class="w-10 h-10 rounded-xl shadow-sm border-2 border-white">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Mike Johnson</p>
                                <p class="text-xs text-slate-400">mike.j@student.edu</p>
                            </div>
                        </div>
                    </td>
                    <td class="p-5 text-sm text-slate-500 font-mono">2024-003</td>
                    <td class="p-5 text-sm text-slate-600">BSHM</td>
                    <td class="p-5">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-400/10">Restaurant</span>
                    </td>
                    <td class="p-5 text-sm text-slate-600">Luxury Inn - G1</td>
                    <td class="p-5">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-slate-200 rounded-full h-2 max-w-[100px]">
                                <div class="bg-gradient-to-r from-amber-400 to-amber-600 h-2 rounded-full" style="width: 90%"></div>
                            </div>
                            <span class="text-xs font-bold text-amber-600">90%</span>
                        </div>
                    </td>
                    <td class="p-5">
                        <span class="flex items-center gap-1.5 text-xs font-semibold text-green-600">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Active
                        </span>
                    </td>
                    <td class="p-5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 transition-colors">
                                <span class="iconify text-slate-400 hover:text-brand" data-icon="mdi:eye-outline"></span>
                            </button>
                            <button class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 transition-colors">
                                <span class="iconify text-slate-400 hover:text-brand" data-icon="mdi:pencil-outline"></span>
                            </button>
                        </div>
                    </td>
                </tr>

                <!-- Student 4 -->
                <tr class="hover:bg-purple-50/30 transition-colors">
                    <td class="p-5">
                        <div class="flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name=Sarah+Lee&background=A855F7&color=fff&size=40&font-size=0.4" class="w-10 h-10 rounded-xl shadow-sm border-2 border-white">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Sarah Lee</p>
                                <p class="text-xs text-slate-400">sarah.lee@student.edu</p>
                            </div>
                        </div>
                    </td>
                    <td class="p-5 text-sm text-slate-500 font-mono">2024-004</td>
                    <td class="p-5 text-sm text-slate-600">BSHM</td>
                    <td class="p-5">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-plum-accent border border-plum-accent/10">Maintenance</span>
                    </td>
                    <td class="p-5 text-sm text-slate-600">Luxury Inn - G1</td>
                    <td class="p-5">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-slate-200 rounded-full h-2 max-w-[100px]">
                                <div class="bg-gradient-to-r from-plum-accent to-brand h-2 rounded-full" style="width: 45%"></div>
                            </div>
                            <span class="text-xs font-bold text-plum-accent">45%</span>
                        </div>
                    </td>
                    <td class="p-5">
                        <span class="flex items-center gap-1.5 text-xs font-semibold text-amber-600">
                            <span class="w-2 h-2 bg-amber-500 rounded-full"></span> Pending
                        </span>
                    </td>
                    <td class="p-5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 transition-colors">
                                <span class="iconify text-slate-400 hover:text-brand" data-icon="mdi:eye-outline"></span>
                            </button>
                            <button class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 transition-colors">
                                <span class="iconify text-slate-400 hover:text-brand" data-icon="mdi:pencil-outline"></span>
                            </button>
                        </div>
                    </td>
                </tr>

                <!-- Student 5 -->
                <tr class="hover:bg-brand-soft/20 transition-colors">
                    <td class="p-5">
                        <div class="flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name=Carlos+Reyes&background=DB2777&color=fff&size=40&font-size=0.4" class="w-10 h-10 rounded-xl shadow-sm border-2 border-white">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Carlos Reyes</p>
                                <p class="text-xs text-slate-400">carlos.r@student.edu</p>
                            </div>
                        </div>
                    </td>
                    <td class="p-5 text-sm text-slate-500 font-mono">2024-005</td>
                    <td class="p-5 text-sm text-slate-600">BSTM</td>
                    <td class="p-5">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-brand-soft text-brand border border-brand/10">Room Mgmt</span>
                    </td>
                    <td class="p-5 text-sm text-slate-600">Paradise Resort - G2</td>
                    <td class="p-5">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-slate-200 rounded-full h-2 max-w-[100px]">
                                <div class="bg-gradient-to-r from-brand-light to-brand h-2 rounded-full" style="width: 68%"></div>
                            </div>
                            <span class="text-xs font-bold text-brand">68%</span>
                        </div>
                    </td>
                    <td class="p-5">
                        <span class="flex items-center gap-1.5 text-xs font-semibold text-green-600">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Active
                        </span>
                    </td>
                    <td class="p-5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 transition-colors">
                                <span class="iconify text-slate-400 hover:text-brand" data-icon="mdi:eye-outline"></span>
                            </button>
                            <button class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 transition-colors">
                                <span class="iconify text-slate-400 hover:text-brand" data-icon="mdi:pencil-outline"></span>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="p-6 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-center gap-4">
        <span class="text-xs text-slate-500 font-medium">Showing 1 to 5 of 1,240 entries</span>
        <div class="flex gap-1.5">
            <button class="w-9 h-9 flex items-center justify-center border border-slate-200 rounded-xl text-slate-400 hover:bg-white text-xs">
                <span class="iconify" data-icon="mdi:chevron-left"></span>
            </button>
            <button class="w-9 h-9 flex items-center justify-center bg-brand text-white rounded-xl text-xs font-bold shadow-sm shadow-brand/20">1</button>
            <button class="w-9 h-9 flex items-center justify-center border border-slate-200 rounded-xl text-slate-600 hover:bg-white text-xs font-medium">2</button>
            <button class="w-9 h-9 flex items-center justify-center border border-slate-200 rounded-xl text-slate-600 hover:bg-white text-xs font-medium">3</button>
            <button class="w-9 h-9 flex items-center justify-center border border-slate-200 rounded-xl text-slate-600 hover:bg-white text-xs">
                <span class="iconify" data-icon="mdi:chevron-right"></span>
            </button>
        </div>
    </div>
</div>
@endsection
