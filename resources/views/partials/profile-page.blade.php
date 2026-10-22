{{-- My Profile, shared by the dean, faculty and student portals.

     One screen, no scrolling on a laptop: who you are on the left (photo, name,
     the record the school keeps), what you can change on the right (details,
     then password). Styled with its own pf- classes, written out in hex: the
     faculty portal runs on the frozen Tailwind build and a palette file that
     remaps stock colours, and the student dashboard re-points several utilities
     inside #profile-section. No class here contains "text-", so .ink-all leaves
     it alone.

     Expects:
       $pfUser        the signed-in user
       $pfRoleLabel   "Faculty Account", "Student", ...
       $pfAction      URL the details form PUTs to
       $pfPhoneValue  the phone number to prefill
       $pfPhPhone     true for the +63 mobile input the dean and faculty forms validate
       $pfRecordTitle heading over the read-only rows
       $pfRecords     [[label, value], ...]
       $pfRoles       optional list of role names, shown as chips --}}
@php
    $pfName = trim(implode(' ', array_filter([$pfUser?->first_name, $pfUser?->last_name]))) ?: ($pfUser?->name ?? 'User');
    $pfRoles = $pfRoles ?? null;
    $pfPhPhone = $pfPhPhone ?? false;
@endphp
<style>
    .pf { --ink: #181818; --muted: #6B6460; --line: #E4E2E0; --soft: #F5F4F3; --wine: #7B1730; --wine-deep: #4A0D1C; --btn: #4A4643;
          display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); color: var(--ink); max-width: 75rem; margin: 0 auto; }
    @media (min-width: 1024px) { .pf { grid-template-columns: 19rem minmax(0, 1fr); align-items: start; } }
    .pf *, .pf *::before, .pf *::after { box-sizing: border-box; }
    .pf :where(p, dl, dd, form) { margin: 0; }
    .pf :is(button, input) { font-family: inherit; }
    .pf button { border: 0; cursor: pointer; }
    .pf-card { background: #fff; border: 1px solid var(--line); border-radius: 1.25rem; box-shadow: 0 18px 40px -24px rgba(74, 13, 28, .18); overflow: hidden;
               animation: pf-rise .45s cubic-bezier(.16, 1, .3, 1) both; animation-delay: calc(var(--i, 0) * 70ms); }
    @keyframes pf-rise { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) { .pf-card { animation: none; } }
    .pf-main { display: grid; gap: 1rem; min-width: 0; }

    /* Identity card */
    .pf-band { height: 4.75rem; background: var(--wine-deep);
               background-image: radial-gradient(120% 140% at 100% 0%, rgba(201, 164, 92, .28), transparent 55%), linear-gradient(135deg, var(--wine), var(--wine-deep)); }
    .pf-who { padding: 0 1.25rem 1.1rem; margin-top: -2.6rem; display: flex; flex-direction: column; align-items: flex-start; }
    .pf-avatar { position: relative; width: 5.25rem; height: 5.25rem; }
    .pf-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 1.1rem; border: 3px solid #fff; background: var(--soft); box-shadow: 0 10px 24px -12px rgba(24, 24, 24, .45); }
    .pf-cam { position: absolute; right: -.35rem; bottom: -.35rem; width: 2rem; height: 2rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center;
              background: var(--btn); color: #fff; border: 2px solid #fff; cursor: pointer; font-size: 1rem; transition: transform .2s cubic-bezier(.16, 1, .3, 1), background-color .2s; }
    .pf-cam:hover { background: var(--ink); transform: scale(1.06); }
    .pf-name { margin-top: 1rem; font-size: 1.125rem; font-weight: 800; letter-spacing: -.01em; line-height: 1.2; }
    .pf-mail { margin-top: .2rem; font-size: .75rem; color: var(--muted); word-break: break-all; }
    .pf-pill { margin-top: .75rem; display: inline-flex; align-items: center; gap: .35rem; padding: .2rem .6rem; border-radius: 9999px;
               font-size: .625rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; background: #FBEEE9; color: var(--wine); }
    .pf-photo-note { margin-top: .85rem; font-size: .6875rem; color: var(--muted); line-height: 1.45; }
    .pf-remove { margin-top: .35rem; display: inline-flex; align-items: center; gap: .4rem; font-size: .6875rem; font-weight: 700; color: var(--wine); cursor: pointer; }
    .pf-remove input { accent-color: var(--wine); }
    .pf-record { border-top: 1px solid var(--line); padding: .85rem 1.25rem 1rem; }
    .pf-kicker { font-size: .625rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); margin-bottom: .45rem; }
    .pf-rows { display: grid; gap: .05rem; }
    .pf-row { display: flex; align-items: baseline; justify-content: space-between; gap: .75rem; padding: .35rem 0; font-size: .75rem; }
    .pf-row dt { color: var(--muted); font-weight: 600; white-space: nowrap; }
    .pf-row dd { font-weight: 700; text-align: right; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pf-chips { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: .35rem; }
    .pf-chip { padding: .15rem .55rem; border-radius: 9999px; background: var(--soft); border: 1px solid var(--line); font-size: .625rem; font-weight: 700; }

    /* Form cards */
    .pf-head { display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap; padding: .85rem 1.25rem; border-bottom: 1px solid var(--line); }
    .pf-title { display: flex; align-items: center; gap: .65rem; min-width: 0; }
    .pf-icon { width: 2.1rem; height: 2.1rem; border-radius: .7rem; background: var(--soft); color: var(--btn); display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; flex: 0 0 auto; }
    .pf-h { font-size: .875rem; font-weight: 800; line-height: 1.2; }
    .pf-sub { font-size: .6875rem; color: var(--muted); margin-top: .1rem; }
    .pf-body { padding: 1rem 1.25rem 1.15rem; display: grid; gap: .85rem; }
    .pf-grid { display: grid; gap: .85rem; grid-template-columns: minmax(0, 1fr); }
    @media (min-width: 640px) { .pf-grid.is-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); } .pf-grid.is-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .pf-field label { display: block; font-size: .625rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); margin-bottom: .35rem; }
    .pf-field label small { font-size: .625rem; font-weight: 600; letter-spacing: 0; text-transform: none; color: #A39C97; }
    .pf-input { position: relative; }
    .pf-input input { width: 100%; height: 2.5rem; padding: 0 .85rem; border-radius: .75rem; border: 1px solid var(--line); background: var(--soft);
                      font-size: .8125rem; color: var(--ink); outline: none; transition: border-color .2s, background-color .2s, box-shadow .2s; }
    .pf-input input:focus { border-color: #8A817A; background: #fff; box-shadow: 0 0 0 3px rgba(123, 23, 48, .1); }
    .pf-input .pf-prefix { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); font-size: .75rem; font-weight: 700; color: var(--muted); pointer-events: none; }
    .pf-input.has-prefix input { padding-left: 2.75rem; }
    .pf-input.has-eye input { padding-right: 2.6rem; }
    .pf-eye { position: absolute; right: .35rem; top: 50%; transform: translateY(-50%); width: 2rem; height: 2rem; border-radius: .55rem; display: inline-flex; align-items: center; justify-content: center; color: #8A817A; }
    .pf-eye:hover { color: var(--ink); }
    .pf-eye [data-pw-eye] { display: flex; font-size: 1.05rem; }
    .pf-eye [data-pw-eye][hidden] { display: none; }
    .pf-err { margin-top: .3rem; font-size: .6875rem; font-weight: 600; color: #B42318; }
    .pf-btn { display: inline-flex; align-items: center; gap: .4rem; height: 2.25rem; padding: 0 1rem; border-radius: .75rem; background: var(--btn); color: #fff;
              font-size: .75rem; font-weight: 800; white-space: nowrap; transition: background-color .2s, transform .15s; }
    .pf-btn:hover { background: #15803D; }
    .pf-btn:active { transform: scale(.98); }
    .pf-flash { grid-column: 1 / -1; display: flex; align-items: center; gap: .6rem; padding: .7rem 1rem; border-radius: .9rem; font-size: .8125rem; font-weight: 600;
                background: #F0FDF4; border: 1px solid #BBF7D0; color: #15803D; }
    .pf-flash.is-warn { background: #FBF3E0; border-color: #E9D3A0; color: #7A5322; }
</style>

<div class="pf">
    @if (session('success'))
        <div class="pf-flash" role="status">
            <span class="iconify" data-icon="mdi:check-circle-outline"></span>
            {{ session('success') }}
        </div>
    @endif

    {{-- Who you are --}}
    <aside class="pf-card" style="--i: 0">
        <div class="pf-band"></div>
        <div class="pf-who">
            <div class="pf-avatar">
                <img id="pfAvatarImg" src="{{ $pfUser?->avatar_url }}" alt="{{ $pfName }}">
                {{-- The file input sits out here, beside the photo it changes, and
                     joins the details form through form=. --}}
                <label for="pfAvatar" class="pf-cam" title="Change photo" aria-label="Change photo">
                    <span class="iconify" data-icon="mdi:camera-outline"></span>
                </label>
                <input id="pfAvatar" type="file" name="avatar" form="pfProfileForm" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
            </div>
            <p class="pf-name">{{ $pfName }}</p>
            <p class="pf-mail">{{ $pfUser?->email }}</p>
            <span class="pf-pill"><span class="iconify" data-icon="mdi:shield-account-outline"></span>{{ $pfRoleLabel }}</span>
            <p class="pf-photo-note" id="pfPhotoNote">Tap the camera to change your photo. JPG, PNG, WEBP or GIF, up to 2 MB. Saved with your details.</p>
            @if ($pfUser?->avatar)
                <label class="pf-remove">
                    <input type="checkbox" name="remove_avatar" value="1" form="pfProfileForm">
                    Remove current photo
                </label>
            @endif
            @error('avatar')<p class="pf-err">{{ $message }}</p>@enderror
        </div>

        @if (!empty($pfRecords) || !empty($pfRoles))
            <div class="pf-record">
                <p class="pf-kicker">{{ $pfRecordTitle }}</p>
                <dl class="pf-rows">
                    @foreach ($pfRecords as [$pfLabel, $pfValue])
                        <div class="pf-row">
                            <dt>{{ $pfLabel }}</dt>
                            <dd title="{{ $pfValue }}">{{ $pfValue ?: '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
                @if (is_array($pfRoles))
                    <div class="pf-chips">
                        @forelse ($pfRoles as $pfRole)
                            <span class="pf-chip">{{ $pfRole }}</span>
                        @empty
                            <span class="pf-chip">No role assigned</span>
                        @endforelse
                    </div>
                @endif
            </div>
        @endif
    </aside>

    <div class="pf-main">
        {{-- Details --}}
        <form id="pfProfileForm" method="POST" action="{{ $pfAction }}" enctype="multipart/form-data" class="pf-card" style="--i: 1">
            @csrf
            @method('PUT')
            <div class="pf-head">
                <div class="pf-title">
                    <span class="pf-icon"><span class="iconify" data-icon="mdi:account-edit-outline"></span></span>
                    <div>
                        <p class="pf-h">Account Details</p>
                        <p class="pf-sub">How your name appears across the portal.</p>
                    </div>
                </div>
                <button type="submit" class="pf-btn">
                    <span class="iconify" data-icon="mdi:content-save-outline"></span>
                    Save Changes
                </button>
            </div>
            <div class="pf-body">
                <div class="pf-grid is-3">
                    @foreach ([
                        ['first_name', 'First name', true],
                        ['middle_name', 'Middle name', false],
                        ['last_name', 'Last name', true],
                    ] as [$pfField, $pfFieldLabel, $pfRequired])
                        <div class="pf-field">
                            <label for="pf_{{ $pfField }}">{{ $pfFieldLabel }} @unless($pfRequired)<small>(optional)</small>@endunless</label>
                            <div class="pf-input">
                                <input id="pf_{{ $pfField }}" name="{{ $pfField }}" type="text" value="{{ old($pfField, $pfUser?->{$pfField}) }}" @if($pfRequired) required @endif>
                            </div>
                            @error($pfField)<p class="pf-err">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
                <div class="pf-grid is-2">
                    <div class="pf-field">
                        <label for="pf_email">Email</label>
                        <div class="pf-input">
                            <input id="pf_email" name="email" type="email" value="{{ old('email', $pfUser?->email) }}" autocomplete="email" required>
                        </div>
                        @error('email')<p class="pf-err">{{ $message }}</p>@enderror
                    </div>
                    <div class="pf-field">
                        <label for="pf_phone">Phone <small>(optional)</small></label>
                        @if ($pfPhPhone)
                            <div class="pf-input has-prefix">
                                <span class="pf-prefix">+63</span>
                                <input id="pf_phone" name="phone_number" type="text" value="{{ $pfPhoneValue }}"
                                       inputmode="numeric" autocomplete="tel-national" pattern="9[0-9]{9}" title="10 digits after +63, starting with 9 (e.g. 9123456789)" data-ph-phone
                                       placeholder="912 345 6789">
                            </div>
                        @else
                            <div class="pf-input">
                                <input id="pf_phone" name="phone_number" type="text" value="{{ $pfPhoneValue }}" autocomplete="tel">
                            </div>
                        @endif
                        @error('phone_number')<p class="pf-err">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </form>

        @include('partials.change-password')
    </div>
</div>

<script>
    // Show the picked photo straight away; it is uploaded with Save Changes.
    document.getElementById('pfAvatar')?.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) return;
        document.getElementById('pfAvatarImg').src = URL.createObjectURL(file);
        const note = document.getElementById('pfPhotoNote');
        if (note) note.textContent = 'New photo picked. Press Save Changes to keep it.';
    });
</script>
