{{-- Change own password. Shared by the dean, faculty and student profile pages
     through partials.profile-page, whose pf- styles it uses; posts to
     account.password, whose errors come back in the "password" bag. --}}
@php $pwErrors = $errors->getBag('password'); @endphp
<form id="change-password" style="scroll-margin-top: 1.5rem; --i: 2" method="POST" action="{{ route('account.password') }}" class="pf-card">
    @csrf
    @method('PUT')
    <div class="pf-head">
        <div class="pf-title">
            <span class="pf-icon"><span class="iconify" data-icon="mdi:lock-reset"></span></span>
            <div>
                <p class="pf-h">Change Password</p>
                <p class="pf-sub">Your current password, then a new one of at least 8 characters.</p>
            </div>
        </div>
        <button type="submit" class="pf-btn">
            <span class="iconify" data-icon="mdi:lock-check-outline"></span>
            Change Password
        </button>
    </div>
    <div class="pf-body">
        @if (auth()->user()?->must_change_password)
            {{-- Set on an account created with a temporary password; EnsurePasswordChanged keeps them here until it clears. --}}
            <div class="pf-flash is-warn">
                <span class="iconify" data-icon="mdi:shield-key-outline"></span>
                You signed in with the temporary password sent to your email. Set a new password to continue using the portal.
            </div>
        @endif

        @if (session('password_success'))
            <div class="pf-flash" role="status">
                <span class="iconify" data-icon="mdi:check-circle-outline"></span>
                {{ session('password_success') }}
            </div>
            <script>
                window.addEventListener('load', function () {
                    Swal.fire({
                        icon: 'success',
                        title: 'Password Changed',
                        text: @json(session('password_success')),
                        // heightAuto would let the page grow behind the dialog.
                        heightAuto: false,
                    });
                });
            </script>
        @endif

        <div class="pf-grid is-3">
            @foreach ([
                ['current_password', 'Current password', 'current-password'],
                ['password', 'New password', 'new-password'],
                ['password_confirmation', 'Confirm new password', 'new-password'],
            ] as [$pwField, $pwLabel, $pwAutocomplete])
                <div class="pf-field">
                    <label for="pw_{{ $pwField }}">{{ $pwLabel }}</label>
                    <div class="pf-input has-eye">
                        <input id="pw_{{ $pwField }}" name="{{ $pwField }}" type="password" autocomplete="{{ $pwAutocomplete }}"
                            @if ($pwField !== 'current_password') minlength="8" @endif
                            required>
                        {{-- Show / hide this one field. The icons sit in wrappers because
                             Iconify swaps each span for an svg; the wrappers are what toggle. --}}
                        <button type="button" class="pf-eye" aria-label="Show password" aria-controls="pw_{{ $pwField }}" aria-pressed="false"
                            onclick="const i = this.previousElementSibling, show = i.type === 'password'; i.type = show ? 'text' : 'password'; this.setAttribute('aria-pressed', show); this.setAttribute('aria-label', show ? 'Hide password' : 'Show password'); this.querySelectorAll('[data-pw-eye]').forEach(e => e.hidden = !e.hidden);">
                            <span data-pw-eye><span class="iconify" data-icon="mdi:eye-off-outline"></span></span>
                            <span data-pw-eye hidden><span class="iconify" data-icon="mdi:eye-outline"></span></span>
                        </button>
                    </div>
                    @if ($pwErrors->has($pwField))
                        <p class="pf-err">{{ $pwErrors->first($pwField) }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</form>
