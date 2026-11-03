{{-- Change own password. Shared by the dean, faculty and student profile pages:
     partials.profile-page puts it in its Change Password pop-up and owns the
     pf- styles. Posts to account.password, whose errors come back in the
     "password" bag. The rules list mirrors AuthController::updatePassword. --}}
@php $pwErrors = $errors->getBag('password'); @endphp
<form id="change-password" method="POST" action="{{ route('account.password') }}" class="pf-sheet">
    @csrf
    @method('PUT')
    <div class="pf-mhead">
        <span class="pf-icon"><span class="iconify" data-icon="mdi:lock-reset"></span></span>
        <div>
            <p class="pf-h" id="pfPasswordTitle">Change Password</p>
            <p class="pf-sub">Confirm your current password, then choose a new one.</p>
        </div>
        <button type="button" class="pf-dialog-x" aria-label="Close" onclick="this.closest('dialog').close()"><span class="iconify" data-icon="mdi:close"></span></button>
    </div>
    <div class="pf-mbody">
        @if (auth()->user()?->must_change_password)
            {{-- Set on an account created with a temporary password; EnsurePasswordChanged keeps them here until it clears. --}}
            <div class="pf-flash is-warn">
                <span class="iconify" data-icon="mdi:shield-key-outline"></span>
                You signed in with the temporary password sent to your email. Set a new password to continue using the portal.
            </div>
        @endif

        @if (session('password_success'))
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

        @foreach ([
            ['current_password', 'Current password', 'current-password'],
            ['password', 'New password', 'new-password'],
            ['password_confirmation', 'Confirm new password', 'new-password'],
        ] as [$pwField, $pwLabel, $pwAutocomplete])
            @if ($pwField === 'password')
                <div class="pf-split"></div>
            @endif
            <div class="pf-field">
                <label for="pw_{{ $pwField }}">{{ $pwLabel }}</label>
                <div class="pf-input has-lead has-eye">
                    <span class="pf-lead"><span class="iconify" data-icon="{{ $pwField === 'current_password' ? 'mdi:lock-outline' : 'mdi:key-outline' }}"></span></span>
                    <input id="pw_{{ $pwField }}" name="{{ $pwField }}" type="password" autocomplete="{{ $pwAutocomplete }}"
                        @if ($pwField !== 'current_password') minlength="8" @endif
                        @if ($loop->first) autofocus @endif
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

        <ul class="pf-rules" aria-live="polite">
            <li data-pw-rule="length">At least 8 characters</li>
            <li data-pw-rule="different">Different from your current password</li>
            <li data-pw-rule="match">Both new passwords match</li>
        </ul>
    </div>
    <div class="pf-mfoot">
        <button type="button" class="pf-btn is-ghost" onclick="this.closest('dialog').close()">Cancel</button>
        <button type="submit" class="pf-btn is-primary">
            <span class="iconify" data-icon="mdi:lock-check-outline"></span>
            Update Password
        </button>
    </div>
</form>
<script>
    // Tick the rules off as they are met, and stop a mismatched confirmation
    // before it goes to the server.
    (function () {
        const form = document.getElementById('change-password');
        if (!form) return;
        const { current_password: current, password: next, password_confirmation: confirm } = form.elements;
        const rules = {
            length: () => next.value.length >= 8,
            different: () => next.value !== '' && next.value !== current.value,
            match: () => confirm.value !== '' && confirm.value === next.value,
        };
        form.addEventListener('input', function () {
            form.querySelectorAll('[data-pw-rule]').forEach(li => li.classList.toggle('is-ok', rules[li.dataset.pwRule]()));
            confirm.setCustomValidity(confirm.value && confirm.value !== next.value ? 'The new passwords do not match.' : '');
        });
    })();
</script>
