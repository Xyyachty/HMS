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
