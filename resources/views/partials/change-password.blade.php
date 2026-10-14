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
                        <input id="pw_{{ $pwField }}" name="{{ $pwField }}" type="password" autocomplete="{{ $pwAutocomplete }}"
                            @if ($pwField !== 'current_password') minlength="8" @endif
                            class="w-full h-11 px-4 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 outline-none focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/15 transition"
                            required>
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
