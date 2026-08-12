<aside class="h-full flex flex-col text-zinc-200">
    <div class="px-5 py-5 flex-1 overflow-y-auto">
        <div class="space-y-4">
            @foreach($groupMembers as $member)
                @php
                    $isMe = (int) ($member->id ?? 0) === (int) (auth()->id() ?? 0);
                    $rolesLabel = implode(', ', array_map(fn ($r) => ucwords(str_replace('_', ' ', $r)), $member->roles ?? []));
                @endphp
                <div class="flex items-start gap-3">
                    <div class="pt-1.5">
                        <span
                            data-member-online="{{ $member->id }}"
                            class="inline-block w-2.5 h-2.5 rounded-full {{ $isMe ? 'bg-emerald-400' : 'bg-zinc-600' }}"
                        ></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-white truncate">
                            {{ $member->name }}
                            @if($isMe)<span class="text-[10px] text-cyan-400 font-semibold ml-1">YOU</span>@endif
                        </p>
                        <p class="text-[11px] text-zinc-500 mt-0.5 truncate">{{ $rolesLabel !== '' ? $rolesLabel : 'No role' }}</p>
                        <p class="text-[10px] mt-1 {{ $isMe ? 'text-emerald-400' : 'text-zinc-600' }}" data-member-online-label>{{ $isMe ? 'Online' : 'Offline' }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        @if(in_array($builderRole ?? null, ['front_desk', 'room_management', 'restaurant_management', 'maintenance', 'housekeeping'], true))
        <div class="mt-5 pt-4 border-t border-zinc-800">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500 mb-2.5">Staff Tools</p>
            <div class="space-y-1.5">
                @if(($builderRole ?? null) === 'front_desk')
                <a href="{{ route('students.frontdesk.verify-guest') }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.frontdesk.verify-guest') ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-user-check text-[13px] text-emerald-400"></i> Verify Guest
                </a>
                <a href="{{ route('students.frontdesk.complaints') }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.frontdesk.complaints') ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-comment-dots text-[13px] text-emerald-400"></i> Complaints
                </a>
                <a href="{{ route('students.frontdesk.dine-in') }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.frontdesk.dine-in') ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-utensils text-[13px] text-emerald-400"></i> Dine-in Tables
                </a>
                <a href="{{ route('students.frontdesk.room-service') }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.frontdesk.room-service') ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-bell-concierge text-[13px] text-emerald-400"></i> Room Service
                </a>
                @elseif(($builderRole ?? null) === 'maintenance')
                <a href="{{ route('students.maintenance.complaints') }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.maintenance.complaints') ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-screwdriver-wrench text-[13px] text-emerald-400"></i> Complaints / Concerns
                </a>
                @elseif(($builderRole ?? null) === 'housekeeping')
                <a href="{{ route('students.housekeeping.inspections') }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.housekeeping.inspections') ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-broom text-[13px] text-emerald-400"></i> Room Inspections
                </a>
                <a href="{{ route('students.housekeeping.complaints') }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.housekeeping.complaints') ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-comment-dots text-[13px] text-emerald-400"></i> Complaints / Concerns
                </a>
                @elseif(($builderRole ?? null) === 'room_management')
                <a href="{{ route('students.roommanagement.manage', ['nav' => 'manage-room']) }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.roommanagement.manage') && request()->query('nav', 'manage-room') === 'manage-room' ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-bed text-[13px] text-emerald-400"></i> Manage Room
                </a>
                <a href="{{ route('students.roommanagement.manage', ['nav' => 'guest-details']) }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.roommanagement.manage') && request()->query('nav') === 'guest-details' ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-user text-[13px] text-emerald-400"></i> Guest Details
                </a>
                <a href="{{ route('students.roommanagement.manage', ['nav' => 'rooms']) }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.roommanagement.manage') && request()->query('nav') === 'rooms' ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-calendar-days text-[13px] text-emerald-400"></i> Room Availability
                </a>
                @elseif(($builderRole ?? null) === 'restaurant_management')
                <a href="{{ route('students.restaurant.manage', ['nav' => 'manage-menu']) }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.restaurant.manage') && request()->query('nav', 'manage-menu') === 'manage-menu' ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-utensils text-[13px] text-emerald-400"></i> Manage Menu
                </a>
                <a href="{{ route('students.restaurant.manage', ['nav' => 'manage-tables']) }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.restaurant.manage') && request()->query('nav') === 'manage-tables' ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-chair text-[13px] text-emerald-400"></i> Manage Tables
                </a>
                <a href="{{ route('students.restaurant.manage', ['nav' => 'orders']) }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.restaurant.manage') && request()->query('nav') === 'orders' ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-receipt text-[13px] text-emerald-400"></i> Orders
                </a>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="px-5 py-4 border-t border-zinc-800 shrink-0 bg-zinc-950/80">
        <a id="backToTasksBtn" href="{{ route('students.dashboard', ['section' => 'tasks']) }}"
           onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
           class="w-full inline-flex items-center justify-center gap-2 h-10 px-4 rounded-xl text-xs font-bold
                  bg-zinc-900 text-zinc-200 border border-zinc-700 hover:border-cyan-500/40 hover:text-white hover:bg-zinc-800 transition"
           title="Back to Tasks">
            <i class="fas fa-arrow-left text-[11px]"></i>
            Back to Tasks
        </a>
    </div>
</aside>
