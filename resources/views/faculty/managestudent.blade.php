@extends('faculty.layout.app')

@section('page_title', 'Manage Students')
@section('students_active', 'active')

@push('styles')
<style>
    /* Bulk upload drop zone */
    #bulkDropZone.drag-over {
        border-color: #059669;
        background-color: rgba(5,150,105,.07);
    }
    #bulkImportBtn:disabled {
        opacity:.6;
        cursor:not-allowed;
        transform:none;
    }
</style>
@endpush

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <!-- Header -->
    <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50/50">
        <div>
            <h3 class="text-lg font-bold text-slate-800">Student Roster</h3>
            <p class="text-sm text-slate-500 mt-1">Manage student accounts across the system.</p>
        </div>
        <div class="flex gap-3">
            <button onclick="openModal('bulkUploadModal')" class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:scale-105 transition shadow-md shadow-emerald-600/20 flex items-center gap-2">
                <span class="iconify" data-icon="mdi:upload"></span> Bulk Upload
            </button>
            <button onclick="openModal('createStudentModal')" class="bg-brand text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:scale-105 transition shadow-md shadow-brand/20 flex items-center gap-2">
                <span class="iconify" data-icon="mdi:account-plus-outline"></span> Add Student
            </button>
        </div>
    </div>

    <!-- Tab Bar -->
    @if (session('success'))
        <div id="successAlert" class="mx-6 mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <!-- DataTable -->
    <div class="p-6">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table id="studentsTable" class="w-full text-sm">
                <thead>
                    <tr class="bg-gradient-to-r from-pink-50 to-rose-50 border-b border-pink-100">
                    <th class="px-6 py-4 text-left text-[11px] font-extrabold uppercase tracking-widest text-slate-600">
                        ID Number
                    </th>
                    <th class="px-6 py-4 text-left text-[11px] font-extrabold uppercase tracking-widest text-slate-600">
                        Student Name
                    </th>
                    <th class="px-6 py-4 text-left text-[11px] font-extrabold uppercase tracking-widest text-slate-600">
                        Email
                    </th>
                    <th class="px-6 py-4 text-left text-[11px] font-extrabold uppercase tracking-widest text-slate-600">
                        Phone Number
                    </th>
                    <th class="px-6 py-4 text-left text-[11px] font-extrabold uppercase tracking-widest text-slate-600">
                        Status
                    </th>
                    <th class="px-6 py-4 text-left text-[11px] font-extrabold uppercase tracking-widest text-slate-600">
                        Joined
                    </th>
                    <th class="px-6 py-4 text-center text-[11px] font-extrabold uppercase tracking-widest text-slate-600">
                        Action
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($students ?? [] as $student)
                    @php
                        $user = $student->user;

                        $displayName = trim(implode(' ', array_filter([
                            $user->last_name ?? null,
                            $user->first_name ?? null,
                            $user->middle_name ?? null,
                        ])));

                        $displayName = $displayName !== ''
                            ? $displayName
                            : ($user->name ?? 'Student');

                        $status = $user->status ?? 'active';
                    @endphp

                    <tr
                        data-student-id="{{ $student->student_id }}"
                        data-user-id="{{ $user->id ?? '' }}"
                        class="hover:bg-pink-50/60 transition-all duration-200"
                    >
                        <td class="px-6 py-4">
                            <span class="font-mono text-slate-600 font-semibold">
                                {{ $student->student_id }}
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img
                                    src="https://ui-avatars.com/api/?name={{ urlencode($displayName) }}&background=DB2777&color=fff&size=40"
                                    class="w-10 h-10 rounded-xl shadow-sm border border-pink-100"
                                    alt="{{ $displayName }}"
                                >

                                <div>
                                    <p class="font-bold text-slate-800">
                                        {{ $displayName }}
                                    </p>

                                    <p class="text-xs text-slate-400">
                                        Student Account
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <span class="text-slate-500">
                                {{ $user->email ?? '—' }}
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            <span class="font-medium text-slate-700">
                                {{ $user->phone_number ?? '—' }}
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            @if ($status === 'active')
                                <span class="inline-flex items-center gap-2 rounded-full bg-green-50 border border-green-200 px-3 py-1 text-xs font-bold text-green-700">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    Active
                                </span>

                            @elseif ($status === 'pending')
                                <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 border border-amber-200 px-3 py-1 text-xs font-bold text-amber-700">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    Pending
                                </span>

                            @else
                                <span class="inline-flex items-center gap-2 rounded-full bg-red-50 border border-red-200 px-3 py-1 text-xs font-bold text-red-700">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                    Suspended
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-slate-500">
                            {{ optional($user->created_at)->format('M d, Y') }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-center">
                                <button
                                    onclick="openUpdateModal(this)"
                                    data-user-id="{{ $user->id ?? '' }}"
                                    data-student-id="{{ $student->student_id }}"
                                    data-first-name="{{ $user->first_name ?? '' }}"
                                    data-middle-name="{{ $user->middle_name ?? '' }}"
                                    data-last-name="{{ $user->last_name ?? '' }}"
                                    data-email="{{ $user->email ?? '' }}"
                                    data-phone-number="{{ $user->phone_number ?? '' }}"
                                    data-status="{{ $status }}"
                                    class="inline-flex items-center gap-2 rounded-xl bg-pink-50 px-4 py-2 text-xs font-bold text-pink-700 border border-pink-200 hover:bg-pink-100 transition-all duration-200"
                                >
                                    <span class="iconify text-base" data-icon="mdi:pencil-outline"></span>
                                    Update
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <span class="iconify text-5xl text-slate-300" data-icon="mdi:account-group-outline"></span>

                                <p class="font-semibold text-slate-500">
                                    No students found
                                </p>

                                <p class="text-xs text-slate-400">
                                    Student records will appear here.
                                </p>
                            </div>
                        </td>
                    </tr>

                @endforelse
            </tbody>
        </table>
        </div>

        <!-- Pagination Links -->
        @if($students->hasPages())
        <div class="mt-6 flex items-center justify-between">
            <div class="text-sm text-slate-600">
                Showing <span class="font-semibold">{{ $students->firstItem() }}</span> to <span class="font-semibold">{{ $students->lastItem() }}</span> of <span class="font-semibold">{{ $students->total() }}</span> students
            </div>
            <div class="flex gap-2">
                @if ($students->onFirstPage())
                    <span class="px-4 py-2 rounded-xl bg-slate-100 text-slate-400 text-sm font-semibold cursor-not-allowed">
                        Previous
                    </span>
                @else
                    <a href="{{ $students->previousPageUrl() }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">
                        Previous
                    </a>
                @endif

                <div class="flex gap-1">
                    @foreach ($students->getUrlRange(1, $students->lastPage()) as $page => $url)
                        @if ($page == $students->currentPage())
                            <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-brand text-white text-sm font-bold shadow-md shadow-brand/20">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                </div>

                @if ($students->hasMorePages())
                    <a href="{{ $students->nextPageUrl() }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">
                        Next
                    </a>
                @else
                    <span class="px-4 py-2 rounded-xl bg-slate-100 text-slate-400 text-sm font-semibold cursor-not-allowed">
                        Next
                    </span>
                @endif
            </div>
        </div>
        @endif
    </div>

<!-- ==================== BULK UPLOAD MODAL ==================== -->
<div id="bulkUploadModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('bulkUploadModal')"></div>
    <div class="relative top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-3xl shadow-2xl border border-slate-100" style="width: 820px; max-width: 94vw; max-height: 92vh; overflow-y: auto;">

        <!-- Modal Header -->
        <div class="bg-emerald-50 px-6 py-4 border-b border-emerald-100 flex justify-between items-center sticky top-0 z-10">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-600 flex items-center justify-center shadow">
                    <span class="iconify text-white text-xl" data-icon="mdi:upload-multiple"></span>
                </div>
                <div>
                    <h4 class="font-bold text-emerald-800 text-lg leading-none">Bulk Upload Students</h4>
                    <p class="text-xs text-emerald-600 mt-0.5">Upload an Excel file (.xlsx) to add multiple students at once</p>
                </div>
            </div>
            <button onclick="closeBulkModal()" class="text-slate-400 hover:text-red-500 hover:bg-red-50 w-8 h-8 rounded-full transition flex items-center justify-center">
                <span class="iconify text-xl" data-icon="mdi:close"></span>
            </button>
        </div>

        <div class="p-6 space-y-5">

            <!-- Step indicator -->
            <div class="flex items-center gap-2 text-xs" id="bulkStepIndicator">
                <span class="flex items-center gap-1.5 font-semibold text-emerald-700" id="stepBadge1">
                    <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px] font-bold">1</span>
                    Upload Excel
                </span>
                <span class="text-slate-300 font-bold">›</span>
                <span class="flex items-center gap-1.5 font-semibold text-slate-400" id="stepBadge2">
                    <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-[10px] font-bold">2</span>
                    Preview
                </span>
                <span class="text-slate-300 font-bold">›</span>
                <span class="flex items-center gap-1.5 font-semibold text-slate-400" id="stepBadge3">
                    <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-[10px] font-bold">3</span>
                    Results
                </span>
            </div>

            <!-- Step 1: Drop zone + template -->
            <div id="bulkStep1">
                <!-- Excel Format Info -->
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex gap-3 items-start">
                    <span class="iconify text-blue-500 text-xl mt-0.5 shrink-0" data-icon="mdi:microsoft-excel"></span>
                    <div class="text-xs text-blue-700 leading-relaxed">
                        <p class="font-bold mb-1">Required Excel columns (Row 1 = headers):</p>
                        <code class="bg-blue-100 px-1 rounded">student_id, first_name, last_name, email</code>
                        <span class="mx-1 text-blue-400">+</span>
                        optional: <code class="bg-blue-100 px-1 rounded">middle_name, phone_number</code>
                        <br><span class="text-blue-500 mt-1 block">Default password will be <strong>hms@{student_id}</strong> for each student.</span>
                    </div>
                </div>

                <!-- Download template -->
                <button onclick="downloadExcelTemplate()" class="flex items-center gap-2 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-4 py-2 rounded-xl transition mt-3">
                    <span class="iconify text-base" data-icon="mdi:file-download-outline"></span>
                    Download Excel Template (.xlsx)
                </button>

                <!-- Drop Zone -->
                <div id="bulkDropZone"
                    class="mt-4 border-2 border-dashed border-slate-300 rounded-2xl p-10 flex flex-col items-center justify-center gap-3 cursor-pointer transition hover:border-emerald-400 hover:bg-emerald-50/40"
                    onclick="document.getElementById('bulkExcelInput').click()"
                    ondragover="bulkDragOver(event)"
                    ondragleave="bulkDragLeave(event)"
                    ondrop="bulkDrop(event)">
                    <span class="iconify text-5xl text-slate-300" data-icon="mdi:microsoft-excel"></span>
                    <p class="text-sm font-semibold text-slate-600">Drag & drop your Excel file here</p>
                    <p class="text-xs text-slate-400">or click to browse — max 5MB, .xlsx / .xls files</p>
                    <input id="bulkExcelInput" name="excel_file" type="file" accept=".xlsx,.xls,.ods" class="hidden" onchange="bulkFileSelected(this.files[0])">
                </div>
                <p id="bulkFileInfo" class="text-xs text-slate-500 mt-2 hidden"></p>
            </div>

            <!-- Step 2: Preview -->
            <div id="bulkStep2" class="hidden">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-bold text-slate-700">Preview <span id="bulkPreviewCount" class="text-emerald-600"></span></p>
                    <button onclick="bulkResetToStep1()" class="text-xs text-slate-500 hover:text-brand underline">Change file</button>
                </div>
                <div class="overflow-x-auto rounded-xl border border-slate-200 max-h-56">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 font-bold text-slate-500 uppercase tracking-widest">#</th>
                                <th class="px-3 py-2 font-bold text-slate-500 uppercase tracking-widest">Student ID</th>
                                <th class="px-3 py-2 font-bold text-slate-500 uppercase tracking-widest">Last Name</th>
                                <th class="px-3 py-2 font-bold text-slate-500 uppercase tracking-widest">First Name</th>
                                <th class="px-3 py-2 font-bold text-slate-500 uppercase tracking-widest">Email</th>
                                <th class="px-3 py-2 font-bold text-slate-500 uppercase tracking-widest">Phone</th>
                            </tr>
                        </thead>
                        <tbody id="bulkPreviewBody" class="divide-y divide-slate-100"></tbody>
                    </table>
                </div>
                <p class="text-xs text-slate-400 mt-2">Showing first 10 rows. All rows will be imported.</p>
            </div>

            <!-- Step 3: Results -->
            <div id="bulkStep3" class="hidden">
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-green-50 border border-green-200 rounded-xl px-5 py-4 flex items-center gap-3">
                        <span class="iconify text-3xl text-green-500" data-icon="mdi:check-circle-outline"></span>
                        <div><p class="text-2xl font-black text-green-700" id="bulkResultCreated">0</p><p class="text-xs text-green-600 font-semibold">Imported</p></div>
                    </div>
                    <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 flex items-center gap-3">
                        <span class="iconify text-3xl text-red-400" data-icon="mdi:alert-circle-outline"></span>
                        <div><p class="text-2xl font-black text-red-600" id="bulkResultFailed">0</p><p class="text-xs text-red-500 font-semibold">Failed</p></div>
                    </div>
                </div>
                <div id="bulkResultsTableWrap" class="hidden">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Row-by-row results</p>
                    <div class="overflow-x-auto rounded-xl border border-slate-200 max-h-48">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 sticky top-0">
                                <tr>
                                    <th class="px-3 py-2 font-bold text-slate-500">Row</th>
                                    <th class="px-3 py-2 font-bold text-slate-500">Name</th>
                                    <th class="px-3 py-2 font-bold text-slate-500">Status</th>
                                    <th class="px-3 py-2 font-bold text-slate-500">Note</th>
                                </tr>
                            </thead>
                            <tbody id="bulkResultsBody" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex justify-between items-center sticky bottom-0">
            <button onclick="closeBulkModal()" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-200 transition font-semibold text-sm">Close</button>
            <div class="flex gap-3">
                <button id="bulkImportBtn" onclick="startBulkImport()" class="hidden px-6 py-2 bg-emerald-600 text-white rounded-xl font-bold text-sm hover:scale-105 transition shadow-md shadow-emerald-600/20 flex items-center gap-2">
                    <span class="iconify" data-icon="mdi:cloud-upload"></span>
                    <span id="bulkImportBtnLabel">Import Now</span>
                </button>
                <button id="bulkImportAgainBtn" onclick="bulkResetToStep1()" class="hidden px-5 py-2 bg-emerald-600 text-white rounded-xl font-bold text-sm hover:scale-105 transition">
                    Upload Another
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ==================== CREATE STUDENT MODAL ==================== -->
<div id="createStudentModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('createStudentModal')"></div>
    <div class="relative top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-100" style="width: 760px; max-width: 92vw; max-height: 90vh; overflow-y: auto;">
        <div class="bg-brand-soft px-6 py-4 border-b border-brand/10 flex justify-between items-center sticky top-0 z-10">
            <h4 class="font-bold text-brand text-lg">Add Student</h4>
            <button onclick="closeModal('createStudentModal')" class="text-slate-400 hover:text-brand hover:bg-white w-8 h-8 rounded-full transition flex items-center justify-center">
                <span class="iconify text-xl" data-icon="mdi:close"></span>
            </button>
        </div>
        <form method="POST" action="{{ route('faculty.students.store') }}" onsubmit="return validateStudentPasswords()">
            @csrf
            @if ($errors->any())
                <div class="mx-6 mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Student ID</label>
                    <input name="student_id" type="text" placeholder="e.g. 2024-001" required class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Last Name</label>
                    <input name="last_name" type="text" placeholder="e.g. Smith" required class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">First Name</label>
                    <input name="first_name" type="text" placeholder="e.g. Jane" required class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Middle Initial</label>
                    <input name="middle_name" type="text" placeholder="e.g. A." class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Email</label>
                    <input name="email" type="text" placeholder="username or user@hms.edu" required class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Phone Number</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-600 text-xs font-medium">+63</span>
                        <input name="phone_number" type="text" placeholder="912 345 6789" class="w-full h-10 pl-12 pr-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Password</label>
                    <div class="relative">
                        <input id="createStudentPassword" name="password" type="password" placeholder="Min. 8 characters" required oninput="validateStudentPasswords()" class="w-full h-10 px-3 pr-10 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <span class="iconify text-lg" data-icon="mdi:eye-off-outline"></span>
                        </button>
                    </div>
                    <p id="createStudentPasswordHelp" class="mt-1 text-[11px] text-red-500 hidden">Password must be at least 8 characters.</p>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Confirm Password</label>
                    <div class="relative">
                        <input id="createStudentPasswordConfirm" name="password_confirmation" type="password" placeholder="Re-enter password" required oninput="validateStudentPasswords()" class="w-full h-10 px-3 pr-10 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <span class="iconify text-lg" data-icon="mdi:eye-off-outline"></span>
                        </button>
                    </div>
                    <p id="createStudentPasswordMatch" class="mt-1 text-[11px] text-red-500 hidden">Passwords do not match.</p>
                </div>
            </div>
            <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 sticky bottom-0">
                <button type="button" onclick="closeModal('createStudentModal')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-200 transition font-semibold text-sm">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-brand text-white rounded-xl font-bold text-sm hover:scale-105 transition shadow-md shadow-brand/20">Add Student</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== UPDATE STUDENT MODAL ==================== -->
