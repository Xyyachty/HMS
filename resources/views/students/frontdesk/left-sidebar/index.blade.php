<aside class="h-full flex flex-col text-zinc-200">
    <div class="px-5 py-5 border-b border-zinc-800/80">
        <div class="flex items-start justify-between gap-3 mb-4">
            <div>
                <h2 class="text-sm font-bold text-white tracking-wide uppercase">Team Members</h2>
                <p class="text-[11px] text-zinc-500 mt-1">Live presence synced across the group</p>
            </div>
            <div class="w-9 h-9 rounded-xl bg-cyan-500/15 border border-cyan-400/20 flex items-center justify-center text-cyan-300">
                <i class="fas fa-users text-sm"></i>
            </div>
        </div>

        <div class="settings-card mb-0">
            <span class="settings-label">Group</span>
            <p class="text-lg font-bold text-white">{{ $groupName ?? ($group->name ?? 'Team') }}</p>
            <p class="text-[11px] text-zinc-500 mt-1">
                @if($canEditTemplate ?? false)
                    You can edit the hotel template
                @else
                    View-only — {{ $roleLabelFull ?? 'assigned' }} role required to edit
                @endif
            </p>
        </div>
    </div>

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
    </div>
</aside>
