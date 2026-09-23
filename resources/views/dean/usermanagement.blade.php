@extends('dean.layouts.app')

@section('page_title', 'List of User Management')
@section('page_subtitle', 'Deans, faculty and students with access to the system.')
@section('users_active', 'active')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    /* ── User table, styled like the faculty Manage Students list ──
       DataTables still draws the rows and the pager, so its own stripes, borders
       and buttons are overridden here. Status chips carry their own hex values
       because the palette turns green and red into gold and wine. */
    .st-card { border: 1px solid #EADAD5; border-radius: 1rem; overflow: hidden; background: #fff; }
    table.dataTable#usersTable { border-collapse: collapse !important; width: 100% !important; margin: 0 !important; }
    table.dataTable#usersTable thead th {
        background: #7B1730; color: #FBEEE9; border-bottom: 0 !important;
        font-size: 10.5px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase;
        padding: .9rem .9rem; text-align: left; white-space: nowrap;
    }
    table.dataTable#usersTable tbody tr { background: #fff; transition: background-color .15s ease, box-shadow .15s ease; }
    table.dataTable#usersTable tbody tr.even { background: #FDF8F6; }
    table.dataTable#usersTable tbody tr:hover { background: #FBEEE9 !important; box-shadow: inset 3px 0 0 #7B1730; }
    table.dataTable#usersTable tbody td {
        padding: .8rem .9rem; vertical-align: middle; font-size: 13px;
        border-top: 1px solid #F2E9E7 !important; box-shadow: none !important;
    }
    table.dataTable#usersTable.no-footer { border-bottom: 0 !important; }

    .st-name { font-size: 13.5px; font-weight: 800; color: #2A1118; }
    .st-email { font-size: 11.5px; color: #8A6F76; margin-top: .1rem; }
    .st-muted { font-size: 12px; color: #5A3941; font-weight: 600; }
    .st-dim { color: #C9AFAA; }
    .st-block {
        display: inline-block; white-space: nowrap; font-size: 11px; font-weight: 800;
        color: #7B1730; background: #F7EEEB; border: 1px solid #EADAD5; border-radius: .5rem; padding: .2rem .55rem;
    }

    .st-status {
        display: inline-flex; align-items: center; gap: .4rem; white-space: nowrap;
        border-radius: 9999px; padding: .25rem .65rem; font-size: 11px; font-weight: 800; border: 1px solid;
    }
    .st-status .dot { width: .45rem; height: .45rem; border-radius: 9999px; }
    .st-status.is-active   { color: #15803D; background: #ECFDF3; border-color: #ABEFC6; }
    .st-status.is-active .dot   { background: #16A34A; box-shadow: 0 0 0 3px rgba(22,163,74,.18); }
    .st-status.is-inactive { color: #B42318; background: #FEF3F2; border-color: #FECDCA; }
    .st-status.is-inactive .dot { background: #DC2626; box-shadow: 0 0 0 3px rgba(220,38,38,.18); }
    .st-status.is-pending  { color: #96692C; background: #FBF3E0; border-color: #E9D3A0; }
    .st-status.is-pending .dot  { background: #C9A45C; }

    .st-update, .st-approve {
        display: inline-flex; align-items: center; justify-content: center; gap: .35rem; white-space: nowrap;
        min-width: 6rem; border-radius: .6rem; padding: .4rem .8rem; font-size: 11.5px; font-weight: 800;
        border: 1px solid; transition: all .15s ease;
    }
    .st-update { color: #7B1730; background: #fff; border-color: #DE8299; }
    .st-update:hover { background: #7B1730; border-color: #7B1730; color: #fff; }
    .st-approve { color: #fff; background: #7B1730; border-color: #7B1730; box-shadow: 0 6px 14px -8px rgba(123,23,48,.6); }
    .st-approve:hover { background: #5E1024; border-color: #5E1024; }

    /* Info line and pager under the table */
    #usersTable_wrapper .dataTables_info { padding-top: 1.25rem; font-size: 13px; color: #6B4A54; }
    #usersTable_wrapper .dataTables_info b { color: #2A1118; }
    #usersTable_wrapper .dataTables_paginate { padding-top: 1rem; display: flex; gap: .375rem; flex-wrap: wrap; justify-content: flex-end; }
    #usersTable_wrapper .dataTables_paginate .paginate_button {
        min-width: 2.35rem; height: 2.35rem; padding: 0 .8rem !important; margin: 0 !important; border-radius: .7rem !important;
        display: inline-flex !important; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 700; color: #5A3941 !important; background: #fff !important;
        border: 1px solid #EADAD5 !important; box-shadow: none !important; transition: all .15s ease;
    }
    #usersTable_wrapper .dataTables_paginate .paginate_button:hover {
        color: #7B1730 !important; border-color: #7B1730 !important; background: #FBEEE9 !important;
    }
    #usersTable_wrapper .dataTables_paginate .paginate_button.current,
    #usersTable_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #7B1730 !important; border-color: #7B1730 !important; color: #fff !important;
        box-shadow: 0 6px 14px -6px rgba(123,23,48,.55) !important;
    }
    #usersTable_wrapper .dataTables_paginate .paginate_button.disabled,
    #usersTable_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #C9AFAA !important; background: #FAF6F5 !important; border-color: #EADAD5 !important; cursor: not-allowed;
    }
    #usersTable_wrapper .dataTables_paginate .ellipsis { padding: 0 .35rem; color: #8A6F76; }
</style>
@endpush

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <!-- Toolbar: Faculty / Students + Search + Add Faculty -->
    <div class="px-4 md:px-6 pt-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2 min-w-0">
            <button type="button" onclick="switchTab('faculty')" id="tab-faculty"
                class="tab-btn inline-flex items-center gap-1.5 px-4 py-2.5 rounded-t-xl text-sm font-bold border border-b-0 border-slate-200 bg-white text-brand -mb-px relative z-10 transition">
                <span class="iconify" data-icon="mdi:school-outline"></span> Faculty
            </button>
            <button type="button" onclick="switchTab('student')" id="tab-student"
                class="tab-btn inline-flex items-center gap-1.5 px-4 py-2.5 rounded-t-xl text-sm font-bold border border-b-0 border-transparent bg-slate-50 text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition">
                <span class="iconify" data-icon="mdi:account-group-outline"></span> Students
            </button>
        </div>

        <div class="flex items-center gap-2 pb-3 shrink-0 ml-auto">
            <div class="flex items-center bg-slate-100 rounded-xl px-3.5 h-10 gap-2 w-full sm:w-52">
                <span class="iconify text-slate-400 shrink-0" data-icon="mdi:magnify"></span>
                <input
                    id="userSearchInput"
                    type="text"
                    placeholder="Search users..."
                    class="bg-transparent text-sm outline-none w-full placeholder-slate-400"
                    oninput="filterUsersTable(this.value)"
                >
            </div>
            <button type="button" id="addFacultyBtn" onclick="openModal('createUserModal')"
                class="h-10 bg-brand text-white px-4 rounded-xl text-sm font-bold hover:opacity-95 transition shadow-md shadow-brand/20 inline-flex items-center gap-2 whitespace-nowrap">
                <span class="iconify text-base" data-icon="mdi:account-plus-outline"></span>
                Add Faculty
            </button>
        </div>
    </div>

    @php
        $studentBlocks = $users
            ->where('role', 'student')
            ->map(fn ($u) => $u->student?->facultyClass?->letter)
            ->filter()
            ->map(fn ($letter) => strtoupper($letter));
        $blockTabs = collect($systemBlocks)->merge($studentBlocks)->unique()->sort()->values();
        $hasUnassignedStudents = $users
            ->where('role', 'student')
            ->contains(fn ($u) => ! $u->student?->facultyClass?->letter);
    @endphp

    <!-- Block sub-tabs (Students only) -->
    <div id="blockTabBar" class="hidden px-4 md:px-6 pt-3 pb-3 border-b border-slate-100 bg-slate-50/60 flex-wrap gap-2">
        <button type="button" onclick="switchBlockTab('')" data-block-tab=""
            class="block-tab-btn px-3.5 py-1.5 rounded-full text-xs font-bold border border-slate-200 bg-white text-slate-500 hover:text-brand transition">
            All
        </button>
        @foreach ($blockTabs as $letter)
            <button type="button" onclick="switchBlockTab('{{ $letter }}')" data-block-tab="{{ $letter }}"
                class="block-tab-btn px-3.5 py-1.5 rounded-full text-xs font-bold border border-slate-200 bg-white text-slate-500 hover:text-brand transition">
                Block {{ $letter }}
            </button>
        @endforeach
        <button type="button" onclick="switchBlockTab('__none__')" data-block-tab="__none__"
            class="block-tab-btn px-3.5 py-1.5 rounded-full text-xs font-bold border border-slate-200 bg-white text-slate-500 hover:text-brand transition {{ $hasUnassignedStudents ? '' : 'hidden' }}">
            Unassigned
        </button>
    </div>

    @if (session('success'))
        <div id="successAlert" class="mx-6 mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <!-- DataTable -->
    <div class="p-4 md:p-6">
        <div class="st-card overflow-x-auto">
        <table id="usersTable" class="display nowrap w-full">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Phone Number</th>
                    <th>Block</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th class="text-center" style="text-align: center !important;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    @php
                        $displayName = trim(implode(' ', array_filter([
                            $user->last_name ?? null,
                            $user->first_name ?? null,
                            $user->middle_name ?? null,
                        ])));
                        $displayName = $displayName !== '' ? $displayName : ($user->name ?? 'User');
                        $phone = $user->phone_number ?? ($user->faculty->phone_number ?? null);
                        $status = $user->status ?? ($user->faculty->status ?? 'active');
                        $statusChip = match ($status) {
                            'active'  => ['class' => 'is-active', 'label' => 'Active'],
                            'pending' => ['class' => 'is-pending', 'label' => 'Pending'],
                            default   => ['class' => 'is-inactive', 'label' => 'Inactive'],
                        };
                        $username = $user->email ? explode('@', $user->email, 2)[0] : '';
                        $emailDomain = $user->email && str_contains($user->email, '@')
                            ? explode('@', $user->email, 2)[1]
                            : 'hms.edu';
                        $block = null;
                        if ($user->role === 'faculty') {
                            $block = $user->faculty->block ?? null;
                        } elseif ($user->role === 'student') {
                            // A student's block is the block row they sit in, not the faculty's own block letter.
                            $block = $user->student?->facultyClass?->letter ?? null;
                        }
                        $blockLabel = \App\Models\Faculty::blockLabel($block);
                    @endphp
                    <tr data-user-id="{{ $user->user_id }}" data-role="{{ $user->role }}" data-block="{{ strtoupper((string) $block) }}">
                        <td>
                            <div class="flex items-center gap-3 min-w-0">
                                @include('partials.user-avatar', [
                                    'user'         => $user,
                                    'name'         => $displayName,
                                    'size'         => 'w-9 h-9',
                                    'rounded'      => 'rounded-full',
                                    'extraClasses' => 'bg-brand-soft text-brand text-[11px] font-bold border border-pink-100',
                                ])
                                <div class="min-w-0">
                                    <p class="st-name truncate">{{ $displayName }}</p>
                                    <p class="st-email truncate">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if ($phone)
                                <span class="st-muted">{{ $phone }}</span>
                            @else
                                <span class="st-muted st-dim">—</span>
                            @endif
                        </td>
                        <td>
                            @if ($block)
                                <span class="st-block">{{ $blockLabel }}</span>
                            @else
                                <span class="st-muted st-dim">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="st-status {{ $statusChip['class'] }}"><span class="dot"></span> {{ $statusChip['label'] }}</span>
                        </td>
                        <td>
                            <span class="st-muted inline-flex items-center gap-1.5 whitespace-nowrap">
                                <span class="iconify text-sm text-amber-500" data-icon="mdi:calendar-blank-outline"></span>
                                {{ optional($user->created_at)->format('M d, Y') }}
                            </span>
                        </td>
                        <td class="text-center" style="text-align: center !important;">
                            <div class="flex justify-center w-full">
                                @if ($user->role === 'student' && $status === 'pending')
                                    <form method="POST" action="{{ route('dean.users.approve', $user) }}">
                                        @csrf
                                        <button type="submit" class="st-approve" aria-label="Approve student">
                                            <span class="iconify text-sm" data-icon="mdi:check-circle-outline"></span>
                                            Approve
                                        </button>
                                    </form>
                                @else
                                    <button
                                        type="button"
                                        onclick="openUpdateModal(this)"
                                        data-user-id="{{ $user->user_id }}"
                                        data-first-name="{{ $user->first_name }}"
                                        data-middle-name="{{ $user->middle_name }}"
                                        data-last-name="{{ $user->last_name }}"
                                        data-username="{{ $username }}"
                                        data-email-domain="{{ $emailDomain }}"
                                        data-full-name="{{ $displayName }}"
                                        data-phone-number="{{ $phone }}"
                                        data-status="{{ $status }}"
                                        data-role="{{ $user->role }}"
                                        data-block="{{ $block ?? '' }}"
                                        class="st-update"
                                        aria-label="Update user"
                                    >
                                        <span class="iconify text-sm" data-icon="mdi:pencil-outline"></span>
                                        Update
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-slate-500 py-6">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div id="createUserModal" class="fixed inset-0 hidden" style="z-index: 9999;">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('createUserModal')"></div>
    <div class="relative top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-100" style="width: 760px; max-width: 92vw; max-height: 90vh; overflow-y: auto; z-index: 10000;">
        <div class="bg-brand-soft px-6 py-4 border-b border-brand/10 flex justify-between items-center sticky top-0 z-10">
            <h4 class="font-bold text-brand text-lg">Add Faculty Details</h4>
            <button onclick="closeModal('createUserModal')" class="text-slate-400 hover:text-brand hover:bg-white w-8 h-8 rounded-full transition flex items-center justify-center">
                <span class="iconify text-xl" data-icon="mdi:close"></span>
            </button>
        </div>
        <form method="POST" action="{{ route('dean.users.store') }}" onsubmit="return validateUserPasswords()">
        @csrf
        @if ($errors->any())
            <div class="mx-6 mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Last Name</label>
                <input name="last_name" type="text" placeholder="e.g. Smith" required class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">First Name</label>
                <input name="first_name" type="text" placeholder="e.g. Jane" required class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Middle Name</label>
                <input name="middle_name" type="text" placeholder="e.g. A." class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Email</label>
                <input name="email" type="email" placeholder="user@hms.edu" required class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Phone Number</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-600 text-xs font-medium">+63</span>
                    <input name="phone_number" type="text" placeholder="912 345 6789" class="w-full h-10 pl-12 pr-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Role</label>
                {{-- This modal only ever adds faculty (the Add Faculty button is hidden on the
                     Students tab), so the role is fixed rather than chosen. --}}
                <input type="hidden" name="role" id="createUserRole" value="faculty">
                <div class="w-full h-10 px-3 bg-slate-100 border border-slate-200 rounded-xl text-sm flex items-center text-slate-500 font-semibold">
                    Faculty
                </div>
            </div>
            {{-- No block to choose: it is always the next free class letter, so it
                 is assigned on save. Changing one is what the Block field on the
                 update form below is for. --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Status</label>
                {{-- Active, and not a choice - see the Faculties page. Deactivating an
                     account is what the update form below is for. --}}
                <input type="hidden" name="status" value="active">
                <div class="w-full h-10 px-3 bg-slate-100 border border-slate-200 rounded-xl text-sm flex items-center gap-2 text-slate-500 font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Password</label>
                <div class="relative">
                    <input id="createUserPassword" name="password" type="password" placeholder="Min. 8 characters" required oninput="validateUserPasswords()" class="w-full h-10 px-3 pr-10 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                    <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <span class="iconify text-lg" data-icon="mdi:eye-off-outline"></span>
                    </button>
                </div>
                <p id="createUserPasswordHelp" class="mt-1 text-[11px] text-red-500 hidden">Password must be at least 8 characters.</p>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Confirm Password</label>
                <div class="relative">
                    <input id="createUserPasswordConfirm" name="password_confirmation" type="password" placeholder="Re-enter password" required oninput="validateUserPasswords()" class="w-full h-10 px-3 pr-10 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                    <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <span class="iconify text-lg" data-icon="mdi:eye-off-outline"></span>
                    </button>
                </div>
                <p id="createUserPasswordMatch" class="mt-1 text-[11px] text-red-500 hidden">Passwords do not match.</p>
            </div>
        </div>
        <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 sticky bottom-0 relative" style="z-index: 10;">
            <button type="button" onclick="closeModal('createUserModal')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-200 transition font-semibold text-sm">Cancel</button>
            <button type="submit" class="px-5 py-2 bg-brand text-white rounded-xl font-bold text-sm hover:scale-105 transition shadow-md shadow-brand/20">Add User</button>
        </div>
        </form>
    </div>
</div>

<!-- Update User Modal -->
<div id="updateUserModal" class="fixed inset-0 hidden" style="z-index: 9999;">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('updateUserModal')"></div>
    <div class="relative top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-100" style="width: 760px; max-width: 92vw; max-height: 90vh; overflow-y: auto; z-index: 10000;">
        <div class="bg-brand-soft px-6 py-4 border-b border-brand/10 flex justify-between items-center sticky top-0 z-10">
            <h4 class="font-bold text-brand text-lg">Update User</h4>
            <button onclick="closeModal('updateUserModal')" class="text-slate-400 hover:text-brand hover:bg-white w-8 h-8 rounded-full transition flex items-center justify-center">
                <span class="iconify text-xl" data-icon="mdi:close"></span>
            </button>
        </div>
        <form id="updateUserForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Last Name</label>
                    <input id="updateLastName" type="text" placeholder="e.g. Smith" readonly class="w-full h-10 px-3 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">First Name</label>
                    <input id="updateFirstName" type="text" placeholder="e.g. Jane" readonly class="w-full h-10 px-3 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Middle Name</label>
                    <input id="updateMiddleName" type="text" placeholder="e.g. A." readonly class="w-full h-10 px-3 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Username</label>
                    <div class="relative">
                        <input id="updateUsername" type="text" placeholder="user" readonly class="w-full h-10 pl-3 pr-24 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 focus:outline-none">
                        <span id="updateEmailDomain" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-medium">@hms.edu</span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Phone Number</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-600 text-xs font-medium">+63</span>
                        <input id="updatePhone" type="text" placeholder="912 345 6789" readonly class="w-full h-10 pl-12 pr-3 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Role</label>
                    <div id="updateRole" class="w-full h-10 px-3 bg-slate-100 border border-slate-200 rounded-xl text-sm flex items-center text-slate-500 font-semibold">
                        —
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Block</label>
                    <select id="updateBlock" name="block" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition appearance-none">
                        <option value="">Select block</option>
                    </select>
                    <div id="updateBlockReadonly" class="hidden w-full h-10 px-3 bg-slate-100 border border-slate-200 rounded-xl text-sm items-center text-slate-500 font-semibold">
                        —
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Status</label>
                    <select id="updateStatus" name="status" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition appearance-none">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 sticky bottom-0">
                <button type="button" onclick="closeModal('updateUserModal')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-200 transition font-semibold text-sm">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-brand text-white rounded-xl font-bold text-sm hover:scale-105 transition shadow-md shadow-brand/20">Update User</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/hms-swal-theme.js') }}?v={{ filemtime(public_path('js/hms-swal-theme.js')) }}"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.15.0/echo.iife.js"></script>
<script>
    const approveBaseUrl = "{{ url('/dean/users') }}";
    const updateBaseUrl = "{{ url('/dean/users') }}";
    const csrfToken = "{{ csrf_token() }}";

    document.addEventListener('DOMContentLoaded', function() {
        const successAlert = document.getElementById('successAlert');
        if (successAlert) {
            setTimeout(() => {
                successAlert.classList.add('hidden');
            }, 5000);
        }

        @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
            timer: 2500,
            showConfirmButton: false,
        });
        @endif

        @if ($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Action Failed',
            text: '{{ $errors->first() }}',
        });
        @endif
    });

    let usersTable;
    const seenUserIds = new Set(@json($users->pluck('user_id')->map(fn ($id) => (int) $id)->values()));
    const liveUsersUrl = "{{ route('dean.users.live') }}";
    const availableBlocks = @json($availableBlocks);
    const systemBlocks = @json($systemBlocks);
    let currentTab = 'faculty';
    let currentBlock = ''; // '' = all blocks, '__none__' = students with no block
    const BLOCK_COLUMN_INDEX = 2;

    function syncSeenUserIds() {
        if (!usersTable) return;
        usersTable.rows({ page: 'all' }).nodes().to$().each(function () {
            const id = this.getAttribute('data-user-id');
            if (id) {
                seenUserIds.add(Number(id));
            }
        });
    }

    $(document).ready(function() {
        usersTable = $('#usersTable').DataTable({
            "responsive": true,
            "pageLength": 5,
            "lengthChange": false,
            "dom": 'rtip',
            "language": {
                "info": "Showing <b>_START_</b> to <b>_END_</b> of <b>_TOTAL_</b>",
                "paginate": {
                    "next": "<span class='iconify' data-icon='mdi:chevron-right'></span>",
                    "previous": "<span class='iconify' data-icon='mdi:chevron-left'></span>"
                }
            },
            "createdRow": function (row, data, dataIndex) {
                const existing = row.getAttribute('data-user-id');
                if (!existing && data && data.DT_RowAttr && data.DT_RowAttr['data-user-id']) {
                    row.setAttribute('data-user-id', data.DT_RowAttr['data-user-id']);
                }
            }
        });

        // Custom filter by data-role, plus data-block while the Students tab is open
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (!usersTable || settings.nTable !== usersTable.table().node()) {
                return true;
            }
            const row = usersTable.row(dataIndex).node();
            if (!row) return true;
            const role = (row.getAttribute('data-role') || '').toLowerCase();
            if (role !== currentTab) return false;
            if (currentTab !== 'student' || currentBlock === '') return true;

            const block = (row.getAttribute('data-block') || '').toUpperCase();
            return currentBlock === '__none__' ? block === '' : block === currentBlock;
        });

        syncSeenUserIds();
        switchTab('faculty');
    });

    function filterUsersTable(query) {
        if (!usersTable) return;
        usersTable.search(query || '').draw();
    }

    function switchTab(role) {
        currentTab = role;
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-slate-200', 'bg-white', 'text-brand', '-mb-px', 'z-10');
            btn.classList.add('border-transparent', 'bg-slate-50', 'text-slate-500');
        });
        const active = document.getElementById('tab-' + role);
        if (active) {
            active.classList.remove('border-transparent', 'bg-slate-50', 'text-slate-500');
            active.classList.add('border-slate-200', 'bg-white', 'text-brand', '-mb-px', 'z-10');
        }

        const addFacultyBtn = document.getElementById('addFacultyBtn');
        if (addFacultyBtn) {
            addFacultyBtn.classList.toggle('hidden', role !== 'faculty');
        }

        const blockBar = document.getElementById('blockTabBar');
        if (blockBar) {
            blockBar.classList.toggle('hidden', role !== 'student');
            blockBar.classList.toggle('flex', role === 'student');
        }

        if (role === 'student') {
            switchBlockTab(currentBlock);
            return;
        }

        if (!usersTable) return;
        // Block is a column for faculty, a tab for students.
        usersTable.column(BLOCK_COLUMN_INDEX).visible(true, false);
        usersTable.columns.adjust().draw();
    }

    function switchBlockTab(block) {
        currentBlock = block || '';
        document.querySelectorAll('.block-tab-btn').forEach(btn => {
            const isActive = (btn.getAttribute('data-block-tab') || '') === currentBlock;
            btn.classList.toggle('bg-brand', isActive);
            btn.classList.toggle('text-white', isActive);
            btn.classList.toggle('border-brand', isActive);
            btn.classList.toggle('bg-white', !isActive);
            btn.classList.toggle('text-slate-500', !isActive);
            btn.classList.toggle('border-slate-200', !isActive);
        });

        if (!usersTable) return;
        usersTable.column(BLOCK_COLUMN_INDEX).visible(false, false);
        usersTable.columns.adjust().draw();
    }

    function revealUnassignedBlockTab() {
        const btn = document.querySelector('.block-tab-btn[data-block-tab="__none__"]');
        if (btn) btn.classList.remove('hidden');
    }

    function blockBadge(block, blockLabel) {
        if (block) {
            const label = blockLabel || (`Block ${block}`);
            return `<span class="st-block">${escapeCell(label)}</span>`;
        }
        return '<span class="st-muted st-dim">—</span>';
    }

    function escapeCell(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function statusChip(status) {
        const chip = status === 'active'
            ? ['is-active', 'Active']
            : status === 'pending' ? ['is-pending', 'Pending'] : ['is-inactive', 'Inactive'];
        return `<span class="st-status ${chip[0]}"><span class="dot"></span> ${chip[1]}</span>`;
    }

    function buildUserRow(user) {
        const initials = String(user.name || '?').trim().split(/\s+/).slice(0, 2).map(p => p[0] || '').join('').toUpperCase();
        const nameCell = `
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-9 h-9 rounded-full bg-brand-soft text-brand text-[11px] font-bold border border-pink-100 inline-flex items-center justify-center shrink-0">${escapeCell(initials)}</span>
                <div class="min-w-0">
                    <p class="st-name truncate">${escapeCell(user.name)}</p>
                    <p class="st-email truncate">${escapeCell(user.email ?? '')}</p>
                </div>
            </div>`;
        const phoneCell = user.phone_number
            ? `<span class="st-muted">${escapeCell(user.phone_number)}</span>`
            : '<span class="st-muted st-dim">—</span>';
        const joinedCell = `
            <span class="st-muted inline-flex items-center gap-1.5 whitespace-nowrap">
                <span class="iconify text-sm text-amber-500" data-icon="mdi:calendar-blank-outline"></span>
                ${escapeCell(user.joined ?? '—')}
            </span>`;
        const actionCell = user.role === 'student' && user.status === 'pending'
            ? `
                <div class="flex justify-center w-full">
                    <form method="POST" action="${approveBaseUrl}/${user.id}/approve">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <button type="submit" class="st-approve" aria-label="Approve student">
                            <span class="iconify text-sm" data-icon="mdi:check-circle-outline"></span>
                            Approve
                        </button>
                    </form>
                </div>
            `
            : `
                <div class="flex justify-center w-full">
                    <button
                        type="button"
                        onclick="openUpdateModal(this)"
                        data-user-id="${user.id}"
                        data-first-name="${escapeCell(user.first_name)}"
                        data-middle-name="${escapeCell(user.middle_name)}"
                        data-last-name="${escapeCell(user.last_name)}"
                        data-username="${escapeCell(user.username)}"
                        data-email-domain="${escapeCell(user.email_domain ?? 'hms.edu')}"
                        data-full-name="${escapeCell(user.name)}"
                        data-phone-number="${escapeCell(user.phone_number)}"
                        data-status="${escapeCell(user.status ?? 'active')}"
                        data-role="${escapeCell(user.role)}"
                        data-block="${escapeCell(user.block)}"
                        class="st-update"
                        aria-label="Update user"
                    >
                        <span class="iconify text-sm" data-icon="mdi:pencil-outline"></span>
                        Update
                    </button>
                </div>
            `;

        return [
            nameCell,
            phoneCell,
            blockBadge(user.block, user.block_label),
            statusChip(user.status),
            joinedCell,
            actionCell,
        ];
    }

    function addUserRow(user) {
        if (!usersTable || !user || user.id == null) {
            return;
        }

        const id = Number(user.id);
        if (seenUserIds.has(id)) {
            return;
        }

        const alreadyInTable = usersTable
            .rows({ page: 'all' })
            .nodes()
            .to$()
            .filter(`[data-user-id="${id}"]`)
            .length > 0;

        if (alreadyInTable) {
            seenUserIds.add(id);
            return;
        }

        const block = (user.block || '').toUpperCase();
        const rowNode = usersTable.row.add(buildUserRow(user)).draw(false).node();
        if (rowNode) {
            rowNode.setAttribute('data-user-id', String(id));
            rowNode.setAttribute('data-role', user.role || '');
            rowNode.setAttribute('data-block', block);
        }
        if (user.role === 'student') {
            block ? addBlockTabIfMissing(block) : revealUnassignedBlockTab();
        }
        seenUserIds.add(id);
    }

    function addBlockTabIfMissing(letter) {
        const bar = document.getElementById('blockTabBar');
        if (!bar || bar.querySelector(`.block-tab-btn[data-block-tab="${letter}"]`)) return;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.setAttribute('data-block-tab', letter);
        btn.className = 'block-tab-btn px-3.5 py-1.5 rounded-full text-xs font-bold border border-slate-200 bg-white text-slate-500 hover:text-brand transition';
        btn.textContent = `Block ${letter}`;
        btn.onclick = () => switchBlockTab(letter);

        const unassigned = bar.querySelector('.block-tab-btn[data-block-tab="__none__"]');
        bar.insertBefore(btn, unassigned);
    }

    async function pollLiveUsers() {
        try {
            const response = await fetch(liveUsersUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) {
                return;
            }
            const users = await response.json();
            users.forEach(addUserRow);
        } catch (error) {
            console.warn('Live users fetch failed', error);
        }
    }

    const pusherKey = "{{ env('PUSHER_APP_KEY') }}";
    const pusherCluster = "{{ env('PUSHER_APP_CLUSTER', 'mt1') }}";
    const pusherHost = "{{ env('PUSHER_HOST') }}";
    const pusherPort = "{{ env('PUSHER_PORT', 443) }}";
    const pusherScheme = "{{ env('PUSHER_SCHEME', 'https') }}";

    if (pusherKey) {
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: pusherKey,
            cluster: pusherCluster,
            wsHost: pusherHost ? pusherHost : `ws-${pusherCluster}.pusher.com`,
            wsPort: pusherPort ?? 80,
            wssPort: pusherPort ?? 443,
            forceTLS: pusherScheme === 'https',
            enabledTransports: ['ws', 'wss'],
        });

        window.Echo.channel('students')
            .listen('.StudentCreated', (payload) => {
                const username = payload.email ? payload.email.split('@')[0] : '';
                const emailDomain = payload.email && payload.email.includes('@')
                    ? payload.email.split('@')[1]
                    : 'hms.edu';
                addUserRow({
                    id: payload.user_id,
                    name: payload.name,
                    first_name: payload.first_name ?? '',
                    middle_name: payload.middle_name ?? '',
                    last_name: payload.last_name ?? '',
                    username: username,
                    email_domain: emailDomain,
                    email: payload.email,
                    phone_number: payload.phone_number,
                    role: 'student',
                    block: payload.block ?? null,
                    block_label: payload.block_label ?? null,
                    status: payload.status ?? 'active',
                    joined: payload.joined,
                });
            });
    }

    setInterval(pollLiveUsers, 5000);

    function openModal(id) { document.getElementById(id).classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
    function closeModal(id) { document.getElementById(id).classList.add('hidden'); document.body.style.overflow = 'auto'; }

    function parseNameParts(fullName) {
        const parts = fullName.trim().split(/\s+/).filter(Boolean);
        if (parts.length === 0) {
            return { first: '', middle: '', last: '' };
        }
        if (parts.length === 1) {
            return { first: parts[0], middle: '', last: '' };
        }
        if (parts.length === 2) {
            return { first: parts[0], middle: '', last: parts[1] };
        }
        return {
            first: parts[0],
            middle: parts.slice(1, -1).join(' '),
            last: parts[parts.length - 1],
        };
    }

    function openUpdateModal(button) {
        const form = document.getElementById('updateUserForm');
        const userId = button.getAttribute('data-user-id');
        let firstName = button.getAttribute('data-first-name') || '';
        let middleName = button.getAttribute('data-middle-name') || '';
        let lastName = button.getAttribute('data-last-name') || '';
        const username = button.getAttribute('data-username') || '';
        const emailDomain = button.getAttribute('data-email-domain') || 'hms.edu';
        const fullName = button.getAttribute('data-full-name') || '';
        const phoneNumber = button.getAttribute('data-phone-number') || '';
        const status = button.getAttribute('data-status') || 'active';
        const role = button.getAttribute('data-role') || 'user';
        const block = (button.getAttribute('data-block') || '').toUpperCase();

        if (!firstName && !middleName && !lastName && fullName) {
            const parsed = parseNameParts(fullName);
            firstName = parsed.first;
            middleName = parsed.middle;
            lastName = parsed.last;
        }

        form.action = `${updateBaseUrl}/${userId}`;
        document.getElementById('updateFirstName').value = firstName;
        document.getElementById('updateMiddleName').value = middleName;
        document.getElementById('updateLastName').value = lastName;
        document.getElementById('updateUsername').value = username;
        document.getElementById('updateEmailDomain').textContent = `@${emailDomain}`;
        document.getElementById('updatePhone').value = phoneNumber;
        document.getElementById('updateStatus').value = status;
        document.getElementById('updateRole').textContent = role.charAt(0).toUpperCase() + role.slice(1);

        const blockSelect = document.getElementById('updateBlock');
        const blockReadonly = document.getElementById('updateBlockReadonly');
        const isFaculty = role === 'faculty';

        if (isFaculty) {
            blockSelect.classList.remove('hidden');
            blockReadonly.classList.add('hidden');
            blockReadonly.classList.remove('flex');
            blockSelect.disabled = false;
            blockSelect.required = true;
            blockSelect.name = 'block';

            // Only system-defined blocks that are available (+ current if valid)
            const options = availableBlocks.filter((letter) => systemBlocks.includes(letter));
            if (block && systemBlocks.includes(block) && !options.includes(block)) {
                options.push(block);
            }
            options.sort();

            blockSelect.innerHTML = '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = options.length ? 'Select block' : 'No blocks available';
            placeholder.disabled = options.length === 0;
            blockSelect.appendChild(placeholder);

            options.forEach((letter) => {
                const opt = document.createElement('option');
                opt.value = letter;
                opt.textContent = `Block ${letter}`;
                if (letter === block) opt.selected = true;
                blockSelect.appendChild(opt);
            });
            if (block && systemBlocks.includes(block)) {
                blockSelect.value = block;
            }
        } else {
            blockSelect.classList.add('hidden');
            blockSelect.disabled = true;
            blockSelect.required = false;
            blockSelect.removeAttribute('name');
            blockReadonly.classList.remove('hidden');
            blockReadonly.classList.add('flex');
            blockReadonly.textContent = (block && systemBlocks.includes(block)) ? `Block ${block}` : '—';
        }

        openModal('updateUserModal');
    }
    
    function togglePassword(btn) {
        const input = btn.parentElement.querySelector('input');
        const icon = btn.querySelector('.iconify');
        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('data-icon', 'mdi:eye-outline');
        } else {
            input.type = 'password';
            icon.setAttribute('data-icon', 'mdi:eye-off-outline');
        }
    }

    function validateUserPasswords() {
        const password = document.getElementById('createUserPassword');
        const confirm = document.getElementById('createUserPasswordConfirm');
        const lengthHelp = document.getElementById('createUserPasswordHelp');
        const matchHelp = document.getElementById('createUserPasswordMatch');

        if (!password || !confirm) {
            return true;
        }

        const tooShort = password.value.length > 0 && password.value.length < 8;
        const mismatch = confirm.value.length > 0 && password.value !== confirm.value;

        lengthHelp.classList.toggle('hidden', !tooShort);
        matchHelp.classList.toggle('hidden', !mismatch);

        return !(tooShort || mismatch);
    }

    @if ($errors->any() && old('role'))
        openModal('createUserModal');
    @endif
</script>
@endpush
@endsection