<div id="updateStudentModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('updateStudentModal')"></div>
    <div class="relative top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-100" style="width: 760px; max-width: 92vw; max-height: 90vh; overflow-y: auto;">
        <div class="bg-brand-soft px-6 py-4 border-b border-brand/10 flex justify-between items-center sticky top-0 z-10">
            <h4 class="font-bold text-brand text-lg">Update Student</h4>
            <button onclick="closeModal('updateStudentModal')" class="text-slate-400 hover:text-brand hover:bg-white w-8 h-8 rounded-full transition flex items-center justify-center">
                <span class="iconify text-xl" data-icon="mdi:close"></span>
            </button>
        </div>
        <form method="POST" id="updateStudentForm" onsubmit="return validateUpdatePasswords()">
            @csrf
            @method('PUT')
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Student ID</label>
                    <input id="updateStudentId" type="text" readonly class="w-full h-10 px-3 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Last Name</label>
                    <input id="updateLastName" type="text" readonly class="w-full h-10 px-3 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">First Name</label>
                    <input id="updateFirstName" type="text" readonly class="w-full h-10 px-3 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Middle Name</label>
                    <input id="updateMiddleName" type="text" readonly class="w-full h-10 px-3 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Email</label>
                    <input id="updateEmail" type="text" readonly class="w-full h-10 px-3 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Phone Number</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-600 text-xs font-medium">+63</span>
                        <input id="updatePhoneNumber" name="phone_number" type="text" placeholder="912 345 6789" class="w-full h-10 pl-12 pr-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">New Password (Leave blank to keep current)</label>
                    <div class="relative">
                        <input id="updatePassword" name="password" type="password" placeholder="Min. 8 characters" oninput="validateUpdatePasswords()" class="w-full h-10 px-3 pr-10 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <span class="iconify text-lg" data-icon="mdi:eye-off-outline"></span>
                        </button>
                    </div>
                    <p id="updatePasswordHelp" class="mt-1 text-[11px] text-red-500 hidden">Password must be at least 8 characters.</p>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Confirm Password</label>
                    <div class="relative">
                        <input id="updatePasswordConfirm" name="password_confirmation" type="password" placeholder="Re-enter password" oninput="validateUpdatePasswords()" class="w-full h-10 px-3 pr-10 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <span class="iconify text-lg" data-icon="mdi:eye-off-outline"></span>
                        </button>
                    </div>
                    <p id="updatePasswordMatch" class="mt-1 text-[11px] text-red-500 hidden">Passwords do not match.</p>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Status</label>
                    <select id="updateStatus" name="status" required class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition appearance-none">
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
            </div>
            <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 sticky bottom-0">
                <button type="button" onclick="closeModal('updateStudentModal')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-200 transition font-semibold text-sm">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-brand text-white rounded-xl font-bold text-sm hover:scale-105 transition shadow-md shadow-brand/20">Update Student</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
    const updateBaseUrl = "{{ url('/faculty/students') }}";
    const csrfToken = "{{ csrf_token() }}";

    // Global modal functions
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const successAlert = document.getElementById('successAlert');
        if (successAlert) {
            setTimeout(() => {
                successAlert.classList.add('hidden');
            }, 5000);
        }

        @if (session('success'))
        const successMessage = @json(session('success'));
        Swal.fire({
            icon: 'success',
            title: 'Student Created',
            html: `<p class="text-sm text-slate-500">${successMessage}</p>`,
            timer: 2500,
            showConfirmButton: false,
            backdrop: 'rgba(15, 23, 42, 0.35)',
            iconColor: '#DB2777',
            customClass: {
                popup: 'rounded-2xl p-6 bg-white shadow-2xl',
                title: 'text-lg font-bold text-slate-800',
                htmlContainer: 'mt-1',
            },
            buttonsStyling: false,
        });
        @endif

        @if ($errors->any())
        const errorMessage = @json($errors->first());
        Swal.fire({
            icon: 'error',
            title: 'Action Failed',
            html: `<p class="text-sm text-slate-500">${errorMessage}</p>`,
            confirmButtonText: 'Okay',
            backdrop: 'rgba(15, 23, 42, 0.35)',
            iconColor: '#EF4444',
            customClass: {
                popup: 'rounded-2xl p-6 bg-white shadow-2xl',
                title: 'text-lg font-bold text-slate-800',
                htmlContainer: 'mt-1',
                confirmButton: 'mt-4 bg-brand text-white px-4 py-2 rounded-lg font-semibold hover:bg-brand-dark',
            },
            buttonsStyling: false,
        });
        @endif
    });

    // ===================== BULK UPLOAD LOGIC =====================
    let bulkSelectedFile = null;
    const bulkExcelUrl = "{{ route('faculty.students.bulk') }}";

    function closeBulkModal() {
        closeModal('bulkUploadModal');
        setTimeout(bulkResetToStep1, 300);
    }

    function bulkResetToStep1() {
        bulkSelectedFile = null;
        document.getElementById('bulkExcelInput').value = '';
        document.getElementById('bulkFileInfo').classList.add('hidden');
        document.getElementById('bulkFileInfo').textContent = '';
        document.getElementById('bulkPreviewBody').innerHTML = '';
        document.getElementById('bulkResultsBody').innerHTML = '';
        document.getElementById('bulkResultsTableWrap').classList.add('hidden');
        document.getElementById('bulkImportBtn').classList.add('hidden');
        document.getElementById('bulkImportAgainBtn').classList.add('hidden');
        bulkSetStep(1);
    }

    function bulkSetStep(n) {
        document.getElementById('bulkStep1').classList.toggle('hidden', n !== 1);
        document.getElementById('bulkStep2').classList.toggle('hidden', n !== 2);
        document.getElementById('bulkStep3').classList.toggle('hidden', n !== 3);
        [1,2,3].forEach(i => {
            const badge = document.getElementById(`stepBadge${i}`);
            const circle = badge.querySelector('span');
            if (i <= n) {
                badge.classList.remove('text-slate-400');
                badge.classList.add('text-emerald-700');
                circle.classList.remove('bg-slate-200','text-slate-500');
                circle.classList.add('bg-emerald-600','text-white');
            } else {
                badge.classList.add('text-slate-400');
                badge.classList.remove('text-emerald-700');
                circle.classList.add('bg-slate-200','text-slate-500');
                circle.classList.remove('bg-emerald-600','text-white');
            }
        });
    }

    function bulkDragOver(e) {
        e.preventDefault();
        document.getElementById('bulkDropZone').classList.add('drag-over');
    }
    function bulkDragLeave(e) {
        document.getElementById('bulkDropZone').classList.remove('drag-over');
    }
    function bulkDrop(e) {
        e.preventDefault();
        document.getElementById('bulkDropZone').classList.remove('drag-over');
        const f = e.dataTransfer.files[0];
        if (f) bulkFileSelected(f);
    }

    function bulkFileSelected(file) {
        if (!file) return;
        const nameLower = file.name.toLowerCase();
        if (!nameLower.endsWith('.xlsx') && !nameLower.endsWith('.xls') && !nameLower.endsWith('.ods') && !nameLower.endsWith('.csv')) {
            Swal.fire({ icon:'error', title:'Invalid file', text:'Please upload an Excel or CSV file.', timer:2500, showConfirmButton:false,
                iconColor:'#EF4444', customClass:{popup:'rounded-2xl p-6 bg-white shadow-2xl', title:'text-lg font-bold text-slate-800'}, buttonsStyling:false });
            return;
        }
        bulkSelectedFile = file;
        const info = document.getElementById('bulkFileInfo');
        info.textContent = `📄 ${file.name}  (${(file.size/1024).toFixed(1)} KB)`;
        info.classList.remove('hidden');
        bulkParsePreview(file);
    }

    function bulkParsePreview(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, {type: 'array'});
                const firstSheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[firstSheetName];

                const allRows = XLSX.utils.sheet_to_json(worksheet, {header: 1, defval: ""});
                if (allRows.length < 2) {
                    Swal.fire({icon:'warning', title:'Empty File', text:'The file has no data rows.', timer:2500, showConfirmButton:false,
                        iconColor:'#F59E0B', customClass:{popup:'rounded-2xl p-6 bg-white shadow-2xl', title:'text-lg font-bold text-slate-800'}, buttonsStyling:false });
                    return;
                }

                const header = allRows[0].map(h => String(h || '').trim().toLowerCase());
                const required = ['student_id','first_name','last_name','email'];
                const missing = required.filter(r => !header.includes(r));
                if (missing.length) {
                    Swal.fire({icon:'error', title:'Missing Columns', html:`<p class="text-sm text-slate-500">Missing: <b>${missing.join(', ')}</b></p>`,
                        confirmButtonText:'Okay', iconColor:'#EF4444',
                        customClass:{popup:'rounded-2xl p-6 bg-white shadow-2xl', title:'text-lg font-bold text-slate-800',
                            confirmButton:'mt-4 bg-brand text-white px-4 py-2 rounded-lg font-semibold'}, buttonsStyling:false });
                    return;
                }

                const dataRows = allRows.slice(1);
                const nonEmpRows = dataRows.filter(row => {
                    return row.some(cell => String(cell || '').trim() !== '');
                });

                if (nonEmpRows.length === 0) {
                    Swal.fire({icon:'warning', title:'Empty File', text:'The file has no data rows.', timer:2500, showConfirmButton:false,
                        iconColor:'#F59E0B', customClass:{popup:'rounded-2xl p-6 bg-white shadow-2xl', title:'text-lg font-bold text-slate-800'}, buttonsStyling:false });
                    return;
                }

                const preview  = nonEmpRows.slice(0, 10);
                const tbody = document.getElementById('bulkPreviewBody');
                tbody.innerHTML = '';
                preview.forEach((row, i) => {
                    const rowObj  = {};
                    header.forEach((h, idx) => rowObj[h] = String(row[idx] ?? '').trim());
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50';
                    tr.innerHTML = `
                        <td class="px-3 py-2 text-slate-400">${i+2}</td>
                        <td class="px-3 py-2 font-mono text-slate-700">${rowObj.student_id||'—'}</td>
                        <td class="px-3 py-2">${rowObj.last_name||'—'}</td>
                        <td class="px-3 py-2">${rowObj.first_name||'—'}</td>
                        <td class="px-3 py-2 text-slate-500">${rowObj.email||'—'}</td>
                        <td class="px-3 py-2 text-slate-500">${rowObj.phone_number||'—'}</td>
                    `;
                    tbody.appendChild(tr);
                });

                document.getElementById('bulkPreviewCount').textContent = `(${nonEmpRows.length} student${nonEmpRows.length!==1?'s':''})`;
                document.getElementById('bulkImportBtn').classList.remove('hidden');
                bulkSetStep(2);
            } catch (err) {
                console.error(err);
                Swal.fire({icon:'error', title:'Parse Error', text:'Could not read file as Excel.', timer:2500, showConfirmButton:false,
                    iconColor:'#EF4444', customClass:{popup:'rounded-2xl p-6 bg-white shadow-2xl', title:'text-lg font-bold text-slate-800'}, buttonsStyling:false });
            }
        };
        reader.readAsArrayBuffer(file);
    }

    async function startBulkImport() {
        if (!bulkSelectedFile) return;
        const btn = document.getElementById('bulkImportBtn');
        const label = document.getElementById('bulkImportBtnLabel');
        btn.disabled = true;
        label.textContent = 'Importing…';

        const formData = new FormData();
        formData.append('excel_file', bulkSelectedFile);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content
            || '{{ csrf_token() }}');

        try {
            const response = await fetch(bulkExcelUrl, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData,
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Upload failed.');
            }

            document.getElementById('bulkResultCreated').textContent = data.created;
            document.getElementById('bulkResultFailed').textContent  = data.failed;

            if (data.results && data.results.length) {
                const rb = document.getElementById('bulkResultsBody');
                rb.innerHTML = '';
                data.results.forEach(r => {
                    const ok = r.status === 'success';
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="px-3 py-2 text-slate-400">${r.row}</td>
                        <td class="px-3 py-2 font-semibold text-slate-700">${r.name}</td>
                        <td class="px-3 py-2">
                            ${ok
                                ? '<span class="inline-flex items-center gap-1 text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded-full text-[10px] font-bold">✓ Success</span>'
                                : '<span class="inline-flex items-center gap-1 text-red-600 bg-red-50 border border-red-200 px-2 py-0.5 rounded-full text-[10px] font-bold">✗ Failed</span>'}
                        </td>
                        <td class="px-3 py-2 text-slate-400">${ok ? (r.email||'') : (r.reason||'')}</td>
                    `;
                    rb.appendChild(tr);
                });
                document.getElementById('bulkResultsTableWrap').classList.remove('hidden');
            }

            btn.classList.add('hidden');
            document.getElementById('bulkImportAgainBtn').classList.remove('hidden');
            bulkSetStep(3);

            if (data.created > 0) {
                setTimeout(() => window.location.reload(), 800);
            }

        } catch (err) {
            btn.disabled = false;
            label.textContent = 'Import Now';
            Swal.fire({icon:'error', title:'Import Failed',
                html:`<p class="text-sm text-slate-500">${err.message}</p>`,
                confirmButtonText:'Okay', iconColor:'#EF4444',
                customClass:{popup:'rounded-2xl p-6 bg-white shadow-2xl', title:'text-lg font-bold text-slate-800',
                    confirmButton:'mt-4 bg-brand text-white px-4 py-2 rounded-lg font-semibold hover:bg-brand-dark'},
                buttonsStyling:false});
        }
    }

    function downloadExcelTemplate() {
        const headers = ["student_id", "first_name", "last_name", "middle_name", "email", "phone_number"];
        const data = [
            ["2024-001", "Juan", "Dela Cruz", "M.", "jdelacruz", "09171234567"],
            ["2024-002", "Maria", "Santos", "", "msantos@hms.edu", ""]
        ];

        const ws = XLSX.utils.aoa_to_sheet([headers, ...data]);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Students");

        XLSX.writeFile(wb, "students_template.xlsx");
    }

    function togglePassword(btn) {
        const input = btn.parentElement.querySelector('input');
        const icon = btn.querySelector('span');
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        icon.setAttribute('data-icon', isHidden ? 'mdi:eye-outline' : 'mdi:eye-off-outline');
    }

    function validateStudentPasswords() {
        const password = document.getElementById('createStudentPassword');
        const confirm = document.getElementById('createStudentPasswordConfirm');
        const lengthHelp = document.getElementById('createStudentPasswordHelp');
        const matchHelp = document.getElementById('createStudentPasswordMatch');

        if (!password || !confirm) {
            return true;
        }

        const tooShort = password.value.length > 0 && password.value.length < 8;
        const mismatch = confirm.value.length > 0 && password.value !== confirm.value;

        lengthHelp.classList.toggle('hidden', !tooShort);
        matchHelp.classList.toggle('hidden', !mismatch);

        return !(tooShort || mismatch);
    }

    function validateUpdatePasswords() {
        const password = document.getElementById('updatePassword');
        const confirm = document.getElementById('updatePasswordConfirm');
        const lengthHelp = document.getElementById('updatePasswordHelp');
        const matchHelp = document.getElementById('updatePasswordMatch');

        if (!password || !confirm) {
            return true;
        }

        const tooShort = password.value.length > 0 && password.value.length < 8;
        const mismatch = confirm.value.length > 0 && password.value !== confirm.value;

        lengthHelp.classList.toggle('hidden', !tooShort);
        matchHelp.classList.toggle('hidden', !mismatch);

        return !(tooShort || mismatch);
    }

    function openUpdateModal(button) {
        const form = document.getElementById('updateStudentForm');
        const userId = button.getAttribute('data-user-id');
        const studentId = button.getAttribute('data-student-id');
        const firstName = button.getAttribute('data-first-name') || '';
        const middleName = button.getAttribute('data-middle-name') || '';
        const lastName = button.getAttribute('data-last-name') || '';
        const email = button.getAttribute('data-email') || '';
        const phone = button.getAttribute('data-phone-number') || '';
        const status = button.getAttribute('data-status') || 'active';

        form.action = `${updateBaseUrl}/${userId}`;
        document.getElementById('updateStudentId').value = studentId;
        document.getElementById('updateLastName').value = lastName;
        document.getElementById('updateFirstName').value = firstName;
        document.getElementById('updateMiddleName').value = middleName;
        document.getElementById('updateEmail').value = email;
        document.getElementById('updatePhoneNumber').value = phone.replace('+63', '');
        document.getElementById('updateStatus').value = status;
        document.getElementById('updatePassword').value = '';
        document.getElementById('updatePasswordConfirm').value = '';
        document.getElementById('updatePasswordHelp').classList.add('hidden');
        document.getElementById('updatePasswordMatch').classList.add('hidden');
        openModal('updateStudentModal');
    }

    @if ($errors->any())
        openModal('createStudentModal');
    @endif
</script>
@endpush
@endsection