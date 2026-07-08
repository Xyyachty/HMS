@extends('dean.layouts.app')

@section('page_title', 'User Management')
@section('users_active', 'active')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    .dataTables_wrapper .dataTables_filter input {
        background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 0.75rem;
        padding: 0.5rem 1rem; margin-left: 0.5rem; outline: none; font-family: 'Manrope', sans-serif;
    }
    .dataTables_wrapper .dataTables_filter input:focus { border-color: #DB2777; box-shadow: 0 0 0 3px rgba(219, 39, 119, 0.12); }
    .dataTables_wrapper .dataTables_length select {
        background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 0.5rem;
        padding: 0.25rem 0.5rem; outline: none; font-family: 'Manrope', sans-serif;
    }
    table.dataTable thead th {
        background: #FDF2F8; color: #64748B; font-weight: 800; text-transform: uppercase;
        font-size: 0.65rem; letter-spacing: 0.1em; padding: 1rem; border-bottom: 2px solid #FBCFE8;
    }
    table.dataTable tbody tr { transition: background 0.2s; }
    table.dataTable tbody tr:hover { background: #FDF2F8 !important; }
    table.dataTable tbody td { padding: 1rem; vertical-align: middle; font-size: 0.875rem; }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #DB2777 !important; color: white !important; border-radius: 9999px !important;
        border: none !important; padding: 0.25rem 0.75rem; box-shadow: 0 4px 6px -1px rgba(219,39,119,0.2);
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 9999px !important; background: transparent; border: 1px solid #E2E8F0; margin: 0 2px;
    }
</style>
@endpush

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <!-- Header -->
    <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50/50">
        <div>
            <h3 class="text-lg font-bold text-slate-800">All Users</h3>
            <p class="text-sm text-slate-500 mt-1">Manage faculty & student accounts across the system.</p>
        </div>
        <div class="flex gap-3">
            <button onclick="openModal('createUserModal')" class="bg-brand text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:scale-105 transition shadow-md shadow-brand/20 flex items-center gap-2">
                <span class="iconify" data-icon="mdi:account-plus-outline"></span> Add User
            </button>
        </div>
    </div>

    <!-- Tab Bar -->
    <div class="px-6 pt-4 flex gap-2 border-b border-slate-100">
        <button onclick="switchTab('all')" id="tab-all" class="tab-btn px-5 py-2.5 text-sm font-bold rounded-t-xl border-b-2 border-brand text-brand bg-brand-soft transition">
            All Users
        </button>
        <button onclick="switchTab('faculty')" id="tab-faculty" class="tab-btn px-5 py-2.5 text-sm font-bold rounded-t-xl border-b-2 border-transparent text-slate-500 hover:text-brand transition">
            <span class="iconify mr-1" data-icon="mdi:school-outline"></span> Faculty
        </button>
        <button onclick="switchTab('student')" id="tab-student" class="tab-btn px-5 py-2.5 text-sm font-bold rounded-t-xl border-b-2 border-transparent text-slate-500 hover:text-brand transition">
            <span class="iconify mr-1" data-icon="mdi:account-group-outline"></span> Students
        </button>
    </div>

        @if (session('success'))
        <div id="successAlert" class="mx-6 mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
        @endif


    <!-- DataTable -->
    <div class="p-6">
        <table id="usersTable" class="display nowrap w-full text-sm text-slate-700">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Role</th>
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
                        $username = $user->email ? explode('@', $user->email, 2)[0] : '';
                        $emailDomain = $user->email && str_contains($user->email, '@')
                            ? explode('@', $user->email, 2)[1]
                            : 'hms.edu';
                    @endphp
                    <tr data-user-id="{{ $user->id }}">
                        <td>
                            <span class="font-semibold text-slate-800">{{ $displayName }}</span>
                        </td>
                        <td class="text-slate-400">{{ $user->email }}</td>
                        <td class="text-slate-600 font-medium">{{ $phone ?? '—' }}</td>
                        <td>
                            @if ($user->role === 'dean')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Dean</span>
                            @elseif ($user->role === 'faculty')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-accent border border-rose-accent/10">Faculty</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-brand-soft text-brand border border-brand/10">Student</span>
                            @endif
                        </td>
                        <td>
                            @if ($status === 'active')
                                <span class="flex items-center gap-1.5 text-xs font-semibold text-green-600"><span class="w-2 h-2 bg-green-500 rounded-full"></span> Active</span>
                            @elseif ($status === 'pending')
                                <span class="flex items-center gap-1.5 text-xs font-semibold text-amber-600"><span class="w-2 h-2 bg-amber-500 rounded-full"></span> Pending</span>
                            @else
                                <span class="flex items-center gap-1.5 text-xs font-semibold text-amber-600"><span class="w-2 h-2 bg-amber-500 rounded-full"></span> Suspended</span>
                            @endif
                        </td>
                        <td class="text-slate-500 text-sm">{{ optional($user->created_at)->format('M d, Y') }}</td>
                        <td class="text-center" style="text-align: center !important;">
                            <div class="flex justify-center w-full">
                                @if ($user->role === 'student' && $status === 'pending')
                                    <form method="POST" action="{{ route('dean.users.approve', $user) }}">
                                        @csrf
                                        <button type="submit" class="text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition text-xs font-semibold px-3 py-2 rounded-lg inline-flex items-center justify-center gap-2 w-24" aria-label="Approve student">
                                            <span class="iconify text-base" data-icon="mdi:check-circle-outline"></span>
                                            <span>Approve</span>
                                        </button>
                                    </form>
                                @else
                                    <button
                                        onclick="openUpdateModal(this)"
                                        data-user-id="{{ $user->id }}"
                                        data-first-name="{{ $user->first_name }}"
                                        data-middle-name="{{ $user->middle_name }}"
                                        data-last-name="{{ $user->last_name }}"
                                        data-username="{{ $username }}"
                                        data-email-domain="{{ $emailDomain }}"
                                        data-full-name="{{ $displayName }}"
                                        data-phone-number="{{ $phone }}"
                                        data-status="{{ $status }}"
                                        data-role="{{ $user->role }}"
                                        class="text-brand bg-brand-soft hover:bg-brand/10 transition text-xs font-semibold px-3 py-2 rounded-lg inline-flex items-center justify-center gap-2 w-24"
                                        aria-label="Update user"
                                    >
                                        <span class="iconify text-base" data-icon="mdi:update"></span>
                                        <span>Update</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-slate-500 py-6">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create User Modal -->
<div id="createUserModal" class="fixed inset-0 hidden" style="z-index: 9999;">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('createUserModal')"></div>
    <div class="relative top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-100" style="width: 760px; max-width: 92vw; max-height: 90vh; overflow-y: auto; z-index: 10000;">
        <div class="bg-brand-soft px-6 py-4 border-b border-brand/10 flex justify-between items-center sticky top-0 z-10">
            <h4 class="font-bold text-brand text-lg">Add New User</h4>
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
                <select name="role" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition appearance-none">
                    <option value="faculty">Faculty</option>
                    <option value="student">Student</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Status</label>
                <select name="status" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition appearance-none">
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                </select>
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
        <form id="updateUserForm" method="POST" action="" onsubmit="return validateUpdatePasswords()">
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
                        <input id="updatePhone" name="phone_number" type="text" placeholder="912 345 6789" class="w-full h-10 pl-12 pr-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Role</label>
                    <div id="updateRole" class="w-full h-10 px-3 bg-slate-100 border border-slate-200 rounded-xl text-sm flex items-center text-slate-500 font-semibold">
                        —
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Status</label>
                    <select id="updateStatus" name="status" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition appearance-none">
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Password</label>
                    <div class="relative">
                        <input id="updatePassword" name="password" type="password" placeholder="New Password" oninput="validateUpdatePasswords()" class="w-full h-10 px-3 pr-10 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
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
    const seenUserIds = new Set();
    const liveUsersUrl = "{{ route('dean.users.live') }}";
    $(document).ready(function() {
        usersTable = $('#usersTable').DataTable({
            "responsive": true,
            "pageLength": 5,
            "lengthChange": false,
            "language": {
                "search": "",
                "searchPlaceholder": "Search users...",
                "info": "Showing <b>_START_</b> to <b>_END_</b> of <b>_TOTAL_</b>",
                "paginate": {
                    "next": "<span class='iconify' data-icon='mdi:chevron-right'></span>",
                    "previous": "<span class='iconify' data-icon='mdi:chevron-left'></span>"
                }
            }
        });

        document.querySelectorAll('#usersTable tbody tr[data-user-id]').forEach(row => {
            const id = row.getAttribute('data-user-id');
            if (id) {
                seenUserIds.add(Number(id));
            }
        });
    });

    function buildUserRow(user) {
        const nameCell = `<span class="font-semibold text-slate-800">${user.name}</span>`;
        const roleCell = user.role === 'faculty'
            ? '<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-accent border border-rose-accent/10">Faculty</span>'
            : '<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-brand-soft text-brand border border-brand/10">Student</span>';
        const statusCell = user.status === 'active'
            ? '<span class="flex items-center gap-1.5 text-xs font-semibold text-green-600"><span class="w-2 h-2 bg-green-500 rounded-full"></span> Active</span>'
            : user.status === 'pending'
                ? '<span class="flex items-center gap-1.5 text-xs font-semibold text-amber-600"><span class="w-2 h-2 bg-amber-500 rounded-full"></span> Pending</span>'
                : '<span class="flex items-center gap-1.5 text-xs font-semibold text-amber-600"><span class="w-2 h-2 bg-amber-500 rounded-full"></span> Suspended</span>';
        const actionCell = user.role === 'student' && user.status === 'pending'
            ? `
                <div class="flex justify-center w-full">
                    <button
                        onclick="openUpdateModal(this)"
                        data-user-id="${user.id}"
                        data-first-name="${user.first_name ?? ''}"
                        data-middle-name="${user.middle_name ?? ''}"
                        data-last-name="${user.last_name ?? ''}"
                        data-username="${user.username ?? ''}"
                        data-email-domain="${user.email_domain ?? 'hms.edu'}"
                        data-full-name="${user.name ?? ''}"
                        data-phone-number="${user.phone_number ?? ''}"
                        data-status="${user.status ?? 'active'}"
                        data-role="${user.role ?? ''}"
                        class="text-brand bg-brand-soft hover:bg-brand/10 transition text-xs font-semibold px-3 py-2 rounded-lg inline-flex items-center justify-center gap-2 w-24"
                        aria-label="Update user"
                    >
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <button type="submit" class="text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition text-xs font-semibold px-3 py-2 rounded-lg inline-flex items-center justify-center gap-2 w-24" aria-label="Approve student">
                            <span class="iconify text-base" data-icon="mdi:check-circle-outline"></span>
                            <span>Approve</span>
                        </button>
                    </form>
                </div>
            `
            : `
                <div class="flex justify-center w-full">
                    <button onclick="openModal('updateUserModal')" class="text-brand bg-brand-soft hover:bg-brand/10 transition text-xs font-semibold px-3 py-2 rounded-lg inline-flex items-center justify-center gap-2 w-24" aria-label="Update user">
                        <span class="iconify text-base" data-icon="mdi:update"></span>
                        <span>Update</span>
                    </button>
                </div>
            `;

        return [
            nameCell,
            user.email ?? '—',
            user.phone_number ?? '—',
            roleCell,
            statusCell,
            user.joined ?? '—',
            actionCell,
        ];
    }

    function addUserRow(user) {
        if (!usersTable || seenUserIds.has(Number(user.id))) {
            return;
        }

        const rowNode = usersTable.row.add(buildUserRow(user)).node();
        if (rowNode) {
            rowNode.setAttribute('data-user-id', user.id);
            $(rowNode).prependTo($('#usersTable tbody'));
        }
        usersTable.draw(false);
        seenUserIds.add(Number(user.id));
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
        document.getElementById('updatePassword').value = '';
        document.getElementById('updatePasswordConfirm').value = '';

        document.getElementById('updatePasswordHelp').classList.add('hidden');
        document.getElementById('updatePasswordMatch').classList.add('hidden');

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

    function switchTab(role) {
        // Update active tab styles
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-brand', 'text-brand', 'bg-brand-soft');
            btn.classList.add('border-transparent', 'text-slate-500');
        });
        const active = document.getElementById('tab-' + role);
        active.classList.remove('border-transparent', 'text-slate-500');
        active.classList.add('border-brand', 'text-brand', 'bg-brand-soft');

        // Filter DataTable by role column (index 3)
        if (role === 'all') {
            usersTable.column(3).search('').draw();
        } else {
            usersTable.column(3).search(role === 'faculty' ? 'Faculty' : 'Student', false, false).draw();
        }
    }

    @if ($errors->any() && old('role'))
        openModal('createUserModal');
    @endif
</script>
@endpush
@endsection
