{{-- Change own password. Shared by the dean, faculty and student profile pages;
     posts to account.password, whose errors come back in the "password" bag. --}}
@php $pwErrors = $errors->getBag('password'); @endphp
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <form method="POST" action="{{ route('account.password') }}" class="p-6 md:p-8 space-y-6">
        @csrf
        @method('PUT')

        <div>
            <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-1">Change Password</h4>
            <p class="text-xs text-slate-400 mb-5">Enter your current password, then choose a new one of at least 8 characters.</p>

            @if (auth()->user()?->must_change_password)
                {{-- Set on a dean-created faculty account; EnsurePasswordChanged keeps them here until it clears. --}}
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 mb-5 text-sm text-amber-800 flex items-center gap-3">
                    <span class="iconify text-lg shrink-0" data-icon="mdi:shield-key-outline"></span>
                    You signed in with the temporary password sent to your Gmail. Set a new password to continue using the portal.
                </div>
            @endif

            @if (session('password_success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 mb-5 text-sm text-emerald-700 flex items-center gap-3">
                    <span class="iconify text-lg" data-icon="mdi:check-circle"></span>
                    {{ session('password_success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach ([
                    ['current_password', 'Current Password', 'current-password', 'mdi:lock-outline', 'md:col-span-2'],
                    ['password', 'New Password', 'new-password', 'mdi:lock-reset', ''],
                    ['password_confirmation', 'Confirm New Password', 'new-password', 'mdi:lock-check-outline', ''],
                ] as [$pwField, $pwLabel, $pwAutocomplete, $pwIcon, $pwSpan])
                    <div class="{{ $pwSpan }}">
                        <label for="pw_{{ $pwField }}" class="block text-xs font-semibold text-slate-600 mb-2">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="iconify text-brand" data-icon="{{ $pwIcon }}"></span>
                                {{ $pwLabel }}
                            </span>
                        </label>
                        <div class="relative">
                            <input id="pw_{{ $pwField }}" name="{{ $pwField }}" type="password" autocomplete="{{ $pwAutocomplete }}"
                                @if ($pwField !== 'current_password') minlength="8" @endif
                                class="w-full h-11 pl-4 pr-11 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 outline-none focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/15 transition"
                                required>
                            {{-- Show / hide this one field. The icons sit in wrappers because
                                 Iconify swaps each span for an svg; the wrappers are what toggle. --}}
                            <button type="button" aria-label="Show password" aria-controls="pw_{{ $pwField }}" aria-pressed="false"
                                onclick="const i = this.previousElementSibling, show = i.type === 'password'; i.type = show ? 'text' : 'password'; this.setAttribute('aria-pressed', show); this.setAttribute('aria-label', show ? 'Hide password' : 'Show password'); this.querySelectorAll('[data-pw-eye]').forEach(e => e.classList.toggle('hidden'));"
                                class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-brand/20 transition">
                                <span data-pw-eye class="flex"><span class="iconify text-lg" data-icon="mdi:eye-off-outline"></span></span>
                                <span data-pw-eye class="hidden"><span class="iconify text-lg" data-icon="mdi:eye-outline"></span></span>
                            </button>
                        </div>
                        @if ($pwErrors->has($pwField))
                            <p class="text-xs text-red-600 mt-1">{{ $pwErrors->first($pwField) }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-2 border-t border-slate-100">
            <button type="submit"
                class="w-full sm:w-auto h-11 px-6 brand-gradient text-white text-sm font-bold rounded-xl shadow-lg shadow-brand/20 hover:scale-[1.02] transition flex items-center justify-center gap-2">
                <span class="iconify" data-icon="mdi:lock-reset"></span>
                Change Password
            </button>
        </div>
    </form>
</div>
