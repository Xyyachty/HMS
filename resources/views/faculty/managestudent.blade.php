@extends('faculty.layout.app')

@section('page_title', 'List of Students Information')
@section('students_active', 'active')

@push('styles')
<style>
    /* ── Bulk upload, step 1 ──
       Neutral cards on the maroon palette. Written out because the frozen build
       has no grid breakpoints, and the palette file turns blue and amber into the
       wine and gold that made this read as a warning. */
    .bu-lead { font-size: 12.5px; color: #6B4A54; margin-bottom: .75rem; }
    .bu-grid { display: grid; gap: .875rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    @media (max-width: 700px) { .bu-grid { grid-template-columns: minmax(0, 1fr); } }
    .bu-card { border: 1px solid #EADAD5; border-radius: 1rem; background: #fff; padding: 1rem; display: flex; flex-direction: column; }
    .bu-card-head { display: flex; align-items: center; gap: .7rem; margin-bottom: .85rem; }
    .bu-badge {
        width: 2.25rem; height: 2.25rem; border-radius: .7rem; flex: 0 0 auto;
        display: inline-flex; align-items: center; justify-content: center;
        background: #FBEEE9; color: #7B1730; font-size: 1.15rem;
    }
    .bu-card-title { font-size: 13.5px; font-weight: 800; color: #2A1118; line-height: 1.25; }
    .bu-card-sub { font-size: 11.5px; color: #8A6F76; margin-top: .1rem; }
    .bu-label { font-size: 10px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #8A6F76; margin: .25rem 0 .35rem; }
    .bu-chips { display: flex; flex-wrap: wrap; gap: .35rem; margin-bottom: .5rem; }
    .bu-chip {
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 11px; font-weight: 700;
        color: #47262D; background: #F7EEEB; border: 1px solid #EADAD5; border-radius: .4rem; padding: .1rem .4rem;
    }
    .bu-chip.is-soft { color: #8A6F76; background: #fff; border-style: dashed; }
    .bu-text { font-size: 11.5px; line-height: 1.55; color: #6B4A54; margin-top: .25rem; }
    .bu-download {
        margin-top: auto; align-self: flex-start;
        display: inline-flex; align-items: center; gap: .4rem; padding: .5rem .85rem; border-radius: .7rem;
        font-size: 12px; font-weight: 800; color: #7B1730; background: #fff; border: 1px solid #D9B86A;
        transition: all .15s ease;
    }
    .bu-download:hover { background: #7B1730; border-color: #C9A45C; color: #fff; }
    .bu-notes { margin-top: .875rem; display: grid; gap: .45rem; }
    .bu-notes li { display: flex; align-items: flex-start; gap: .5rem; font-size: 11.5px; line-height: 1.5; color: #6B4A54; }
    .bu-notes li .iconify { flex: 0 0 auto; font-size: .95rem; color: #B8873C; margin-top: .1rem; }
    .bu-drop {
        margin-top: 1rem; border: 2px dashed #DEC6BF; border-radius: 1.1rem; background: #FDF8F6;
        padding: 2rem 1.25rem; display: flex; flex-direction: column; align-items: center; gap: .4rem;
        text-align: center; cursor: pointer; transition: border-color .2s ease, background-color .2s ease;
    }
    .bu-drop:hover { border-color: #C9A45C; background: #FBEEE9; }
    .bu-drop-icon {
        width: 3.25rem; height: 3.25rem; border-radius: 9999px; margin-bottom: .25rem;
        display: inline-flex; align-items: center; justify-content: center;
        background: #fff; color: #7B1730; font-size: 1.6rem; box-shadow: 0 6px 16px -8px rgba(123,23,48,.45);
    }
    .bu-drop-title { font-size: 13.5px; font-weight: 800; color: #2A1118; }
    .bu-drop-sub { font-size: 11.5px; color: #8A6F76; }
    .bu-browse {
        margin-top: .35rem; display: inline-flex; align-items: center; padding: .45rem .95rem; border-radius: .7rem;
        font-size: 12px; font-weight: 800; color: #fff; background: #7B1730;
    }
    #bulkDropZone.drag-over { border-color: #C9A45C; background-color: #FBEEE9; }
    #bulkImportBtn:disabled {
        opacity:.6;
        cursor:not-allowed;
        transform:none;
    }

    /* ── Student table ──
       Written out rather than composed from utilities: public/css/app.css is a
       frozen build, and faculty-palette.css turns green and red into gold and
       wine, so the Active and Inactive chips carry their own hex values. */
    .st-card { border: 1px solid #EADAD5; border-radius: 1rem; overflow: hidden; background: #fff; }
    #studentsTable { table-layout: fixed; width: 100%; border-collapse: collapse; }
    #studentsTable th, #studentsTable td { vertical-align: middle; }
    #studentsTable thead th {
        background: #7B1730; color: #FBEEE9;
        font-size: 10.5px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase;
        padding: .9rem .9rem; text-align: left; white-space: nowrap;
    }
    #studentsTable thead th.text-center { text-align: center; }
    #studentsTable tbody tr { border-top: 1px solid #F2E9E7; transition: background-color .15s ease, box-shadow .15s ease; }
    #studentsTable tbody tr:nth-child(even) { background: #FDF8F6; }
    #studentsTable tbody tr[data-student-id]:hover { background: #dadada; }
    #studentsTable td { padding: .8rem .9rem; }
    #studentsTable .col-student { width: 31%; }
    #studentsTable .col-id { width: 13%; }
    #studentsTable .col-phone { width: 15%; }
    #studentsTable .col-status { width: 12%; }
    #studentsTable .col-joined { width: 14%; }
    #studentsTable .col-action { width: 15%; }
    #studentsTable .cell-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%; display: block; }

    .st-id {
        display: inline-block; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 11.5px; font-weight: 700;
        color: #7B1730;
    }
    .st-name { font-size: 13.5px; font-weight: 800; color: #2A1118; }
    .st-email { font-size: 11.5px; color: #8A6F76; margin-top: .1rem; }
    .st-muted { font-size: 12px; color: #5A3941; font-weight: 600; }
    .st-dim { color: #C9AFAA; }

    .st-status {
        display: inline-flex; align-items: center; gap: .4rem; white-space: nowrap;
        font-size: 11px; font-weight: 800;
    }
    .st-status .dot { width: .45rem; height: .45rem; border-radius: 9999px; }
    .st-status.is-active   { color: #15803D; }
    .st-status.is-active .dot   { background: #16A34A; box-shadow: 0 0 0 3px rgba(22,163,74,.18); }
    .st-status.is-inactive { color: #B42318; }
    .st-status.is-inactive .dot { background: #DC2626; box-shadow: 0 0 0 3px rgba(220,38,38,.18); }
    .st-status.is-pending  { color: #96692C; }
    .st-status.is-pending .dot  { background: #C9A45C; }

    .st-update {
        display: inline-flex; align-items: center; gap: .35rem; white-space: nowrap;
        border-radius: .6rem; padding: .4rem .8rem; font-size: 11.5px; font-weight: 800;
        color: #111; background: #fff; border: 1px solid #111; transition: all .15s ease;
    }
    .st-update:hover,
    #studentsTable tbody tr[data-student-id]:hover .st-update { background: #111; border-color: #111; color: #fff; }

    .st-pager { display: flex; align-items: center; gap: .375rem; flex-wrap: wrap; }
    .st-page {
        min-width: 2.35rem; height: 2.35rem; padding: 0 .8rem; border-radius: .7rem;
        display: inline-flex; align-items: center; justify-content: center; gap: .25rem;
        font-size: 13px; font-weight: 700; color: #5A3941; background: #fff; border: 1px solid #EADAD5;
        transition: all .15s ease;
    }
    a.st-page:hover { color: #7B1730; border-color: #C9A45C; background: #FBEEE9; }
    .st-page.is-current { background: #7B1730; border-color: #C9A45C; color: #fff; box-shadow: 0 6px 14px -6px rgba(123,23,48,.55); }
    .st-page.is-disabled { color: #C9AFAA; background: #FAF6F5; cursor: not-allowed; }

    /* Expandable student search — icon-only until opened */
    #studentSearchWrap {
        display: inline-flex;
        align-items: center;
        height: 2.5rem;
        width: 2.5rem;
        border-radius: 0.75rem;
        background: #F2E9E7;
        border: 1px solid transparent;
        overflow: hidden;
        transition: width 0.25s ease, background-color 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    #studentSearchWrap.is-open {
        width: 12.5rem;
        background: #fff;
        border-color: #D9B86A;
        box-shadow: 0 0 0 3px rgba(123,23,48, 0.12);
    }
    #studentSearchWrap .search-toggle {
        width: 2.5rem;
        height: 2.5rem;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #6B4A54;
        border: 0;
        background: transparent;
        cursor: pointer;
        border-radius: 0.75rem;
    }
    #studentSearchWrap.is-open .search-toggle,
    #studentSearchWrap .search-toggle:hover {
        color: #7B1730;
    }
    #studentSearchInput {
        width: 0;
        min-width: 0;
        opacity: 0;
        border: 0;
        outline: none;
        background: transparent;
        font-size: 0.875rem;
        color: #47262D;
        padding: 0;
        transition: width 0.25s ease, opacity 0.2s ease, padding 0.25s ease;
    }
    #studentSearchWrap.is-open #studentSearchInput {
        width: 100%;
        opacity: 1;
        padding-right: 0.75rem;
    }
    #studentSearchWrap.is-open #studentSearchInput::placeholder {
        color: #7A6068;
    }

    /* Bulk upload preview. Written out rather than composed from utilities because
       public/css/app.css is a frozen build — max-h-80, hover:border-brand and the
       disabled: variants this needs are not in it and would silently do nothing. */
    .bulk-preview-scroll { max-height: 20rem; }
    .bulk-pager-btn {
        display: inline-flex; align-items: center; justify-content: center;
        padding: 0.25rem 0.5rem; border-radius: 0.5rem;
        border: 1px solid #E4D3CF; background: #fff; color: #6B4A54;
        transition: color 0.2s, border-color 0.2s, opacity 0.2s;
    }
    .bulk-pager-btn:hover:not(:disabled) { color: #7B1730; border-color: #C9A45C; }
    .bulk-pager-btn:disabled { opacity: 0.4; cursor: default; }

    /* Add Student / Bulk Upload with no block assigned. Same reason as above: the
       frozen build has no disabled: variants to compose this from. */
    .intake-disabled { opacity: 0.45; cursor: not-allowed; box-shadow: none; }
</style>
@endpush

@section('content')
<div class="rounded-2xl shadow-sm border border-slate-100 overflow-hidden" style="background: #dadada;">

    <!-- Block tabs + toolbar: [Search icon] [Bulk] [Add Student] -->
    <div class="px-4 md:px-6 pt-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2 min-w-0">
            @forelse(($classes ?? collect()) as $classTab)
                @php
                    $taken = $classTab->seats_taken ?? $classTab->students()->count();
                    $cap = $classTab->capacity ?? $classCapacity;
                    $isActive = $activeClass && $activeClass->faculty_class_id === $classTab->faculty_class_id;
                    $isClosed = $classTab->status === 'closed';
                @endphp
                <a href="{{ route('faculty.students', ['class' => $classTab->letter]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl text-sm font-bold border border-b-0 transition
                   {{ $isActive
                        ? 'bg-white text-brand border-slate-200 -mb-px relative z-10'
                        : 'bg-slate-50 text-slate-500 border-transparent hover:text-slate-700 hover:bg-slate-100' }}">
                    <span>{{ $classTab->name }}</span>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full
                        {{ $isClosed ? 'bg-slate-200 text-slate-600' : 'bg-brand-soft text-brand' }}">
                        {{ $taken }}/{{ $cap }}
                    </span>
                    @if(!$isClosed)
                        <span class="text-[10px] uppercase tracking-wide text-emerald-600">Open</span>
                    @endif
                </a>
            @empty
                <p class="text-sm text-slate-500 pb-1">
                    Blocks hold up to {{ $classCapacity ?? 40 }} students.
                </p>
            @endforelse
        </div>

        <div class="flex items-center gap-2 pb-1 sm:pb-3 shrink-0 ml-auto">
            <div id="studentSearchWrap" class="shrink-0 {{ ($search ?? '') !== '' ? 'is-open' : '' }}">
                <button
                    type="button"
                    class="search-toggle"
                    title="Search students"
                    aria-label="Search students"
                    onclick="toggleStudentSearch()"
                >
                    <span class="iconify text-lg" data-icon="mdi:magnify"></span>
                </button>
                <input
                    id="studentSearchInput"
                    type="text"
                    placeholder="Search students..."
                    value="{{ $search ?? '' }}"
                    oninput="filterStudentsTable(this.value)"
                    onkeydown="if (event.key === 'Escape') collapseStudentSearch(true)"
                >
            </div>
            @php
                // No block, no class to enrol into — see FacultyController::students().
                $canEnrol = $hasBlock ?? true;
                $noBlockHint = 'No block assigned to your account yet. Ask the dean to assign one.';
            @endphp
            <button
                type="button"
                @if ($canEnrol) onclick="openModal('bulkUploadModal')" @else disabled @endif
                title="{{ $canEnrol ? 'Bulk Upload' : $noBlockHint }}"
                aria-label="Bulk Upload"
                class="h-10 shrink-0 bg-emerald-600 text-white px-4 rounded-xl text-sm font-bold transition shadow-md shadow-emerald-600/20 inline-flex items-center gap-2 whitespace-nowrap {{ $canEnrol ? 'hover:bg-emerald-700' : 'intake-disabled' }}"
            >
                <span class="iconify text-base" data-icon="mdi:upload"></span>
                Bulk Upload
            </button>
            <button
                type="button"
                @if ($canEnrol) onclick="openModal('createStudentModal')" @else disabled @endif
                title="{{ $canEnrol ? 'Add Student' : $noBlockHint }}"
                class="h-10 bg-brand text-white px-4 rounded-xl text-sm font-bold transition shadow-md shadow-brand/20 inline-flex items-center gap-2 whitespace-nowrap {{ $canEnrol ? 'hover:opacity-95' : 'intake-disabled' }}"
            >
                <span class="iconify text-base" data-icon="mdi:account-plus-outline"></span>
                Add Student
            </button>
        </div>
    </div>

    @unless ($hasBlock ?? true)
        <div class="mx-6 mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 flex items-start gap-2">
            <span class="iconify text-base shrink-0" data-icon="mdi:alert-outline"></span>
            <span>
                <b>No block assigned to your account.</b>
                Students are enrolled into your block, so Add Student and Bulk Upload stay
                switched off until the dean assigns one.
            </span>
        </div>
    @endunless

    @if (session('success'))
        <div id="successAlert" class="mx-6 mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <!-- DataTable -->
    <div class="p-4 md:p-6">
        <div class="st-card overflow-x-auto">
            <table id="studentsTable" style="min-width: 760px">
                <thead>
                    <tr>
                        <th class="col-student">Student</th>
                        <th class="col-id">ID Number</th>
                        <th class="col-phone">Phone</th>
                        <th class="col-status">Status</th>
                        <th class="col-joined">Joined</th>
                        <th class="col-action text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($students ?? [] as $student)
                    @php
                        $user = $student->user;

                        $lastName = $user->last_name ?? '';
                        $firstName = $user->first_name ?? '';
                        $middleName = $user->middle_name ?? '';
                        $middleInitial = $middleName !== '' ? mb_substr($middleName, 0, 1) . '.' : '';

                        $primaryName = trim($lastName) !== ''
                            ? $lastName
                            : ($user->name ?? 'Student');
                        $secondaryName = trim(implode(' ', array_filter([$firstName, $middleInitial])));

                        $displayName = trim(implode(' ', array_filter([
                            $lastName ?: null,
                            $firstName ?: null,
                            $middleName ?: null,
                        ])));
                        $displayName = $displayName !== '' ? $displayName : ($user->name ?? 'Student');

                        $status = $user->status ?? 'active';
                        $statusChip = match ($status) {
                            'active'  => ['class' => 'is-active', 'label' => 'Active'],
                            'pending' => ['class' => 'is-pending', 'label' => 'Pending'],
                            default   => ['class' => 'is-inactive', 'label' => 'Inactive'],
                        };
                    @endphp

                    <tr
                        data-student-id="{{ $student->student_number }}"
                        data-user-id="{{ $user->user_id ?? '' }}"
                    >
                        <td>
                            <div class="flex items-center gap-3 min-w-0">
                                @include('partials.user-avatar', [
                                    'user'         => $user,
                                    'name'         => $displayName,
                                    'size'         => 'w-9 h-9',
                                    'rounded'      => 'rounded-full',
                                    'extraClasses' => 'bg-brand-soft text-brand text-[11px] font-bold border border-amber-200',
                                ])
                                <div class="min-w-0 flex-1">
                                    <p class="st-name cell-truncate" title="{{ $displayName }}">
                                        {{ $primaryName }}@if($secondaryName !== ''), {{ $secondaryName }}@endif
                                    </p>
                                    <p class="st-email cell-truncate" title="{{ $user->email ?? '' }}">{{ $user->email ?? '—' }}</p>
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="st-id" title="{{ $student->student_number }}">{{ $student->student_number }}</span>
                        </td>

                        <td>
                            @if ($user?->phone_display)
                                <span class="st-muted cell-truncate" title="{{ $user->phone_display }}">{{ $user->phone_display }}</span>
                            @else
                                <span class="st-muted st-dim">—</span>
                            @endif
                        </td>

                        <td>
                            <span class="st-status {{ $statusChip['class'] }}">
                                <span class="dot"></span>
                                {{ $statusChip['label'] }}
                            </span>
                        </td>

                        <td>
                            <span class="st-muted inline-flex items-center gap-1.5 whitespace-nowrap">
                                <span class="iconify text-sm text-amber-500" data-icon="mdi:calendar-blank-outline"></span>
                                {{ optional($user->created_at)->format('M d, Y') }}
                            </span>
                        </td>

                        <td>
                            <div class="flex justify-center">
                                <button
                                    type="button"
                                    onclick="openUpdateModal(this)"
                                    data-user-id="{{ $user->user_id ?? '' }}"
                                    data-student-id="{{ $student->student_number }}"
                                    data-first-name="{{ $user->first_name ?? '' }}"
                                    data-middle-name="{{ $user->middle_name ?? '' }}"
                                    data-last-name="{{ $user->last_name ?? '' }}"
                                    data-email="{{ $user->email ?? '' }}"
                                    data-phone-number="{{ $user->phone_number ?? '' }}"
                                    data-status="{{ $status }}"
                                    class="st-update"
                                >
                                    <span class="iconify text-sm" data-icon="mdi:pencil-outline"></span>
                                    Update
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-14 h-14 rounded-2xl bg-brand-soft flex items-center justify-center">
                                    <span class="iconify text-3xl text-brand" data-icon="mdi:account-group-outline"></span>
                                </div>
                                @if(($search ?? '') !== '')
                                    <p class="font-semibold text-slate-600">
                                        No students in {{ $activeClass->name ?? 'this block' }} match "{{ $search }}"
                                    </p>
                                    <p class="text-xs text-slate-400">Try a student number, name, email or phone number.</p>
                                @else
                                    <p class="font-semibold text-slate-600">
                                        No students in {{ $activeClass->name ?? 'this block' }} yet
                                    </p>
                                    <p class="text-xs text-slate-400">
                                        Use Add Student or Bulk Upload — seats fill the open class (max {{ $classCapacity ?? 40 }}).
                                    </p>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($students->total() > 0)
        <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
            <p class="text-[13px] text-slate-500">
                Showing <span class="font-bold text-slate-800">{{ $students->firstItem() }}</span>–<span class="font-bold text-slate-800">{{ $students->lastItem() }}</span>
                of <span class="font-bold text-slate-800">{{ $students->total() }}</span> students
            </p>
            @if($students->hasPages())
            <nav class="st-pager" aria-label="Student pages">
                @if ($students->onFirstPage())
                    <span class="st-page is-disabled"><span class="iconify" data-icon="mdi:chevron-left"></span> Previous</span>
                @else
                    <a href="{{ $students->previousPageUrl() }}" class="st-page"><span class="iconify" data-icon="mdi:chevron-left"></span> Previous</a>
                @endif

                @foreach ($students->getUrlRange(1, $students->lastPage()) as $page => $url)
                    @if ($page == $students->currentPage())
                        <span class="st-page is-current" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="st-page">{{ $page }}</a>
                    @endif
                @endforeach

                @if ($students->hasMorePages())
                    <a href="{{ $students->nextPageUrl() }}" class="st-page">Next <span class="iconify" data-icon="mdi:chevron-right"></span></a>
                @else
                    <span class="st-page is-disabled">Next <span class="iconify" data-icon="mdi:chevron-right"></span></span>
                @endif
            </nav>
            @endif
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
                    <p class="text-xs text-emerald-600 mt-0.5">Upload the official block list, or the template, to add many students at once</p>
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

            <!-- Step 1: Drop zone + the two file formats it reads -->
            <div id="bulkStep1">
                <p class="bu-lead">Choose either format — both import the same way.</p>

                <div class="bu-grid">
                    <!-- Registrar's list -->
                    <div class="bu-card">
                        <div class="bu-card-head">
                            <span class="bu-badge"><span class="iconify" data-icon="mdi:file-document-outline"></span></span>
                            <div class="min-w-0">
                                <p class="bu-card-title">Registrar's block list</p>
                                <p class="bu-card-sub">Upload it as-is, no editing needed</p>
                            </div>
                        </div>
                        <p class="bu-label">Columns read</p>
                        <div class="bu-chips">
                            <code class="bu-chip">STUD NO.</code>
                            <code class="bu-chip">NAME</code>
                            <code class="bu-chip">EMAIL</code>
                            <code class="bu-chip">CONTACT #</code>
                        </div>
                        <p class="bu-text">
                            The letterhead, the <em>Female</em> / <em>Male</em> dividers and every other column are skipped.
                            Names written <code class="bu-chip">LAST, FIRST M.</code> are split for you.
                        </p>
                    </div>

                    <!-- HMS template -->
                    <div class="bu-card">
                        <div class="bu-card-head">
                            <span class="bu-badge"><span class="iconify" data-icon="mdi:table-large"></span></span>
                            <div class="min-w-0">
                                <p class="bu-card-title">HMS template</p>
                                <p class="bu-card-sub">A blank sheet with the right headers</p>
                            </div>
                        </div>
                        <p class="bu-label">Required</p>
                        <div class="bu-chips">
                            <code class="bu-chip">student_id</code>
                            <code class="bu-chip">first_name</code>
                            <code class="bu-chip">last_name</code>
                            <code class="bu-chip">email</code>
                        </div>
                        <p class="bu-label">Optional</p>
                        <div class="bu-chips">
                            <code class="bu-chip is-soft">middle_name</code>
                            <code class="bu-chip is-soft">phone_number</code>
                        </div>
                        <button type="button" onclick="downloadExcelTemplate()" class="bu-download">
                            <span class="iconify text-base" data-icon="mdi:tray-arrow-down"></span>
                            Download template (.xlsx)
                        </button>
                    </div>
                </div>

                {{-- The one thing about the registrar's list that costs a student their
                     account, said before the file is picked because the fix is to the
                     spreadsheet. A plain note rather than an alert: it is how the import
                     works, not something that has gone wrong. --}}
                <ul class="bu-notes">
                    <li>
                        <span class="iconify" data-icon="mdi:email-outline"></span>
                        <span>Every student needs an email address. Rows without one are skipped and listed on the Results step; the rest of the file still imports.</span>
                    </li>
                    <li>
                        <span class="iconify" data-icon="mdi:key-outline"></span>
                        <span>Each student gets their own password, emailed to them with their sign-in details.</span>
                    </li>
                </ul>

                <!-- Drop Zone -->
                <div id="bulkDropZone"
                    class="bu-drop"
                    onclick="document.getElementById('bulkExcelInput').click()"
                    ondragover="bulkDragOver(event)"
                    ondragleave="bulkDragLeave(event)"
                    ondrop="bulkDrop(event)">
                    <span class="bu-drop-icon"><span class="iconify" data-icon="mdi:cloud-upload-outline"></span></span>
                    <p class="bu-drop-title">Drag &amp; drop your Excel file here</p>
                    <p class="bu-drop-sub">.xlsx or .xls, up to 5MB</p>
                    <span class="bu-browse">Browse files</span>
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
                {{-- Shown only when the file actually has rows without an address, and
                     saying how many: a warning that appears every time is one nobody
                     reads by the third block. --}}
                <div id="bulkNoEmailNote" class="hidden bg-amber-50 border border-amber-200 rounded-xl p-3 flex gap-2.5 items-start mb-3">
                    <span class="iconify text-amber-500 text-lg mt-0.5 shrink-0" data-icon="mdi:email-off-outline"></span>
                    <div class="text-xs text-amber-800 leading-relaxed">
                        <p class="font-bold"><span id="bulkNoEmailCount"></span> Students without an email address cannot be approved or have an account created.</p>
                        <span class="block mt-0.5 text-amber-700">They are marked below and will be skipped. Everyone else still imports.</span>
                    </div>
                </div>
                <div class="overflow-x-auto rounded-xl border border-slate-200 bulk-preview-scroll">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 font-bold text-slate-500 uppercase tracking-widest">#</th>
                                <th class="px-3 py-2 font-bold text-slate-500 uppercase tracking-widest">Sheet</th>
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
                <div class="flex items-center justify-between gap-3 mt-2">
                    <p class="text-xs text-slate-400">Every row is imported, not just this page. &ldquo;Sheet&rdquo; is the row number in your Excel file.</p>
                    <div id="bulkPreviewPager" class="hidden items-center gap-1 shrink-0">
                        <button type="button" onclick="bulkPreviewGo(bulkPreviewPage - 1)" id="bulkPreviewPrev"
                            class="bulk-pager-btn"
                            aria-label="Previous page">
                            <span class="iconify text-base" data-icon="mdi:chevron-left"></span>
                        </button>
                        <span id="bulkPreviewPageLabel" class="text-xs text-slate-500 font-semibold px-1 whitespace-nowrap"></span>
                        <button type="button" onclick="bulkPreviewGo(bulkPreviewPage + 1)" id="bulkPreviewNext"
                            class="bulk-pager-btn"
                            aria-label="Next page">
                            <span class="iconify text-base" data-icon="mdi:chevron-right"></span>
                        </button>
                    </div>
                </div>
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
        <div class="bg-brand-soft px-6 py-4 border-b border-amber-100 flex justify-between items-center sticky top-0 z-10">
            <h4 class="font-bold text-brand text-lg">Add Student</h4>
            <button onclick="closeModal('createStudentModal')" class="text-slate-400 hover:text-brand hover:bg-white w-8 h-8 rounded-full transition flex items-center justify-center">
                <span class="iconify text-xl" data-icon="mdi:close"></span>
            </button>
        </div>
        <form method="POST" action="{{ route('faculty.students.store') }}">
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
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Email <span class="text-emerald-600 normal-case tracking-normal font-semibold">(real address — the welcome is sent here)</span></label>
                    <input name="email" type="email" placeholder="e.g. student@gmail.com" required class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                    <p class="mt-1 text-[11px] text-slate-400">Their sign-in details are emailed here as soon as the account is created.</p>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Phone Number</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-600 text-xs font-medium">+63</span>
                        <input name="phone_number" type="text" placeholder="912 345 6789" class="w-full h-10 pl-12 pr-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                    </div>
                </div>
                <div class="md:col-span-2">
                    <div class="flex items-start gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-500">
                        <span class="iconify text-base text-slate-400 shrink-0 mt-0.5" data-icon="mdi:key-outline"></span>
                        <span>A password is generated for this student and emailed to them with their sign-in details. Nobody types it, so no two students share one.</span>
                    </div>
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
        <div class="bg-brand-soft px-6 py-4 border-b border-amber-100 flex justify-between items-center sticky top-0 z-10">
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
                    <input id="updateEmail" name="email" type="email" required placeholder="student@example.com" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                    <p class="mt-1 text-[11px] text-slate-400">Editable so a typo from the Excel import can be corrected. Fixing it re-sends the account email to the new address.</p>
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
                        <option value="inactive">Inactive</option>
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
<script src="{{ asset('js/hms-swal-theme.js') }}?v={{ filemtime(public_path('js/hms-swal-theme.js')) }}"></script>
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
            title: @json(session('success_title', 'Success')),
            html: `<p class="text-sm text-slate-500">${successMessage}</p>`,
            timer: 2500,
            showConfirmButton: false,
            backdrop: 'rgba(42,17,24, 0.35)',
            iconColor: '#C9A45C',
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
            backdrop: 'rgba(42,17,24, 0.35)',
            iconColor: '#9E1B3C',
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
        // Dropped here too, or the next file opens on the previous roster's page.
        bulkPreviewStudents = [];
        bulkPreviewPage = 1;
        document.getElementById('bulkPreviewPager').classList.add('hidden');
        document.getElementById('bulkPreviewPager').classList.remove('flex');
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
                iconColor:'#9E1B3C', customClass:{popup:'rounded-2xl p-6 bg-white shadow-2xl', title:'text-lg font-bold text-slate-800'}, buttonsStyling:false });
            return;
        }
        bulkSelectedFile = file;
        const info = document.getElementById('bulkFileInfo');
        info.textContent = `📄 ${file.name}  (${(file.size/1024).toFixed(1)} KB)`;
        info.classList.remove('hidden');
        bulkParsePreview(file);
    }

    /*
       Reads a class list out of a sheet, the same way App\Support\StudentRosterSheet
       does on the server. Two shapes turn up: the template this app hands out, and the
       registrar's official class list, whose header sits under a letterhead, whose name
       is a single "LAST, FIRST M." cell, and whose students are split into Female and
       Male blocks. Only student number, name, email and contact number are read.
    */
    const ROSTER_HEADINGS = {
        student_number: ['stud no','student no','student number','studno','student id','studentid','student_id','id no','id number'],
        name:           ['name','full name','student name','complete name'],
        first_name:     ['first name','firstname','first_name','given name'],
        middle_name:    ['middle name','middlename','middle_name','middle initial'],
        last_name:      ['last name','lastname','last_name','surname','family name'],
        email:          ['email','email address','e mail','email_address'],
        phone_number:   ['contact','contact no','contact number','contact #','phone','phone no','phone number','phone_number','mobile','mobile no','mobile number','cellphone','cell no'],
    };
    const ROSTER_NOT_A_STUDENT = ['female','male','total','nothing follows'];

    function rosterNormalize(value) {
        return String(value ?? '').toLowerCase().trim()
            .replace(/\*/g, '')
            .replace(/[^\p{L}\p{N}\s]+/gu, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function rosterSplitName(name) {
        const clean = String(name || '').replace(/\s+/g, ' ').trim();
        if (!clean) return { first: '', middle: '', last: '' };

        let last, rest;
        if (clean.includes(',')) {
            const at = clean.indexOf(',');
            last = clean.slice(0, at).trim();
            rest = clean.slice(at + 1).trim();
        } else {
            const parts = clean.split(' ');
            last = parts.length > 1 ? parts.pop() : clean;
            rest = parts.join(' ');
        }

        const parts = rest.split(' ').filter(p => p.trim() !== '' && p.trim() !== '.');
        let middle = '';
        if (parts.length > 1 && /^\p{L}\.?$/u.test(parts[parts.length - 1])) {
            middle = parts.pop();
        }

        return { first: parts.join(' '), middle: middle, last: last };
    }

    function parseRosterRows(allRows) {
        let headerRow = null;
        let columns = {};

        for (let i = 0; i < allRows.length && i <= 40; i++) {
            const row = allRows[i] || [];
            const found = {};
            row.forEach((value, column) => {
                const heading = rosterNormalize(value);
                if (!heading) return;
                for (const field in ROSTER_HEADINGS) {
                    if (found[field] === undefined && ROSTER_HEADINGS[field].includes(heading)) {
                        found[field] = column;
                        break;
                    }
                }
            });

            const hasName = found.name !== undefined
                || (found.first_name !== undefined && found.last_name !== undefined);

            if (found.student_number !== undefined && hasName) {
                headerRow = i;
                columns = found;
                break;
            }
        }

        if (headerRow === null) return { headerRow: null, students: [] };

        const cleanEmail = v => ['n/a','na','none','-'].includes(String(v || '').trim().toLowerCase()) ? '' : String(v || '').trim();
        const cleanPhone = v => {
            const text = String(v || '').trim();
            if (!text) return '';
            return (text.startsWith('+') ? '+' : '') + text.replace(/\D/g, '');
        };

        const students = [];
        for (let i = headerRow + 1; i < allRows.length; i++) {
            const row = allRows[i] || [];
            const cell = f => columns[f] === undefined ? '' : String(row[columns[f]] ?? '').trim();

            // Female / Male dividers and the trailing "NOTHING FOLLOWS" marker.
            if (row.some(v => { const t = rosterNormalize(v); return t && ROSTER_NOT_A_STUDENT.includes(t); })) continue;

            let first, middle, last;
            if (columns.name !== undefined) {
                ({ first, middle, last } = rosterSplitName(cell('name')));
            } else {
                first = cell('first_name'); middle = cell('middle_name'); last = cell('last_name');
            }

            const studentNumber = cell('student_number');
            const email = cleanEmail(cell('email'));
            if (!studentNumber && !first && !last && !email) continue;

            students.push({
                row: i + 1,
                student_number: studentNumber,
                first_name: first,
                middle_name: middle,
                last_name: last,
                email: email,
                phone_number: cleanPhone(cell('phone_number')),
            });
        }

        return { headerRow: headerRow, students: students };
    }

    /*
       The preview pages rather than scrolls. A block list runs to forty-odd students
       and the modal cannot show them all at once; the earlier version cut the list to
       ten rows and said so in a caption, which read as "only ten were found". Every
       parsed student is held here and the table renders one page of them.
    */
    const BULK_PREVIEW_PAGE_SIZE = 10;
    let bulkPreviewStudents = [];
    let bulkPreviewPage = 1;

    function bulkPreviewPageCount() {
        return Math.max(1, Math.ceil(bulkPreviewStudents.length / BULK_PREVIEW_PAGE_SIZE));
    }

    function bulkPreviewGo(page) {
        const pages = bulkPreviewPageCount();
        bulkPreviewPage = Math.min(Math.max(1, page), pages);

        const start = (bulkPreviewPage - 1) * BULK_PREVIEW_PAGE_SIZE;
        const slice = bulkPreviewStudents.slice(start, start + BULK_PREVIEW_PAGE_SIZE);

        const tbody = document.getElementById('bulkPreviewBody');
        tbody.innerHTML = '';
        const cell = v => String(v || '—').replace(/[<>&]/g, c => ({'<':'&lt;','>':'&gt;','&':'&amp;'}[c]));

        slice.forEach((s, i) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50';
            tr.innerHTML = `
                <td class="px-3 py-2 text-slate-400">${start + i + 1}</td>
                <td class="px-3 py-2 text-slate-400">${s.row}</td>
                <td class="px-3 py-2 font-mono text-slate-700">${cell(s.student_number)}</td>
                <td class="px-3 py-2">${cell(s.last_name)}</td>
                <td class="px-3 py-2">${cell(s.first_name)}</td>
                <td class="px-3 py-2 ${s.email ? 'text-slate-500' : 'text-amber-700 font-semibold'}">${s.email ? cell(s.email) : 'no email'}</td>
                <td class="px-3 py-2 text-slate-500">${cell(s.phone_number)}</td>
            `;
            tbody.appendChild(tr);
        });

        // How many of them have no address, across the whole file rather than the
        // page on screen: the faculty is deciding about the upload, not the page.
        const missing = bulkPreviewStudents.filter((row) => !row.email).length;
        const note = document.getElementById('bulkNoEmailNote');
        if (note) {
            note.classList.toggle('hidden', missing === 0);
            note.classList.toggle('flex', missing > 0);
            const label = document.getElementById('bulkNoEmailCount');
            if (label) label.textContent = missing === 1 ? '1 student has no email address.' : missing + ' students have no email address.';
        }

        // A single page needs no controls, and hiding them keeps the small-file case
        // looking the way it did before paging existed.
        const pager = document.getElementById('bulkPreviewPager');
        pager.classList.toggle('hidden', pages <= 1);
        pager.classList.toggle('flex', pages > 1);

        const from = bulkPreviewStudents.length ? start + 1 : 0;
        const to = start + slice.length;
        document.getElementById('bulkPreviewPageLabel').textContent = `${from}–${to} of ${bulkPreviewStudents.length}`;
        document.getElementById('bulkPreviewPrev').disabled = bulkPreviewPage <= 1;
        document.getElementById('bulkPreviewNext').disabled = bulkPreviewPage >= pages;

        // Back to the top of the list when the page changes, or row eleven starts
        // halfway down the scroll box.
        tbody.parentElement.parentElement.scrollTop = 0;
    }

    function bulkParsePreview(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, {type: 'array'});
                const firstSheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[firstSheetName];

                // range: 0 forces the grid to start at sheet row 1. Without it SheetJS
                // starts at the sheet's used range instead — the registrar's list
                // declares A2:I59, so every row came back one short of the number
                // Excel shows, and the preview disagreed with the server's results
                // table by one. PhpSpreadsheet's toArray() always starts at row 1, so
                // this is what makes the two agree.
                const allRows = XLSX.utils.sheet_to_json(worksheet, {header: 1, defval: "", range: 0});
                if (!allRows.length) {
                    Swal.fire({icon:'warning', title:'Empty File', text:'The file has no data rows.', timer:2500, showConfirmButton:false,
                        iconColor:'#C9A45C', customClass:{popup:'rounded-2xl p-6 bg-white shadow-2xl', title:'text-lg font-bold text-slate-800'}, buttonsStyling:false });
                    return;
                }

                // Mirrors App\Support\StudentRosterSheet so the preview shows exactly what
                // the server will import — both the app's own template and the registrar's
                // official class list.
                const parsed = parseRosterRows(allRows);

                if (parsed.headerRow === null) {
                    Swal.fire({icon:'error', title:'Columns Not Found',
                        html:`<p class="text-sm text-slate-500">This sheet needs a student number column, and either a <b>Name</b> column or separate first and last name columns.</p>`,
                        confirmButtonText:'Okay', iconColor:'#9E1B3C',
                        customClass:{popup:'rounded-2xl p-6 bg-white shadow-2xl', title:'text-lg font-bold text-slate-800',
                            confirmButton:'mt-4 bg-brand text-white px-4 py-2 rounded-lg font-semibold'}, buttonsStyling:false });
                    return;
                }

                if (parsed.students.length === 0) {
                    Swal.fire({icon:'warning', title:'Empty File', text:'The file has no data rows.', timer:2500, showConfirmButton:false,
                        iconColor:'#C9A45C', customClass:{popup:'rounded-2xl p-6 bg-white shadow-2xl', title:'text-lg font-bold text-slate-800'}, buttonsStyling:false });
                    return;
                }

                bulkPreviewStudents = parsed.students;
                bulkPreviewGo(1);

                document.getElementById('bulkPreviewCount').textContent = `(${parsed.students.length} student${parsed.students.length!==1?'s':''})`;
                document.getElementById('bulkImportBtn').classList.remove('hidden');
                bulkSetStep(2);
            } catch (err) {
                console.error(err);
                Swal.fire({icon:'error', title:'Parse Error', text:'Could not read file as Excel.', timer:2500, showConfirmButton:false,
                    iconColor:'#9E1B3C', customClass:{popup:'rounded-2xl p-6 bg-white shadow-2xl', title:'text-lg font-bold text-slate-800'}, buttonsStyling:false });
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

                    // Three outcomes, not two: created and emailed, created but the
                    // email did not go, and not created at all.
                    let statusCell;
                    if (!ok) {
                        statusCell = '<span class="inline-flex items-center gap-1 text-red-600 bg-red-50 border border-red-200 px-2 py-0.5 rounded-full text-[10px] font-bold">✗ Failed</span>';
                    } else if (r.emailed) {
                        statusCell = '<span class="inline-flex items-center gap-1 text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded-full text-[10px] font-bold">✓ Added &amp; emailed</span>';
                    } else {
                        statusCell = '<span class="inline-flex items-center gap-1 text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full text-[10px] font-bold">! Added, no email</span>';
                    }

                    const note = ok ? (r.emailed ? (r.email || '') : (r.reason || '')) : (r.reason || '');
                    const esc = v => String(v || '').replace(/[<>&]/g, c => ({'<':'&lt;','>':'&gt;','&':'&amp;'}[c]));

                    tr.innerHTML = `
                        <td class="px-3 py-2 text-slate-400">${r.row}</td>
                        <td class="px-3 py-2 font-semibold text-slate-700">${esc(r.name)}</td>
                        <td class="px-3 py-2">${statusCell}</td>
                        <td class="px-3 py-2 text-slate-400">${esc(note)}</td>
                    `;
                    rb.appendChild(tr);
                });
                document.getElementById('bulkResultsTableWrap').classList.remove('hidden');
            }

            btn.classList.add('hidden');
            document.getElementById('bulkImportAgainBtn').classList.remove('hidden');
            bulkSetStep(3);

            if (data.created > 0) {
                Swal.fire({
                    icon: 'success',
                    title: 'Import Successful',
                    html: `<p class="text-sm text-slate-500">${data.created} student${data.created === 1 ? '' : 's'} imported.</p>`,
                    timer: 2000,
                    showConfirmButton: false,
                    backdrop: 'rgba(42,17,24, 0.35)',
                    iconColor: '#C9A45C',
                    customClass: {
                        popup: 'rounded-2xl p-6 bg-white shadow-2xl',
                        title: 'text-lg font-bold text-slate-800',
                        htmlContainer: 'mt-1',
                    },
                    buttonsStyling: false,
                });
                setTimeout(() => {
                    const letter = data.open_class || '';
                    window.location.href = letter
                        ? @json(route('faculty.students')) + '?class=' + encodeURIComponent(letter)
                        : window.location.href;
                }, 1000);
            }

        } catch (err) {
            btn.disabled = false;
            label.textContent = 'Import Now';
            Swal.fire({icon:'error', title:'Import Failed',
                html:`<p class="text-sm text-slate-500">${err.message}</p>`,
                confirmButtonText:'Okay', iconColor:'#9E1B3C',
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

    /* Rows on screen filter as you type; once typing pauses, the page reloads with
       ?q= so the server searches every page of the class, not just this one. */
    let studentSearchTimer = null;
    function filterStudentsTable(query) {
        const q = (query || '').trim().toLowerCase();
        const rows = document.querySelectorAll('#studentsTable tbody tr[data-student-id]');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = !q || text.includes(q) ? '' : 'none';
        });

        clearTimeout(studentSearchTimer);
        studentSearchTimer = setTimeout(() => {
            const url = new URL(window.location.href);
            if ((url.searchParams.get('q') || '').trim().toLowerCase() === q) return;
            if (q) url.searchParams.set('q', (query || '').trim());
            else url.searchParams.delete('q');
            url.searchParams.delete('page');
            window.location.assign(url.toString());
        }, 500);
    }

    // After a search reload, put the cursor back at the end of the query.
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('studentSearchInput');
        if (input && input.value) {
            input.focus();
            input.setSelectionRange(input.value.length, input.value.length);
        }
    });

    function toggleStudentSearch() {
        const wrap = document.getElementById('studentSearchWrap');
        const input = document.getElementById('studentSearchInput');
        if (!wrap || !input) return;

        if (wrap.classList.contains('is-open')) {
            if (input.value.trim() === '') {
                collapseStudentSearch(false);
            } else {
                input.focus();
            }
            return;
        }

        wrap.classList.add('is-open');
        setTimeout(() => input.focus(), 220);
    }

    function collapseStudentSearch(clearIfEmpty) {
        const wrap = document.getElementById('studentSearchWrap');
        const input = document.getElementById('studentSearchInput');
        if (!wrap || !input) return;

        if (clearIfEmpty && input.value.trim() === '') {
            input.value = '';
            filterStudentsTable('');
        }

        // Keep expanded while there is an active query
        if (input.value.trim() !== '') {
            input.blur();
            return;
        }

        wrap.classList.remove('is-open');
        input.blur();
    }

    document.addEventListener('click', function (e) {
        const wrap = document.getElementById('studentSearchWrap');
        if (!wrap || !wrap.classList.contains('is-open')) return;
        if (wrap.contains(e.target)) return;
        collapseStudentSearch(true);
    });
</script>
@endpush
@endsection