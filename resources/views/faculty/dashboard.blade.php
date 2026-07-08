@extends('faculty.layout.app')

@section('page_title', 'Faculty Dashboard')
@section('dashboard_active', 'active')

@section('content')

<!-- Stats Cards -->

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">


<!-- Total Students -->
<div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
    <div class="flex items-center justify-between mb-3">
        <div class="w-11 h-11 bg-rose-50 rounded-xl flex items-center justify-center">
            <span class="iconify text-rose-500 text-xl" data-icon="mdi:account-group-outline"></span>
        </div>
        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
            Total
        </span>
    </div>
    <p class="text-2xl font-bold text-slate-900">{{ $totalStudents }}</p>
    <p class="text-xs text-slate-400 font-medium mt-0.5">Total Faculty</p>
</div>

<!-- Teams -->
<div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
    <div class="flex items-center justify-between mb-3">
        <div class="w-11 h-11 bg-rose-50 rounded-xl flex items-center justify-center">
            <span class="iconify text-rose-500 text-xl" data-icon="mdi:account-multiple-outline"></span>
        </div>
        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
            Active
        </span>
    </div>
    <p class="text-2xl font-bold text-slate-900">{{ $totalTeams }}</p>
    <p class="text-xs text-slate-400 font-medium mt-0.5">Teams</p>
</div>

<!-- Assigned Tasks -->
<div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
    <div class="flex items-center justify-between mb-3">
        <div class="w-11 h-11 bg-rose-50 rounded-xl flex items-center justify-center">
            <span class="iconify text-rose-500 text-xl" data-icon="mdi:clipboard-list-outline"></span>
        </div>
        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">
            Assigned
        </span>
    </div>
    <p class="text-2xl font-bold text-slate-900">{{ $assignedTasks }}</p>
    <p class="text-xs text-slate-400 font-medium mt-0.5">Assigned Tasks</p>
</div>

<!-- Complied Tasks -->
<div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
    <div class="flex items-center justify-between mb-3">
        <div class="w-11 h-11 bg-rose-50 rounded-xl flex items-center justify-center">
            <span class="iconify text-rose-500 text-xl" data-icon="mdi:clipboard-check-outline"></span>
        </div>
        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
            Done
        </span>
    </div>
    <p class="text-2xl font-bold text-slate-900">{{ $compliedTasks }}</p>
    <p class="text-xs text-slate-400 font-medium mt-0.5">Complied Tasks</p>
</div>


</div>

<!-- Quick Actions -->

<div class="mt-4 mb-4 flex justify-center">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden w-full">


    <div class="px-6 py-4 border-b border-slate-100 text-center">
        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">
            Quick Actions
        </h3>
    </div>

    <div class="p-4 grid grid-cols-3 gap-3">

        <a href="#" class="flex flex-col items-center gap-2 p-4 bg-rose-50 rounded-2xl border border-rose-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-200">
            <div class="w-12 h-12 bg-rose-500 rounded-xl flex items-center justify-center shadow-md">
                <span class="iconify text-white text-2xl" data-icon="mdi:account-plus-outline"></span>
            </div>
            <span class="text-xs font-bold text-rose-600">Add Student</span>
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
```

</div>

<!-- Main Grid -->

<div class="grid lg:grid-cols-3 gap-4">


<!-- Recent Student Activity -->
<div class="lg:col-span-2">

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800">
                    Recent Student Activity
                </h3>
                <p class="text-xs text-slate-400 font-light">
                    Latest activities and submissions
                </p>
            </div>

            <a href="#" class="text-xs text-rose-500 font-semibold hover:underline">
                View All
            </a>
        </div>

        <div class="divide-y divide-slate-50">

            <div class="px-6 py-4 flex items-center gap-4 hover:bg-rose-50/30 transition-colors">
                <img src="https://ui-avatars.com/api/?name=John+Doe&background=F43F5E&color=fff"
                    class="w-10 h-10 rounded-xl border-2 border-white shadow-sm">

                <div class="flex-1">
                    <p class="text-sm font-semibold text-slate-700">
                        John Doe submitted a project
                    </p>
                    <p class="text-[10px] text-slate-400">
                        Room Management System
                    </p>
                </div>

                <span class="text-[10px] text-slate-400">
                    2 mins ago
                </span>
            </div>

            <div class="px-6 py-4 flex items-center gap-4 hover:bg-rose-50/30 transition-colors">
                <img src="https://ui-avatars.com/api/?name=Jane+Smith&background=F43F5E&color=fff"
                    class="w-10 h-10 rounded-xl border-2 border-white shadow-sm">

                <div class="flex-1">
                    <p class="text-sm font-semibold text-slate-700">
                        Jane Smith updated role
                    </p>
                    <p class="text-[10px] text-slate-400">
                        Front Desk Manager
                    </p>
                </div>

                <span class="text-[10px] text-slate-400">
                    1 hour ago
                </span>
            </div>

            <div class="px-6 py-4 flex items-center gap-4 hover:bg-rose-50/30 transition-colors">
                <img src="https://ui-avatars.com/api/?name=Mike+Johnson&background=F43F5E&color=fff"
                    class="w-10 h-10 rounded-xl border-2 border-white shadow-sm">

                <div class="flex-1">
                    <p class="text-sm font-semibold text-slate-700">
                        Mike Johnson joined Restaurant Department
                    </p>
                    <p class="text-[10px] text-slate-400">
                        Group Assignment
                    </p>
                </div>

                <span class="text-[10px] text-slate-400">
                    3 hours ago
                </span>
            </div>

        </div>
    </div>

</div>

<!-- Activity Logs -->
<div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

        <div class="px-6 py-5 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800">
                Activity Logs
            </h3>
            <p class="text-xs text-slate-400 font-light">
                Latest faculty events
            </p>
        </div>

        <div class="divide-y divide-slate-100">

            <div class="px-6 py-4 flex items-start gap-3">
                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <span class="iconify text-emerald-600 text-sm" data-icon="mdi:check"></span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">
                        Project approved
                    </p>
                    <p class="text-[10px] text-slate-400">
                        10 mins ago
                    </p>
                </div>
            </div>

            <div class="px-6 py-4 flex items-start gap-3">
                <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center">
                    <span class="iconify text-rose-500 text-sm" data-icon="mdi:account-plus"></span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">
                        New student registered
                    </p>
                    <p class="text-[10px] text-slate-400">
                        1 hour ago
                    </p>
                </div>
            </div>

            <div class="px-6 py-4 flex items-start gap-3">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                    <span class="iconify text-amber-600 text-sm" data-icon="mdi:clock-outline"></span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">
                        Pending review detected
                    </p>
                    <p class="text-[10px] text-amber-600">
                        Action Needed
                    </p>
                </div>
            </div>

        </div>

    </div>

</div>


</div>

@endsection
