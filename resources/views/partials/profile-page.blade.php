{{-- My Profile, shared by the dean, faculty and student portals.

     One screen, no scrolling on a laptop: who you are on the left (photo, name,
     the record the school keeps), Recent Activity on the right. Account Details
     and Change Password open in pop-ups from buttons on the identity card. Styled with its own pf- classes, written out in hex: the
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
    // A pop-up that came back with errors, or a password that must be replaced
    // before anything else, opens again on load rather than hiding the reason.
    $pfOpenDetails = $errors->hasAny(['first_name', 'middle_name', 'last_name', 'email', 'phone_number', 'avatar']);
    $pfOpenPassword = $errors->getBag('password')->any() || $pfUser?->must_change_password;
    $pfLog = $pfUser
        ? \App\Models\ActivityLog::where('user_id', $pfUser->user_id)->latest('created_at')->limit(20)->get()
        : collect();
    $pfLastLogin = optional($pfLog->firstWhere('activity', \App\Models\ActivityLog::LOGIN)?->created_at)->timezone('Asia/Manila');
@endphp
<style>
    .pf { --ink: #181818; --muted: #6B6460; --line: #E4E2E0; --soft: #F5F4F3; --wine: #7B1730; --wine-deep: #4A0D1C; --btn: #4A4643;
          display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); color: var(--ink); max-width: 75rem; margin: 0 auto; }
    @media (min-width: 1024px) { .pf { grid-template-columns: 19rem minmax(0, 1fr); grid-template-rows: minmax(0, 1fr); align-items: stretch; } .pf.has-flash { grid-template-rows: auto minmax(0, 1fr); } }
    .pf *, .pf *::before, .pf *::after { box-sizing: border-box; }
    .pf :where(p, dl, dd, form) { margin: 0; }
    .pf :is(button, input) { font-family: inherit; }
    .pf button { border: 0; cursor: pointer; }
    .pf-card { background: #fff; border: 1px solid var(--line); border-radius: 1.25rem; box-shadow: 0 18px 40px -24px rgba(74, 13, 28, .18); overflow: hidden;
               animation: pf-rise .45s cubic-bezier(.16, 1, .3, 1) both; animation-delay: calc(var(--i, 0) * 70ms); }
    @keyframes pf-rise { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) { .pf-card { animation: none; } }
    .pf-main { display: flex; flex-direction: column; gap: 1rem; min-width: 0; min-height: 0; }
    aside.pf-card { display: flex; flex-direction: column; }
    .pf-foot { margin-top: auto; border-top: 1px solid var(--line); padding: .8rem 1.25rem; display: flex; align-items: center; gap: .6rem; font-size: .6875rem; color: var(--muted); background: #FAFAF9; }
    .pf-foot b { color: var(--ink); font-weight: 800; }

    /* Recent activity: on a wide screen it takes whatever height is left, so the
       page ends at the bottom of the window instead of in grey space; its list
       scrolls inside. Narrow screens stack and cap it instead. The 16rem floor
       keeps about five entries in view on a short laptop screen (1366x768 at
       100%); the page scrolls to reach them rather than squeezing the list. */
    .pf-activity { display: flex; flex-direction: column; min-height: 0; }
    .pf-feed { list-style: none; margin: 0; padding: .35rem 1.25rem .6rem; overflow-y: auto; max-height: 18rem; }
    @media (min-width: 1024px) { .pf-activity { flex: 1 1 0; } .pf-feed { flex: 1 1 0; min-height: 16rem; max-height: none; } }
    .pf-feed li { display: flex; align-items: flex-start; gap: .75rem; padding: .6rem 0; border-bottom: 1px solid #F0EEEC; }
    .pf-feed li:last-child { border-bottom: 0; }
    .pf-dot { width: 1.9rem; height: 1.9rem; border-radius: 9999px; flex: 0 0 auto; display: inline-flex; align-items: center; justify-content: center; background: var(--soft); color: var(--btn); font-size: .95rem; }
    .pf-dot.is-in { background: #F0FDF4; color: #15803D; }
    .pf-dot.is-out { background: #FBEEE9; color: var(--wine); }
    .pf-what { flex: 1 1 auto; min-width: 0; }
    .pf-what b { display: block; font-size: .75rem; font-weight: 800; }
    .pf-what span { display: block; font-size: .6875rem; color: var(--muted); margin-top: .1rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pf-when { flex: 0 0 auto; font-size: .6875rem; color: var(--muted); white-space: nowrap; text-align: right; }
    .pf-when small { display: block; font-size: .625rem; color: #A39C97; }
    .pf-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .35rem; padding: 1.5rem; text-align: center; font-size: .75rem; color: var(--muted); }
    .pf-empty .iconify { font-size: 1.6rem; color: #C9C3BE; }

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
    /* Plain label, not a chip: a filled pill read as a button. */
    .pf-pill { margin-top: .75rem; display: inline-flex; align-items: center; gap: .35rem;
               font-size: .625rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #A16207; }
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
    .pf-eye { position: absolute; right: .35rem; top: 50%; transform: translateY(-50%); width: 2rem; height: 2rem; border-radius: .55rem; background: transparent; display: inline-flex; align-items: center; justify-content: center; color: #8A817A; }
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

    .pf-actions { display: grid; gap: .5rem; padding: 0 1.25rem 1.1rem; }
    .pf-actions .pf-btn { justify-content: center; height: 2.5rem; }
    .pf-btn.is-ghost { background: #fff; color: var(--ink); border: 1px solid var(--line); }
    .pf-btn.is-ghost:hover { background: var(--soft); }
    .pf-btn.is-primary { background: var(--wine); }
    .pf-btn.is-primary:hover { background: var(--wine-deep); }

    /* Pop-ups: native dialogs, so Esc, focus trapping and the top layer come
       from the browser. The sheet scrolls its body, never its header or the
       buttons. */
    dialog.pf-dialog { padding: 0; border: 0; background: transparent; color: var(--ink); overflow: visible;
                       width: min(40rem, calc(100vw - 2rem)); max-height: calc(100dvh - 2rem); }
    dialog.pf-dialog.is-narrow { width: min(30rem, calc(100vw - 2rem)); }
    dialog.pf-dialog::backdrop { background: rgba(28, 8, 14, .55); backdrop-filter: blur(3px); }
    .pf-sheet { display: flex; flex-direction: column; max-height: calc(100dvh - 2rem); background: #fff; border-radius: 1.25rem; overflow: hidden;
                box-shadow: 0 30px 70px -20px rgba(74, 13, 28, .5); }
    dialog.pf-dialog[open] .pf-sheet { animation: pf-pop .3s cubic-bezier(.16, 1, .3, 1) both; }
    @keyframes pf-pop { from { opacity: 0; transform: translateY(14px) scale(.98); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) { dialog.pf-dialog[open] .pf-sheet { animation: none; } }
    .pf-mhead { position: relative; flex: 0 0 auto; display: flex; align-items: center; gap: .9rem; padding: 1.25rem 3.75rem 1.25rem 1.5rem; color: #fff;
                background: var(--wine-deep); background-image: radial-gradient(120% 160% at 100% 0%, rgba(201, 164, 92, .3), transparent 55%), linear-gradient(135deg, var(--wine), var(--wine-deep)); }
    .pf-mhead .pf-icon { width: 2.75rem; height: 2.75rem; border-radius: .9rem; font-size: 1.35rem; background: rgba(255, 255, 255, .12); color: #E9C98A; border: 1px solid rgba(255, 255, 255, .2); }
    .pf-mhead .pf-h { font-size: 1.05rem; }
    .pf-mhead .pf-sub { font-size: .75rem; color: rgba(255, 255, 255, .75); }
    .pf-dialog-x { position: absolute; top: 50%; right: 1rem; transform: translateY(-50%); width: 2.25rem; height: 2.25rem; border-radius: 9999px;
                   display: inline-flex; align-items: center; justify-content: center; background: transparent; color: rgba(255, 255, 255, .8); font-size: 1.2rem; transition: background-color .2s; }
    .pf-dialog-x:hover { background: rgba(255, 255, 255, .14); color: #fff; }
    .pf-mbody { flex: 1 1 auto; overflow-y: auto; padding: 1.35rem 1.5rem 1.5rem; display: grid; gap: 1.15rem; align-content: start; }
    .pf-mbody .pf-kicker { margin-bottom: .6rem; }
    .pf-mbody .pf-field label { font-size: .75rem; font-weight: 700; letter-spacing: 0; text-transform: none; color: var(--ink); }
    .pf-mbody .pf-field label small { font-size: .6875rem; }
    .pf-mbody .pf-input input { height: 2.75rem; font-size: .875rem; }
    .pf-mfoot { flex: 0 0 auto; display: flex; justify-content: flex-end; gap: .6rem; padding: .9rem 1.5rem; border-top: 1px solid var(--line); background: #FAFAF9; }
    .pf-mfoot .pf-btn { height: 2.5rem; padding: 0 1.15rem; }
    @media (max-width: 480px) { .pf-mfoot .pf-btn { flex: 1 1 0; justify-content: center; } }
    .pf-split { height: 1px; background: var(--line); }

    /* An icon at the start of a field. */
    .pf-lead { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); display: flex; font-size: 1.05rem; color: #A39C97; pointer-events: none; transition: color .2s; }
    .pf-input.has-lead input { padding-left: 2.5rem; }
    .pf-input:focus-within .pf-lead { color: var(--wine); }

    /* Photo row at the top of Account Details. */
    .pf-photo { display: flex; align-items: center; gap: 1rem; padding: .85rem; border: 1px solid var(--line); border-radius: 1rem; background: var(--soft); }
    .pf-photo img { width: 3.5rem; height: 3.5rem; flex: 0 0 auto; object-fit: cover; border-radius: .9rem; border: 2px solid #fff; background: #fff; box-shadow: 0 6px 16px -10px rgba(24, 24, 24, .5); }
    .pf-photo-text { flex: 1 1 auto; min-width: 0; }
    .pf-photo-text b { display: block; font-size: .8125rem; font-weight: 800; }
    .pf-photo-text span { display: block; font-size: .6875rem; color: var(--muted); margin-top: .1rem; line-height: 1.4; }
    .pf-photo .pf-remove { margin-top: .4rem; }
    .pf-photo .pf-btn { flex: 0 0 auto; cursor: pointer; }
    @media (max-width: 420px) { .pf-photo { flex-wrap: wrap; } .pf-photo .pf-btn { width: 100%; justify-content: center; } }

    /* Password rules, ticked off as the student types. */
    .pf-rules { list-style: none; margin: 0; padding: .8rem .95rem; display: grid; gap: .45rem; border-radius: .9rem; background: var(--soft); }
    .pf-rules li { display: flex; align-items: center; gap: .55rem; font-size: .75rem; color: var(--muted); transition: color .2s; }
    .pf-rules li::before { content: ''; width: 1rem; height: 1rem; flex: 0 0 auto; border-radius: 9999px; border: 1.5px solid #C9C3BE; background: #fff center / .7rem no-repeat; transition: background-color .2s, border-color .2s; }
    .pf-rules li.is-ok { color: #15803D; font-weight: 600; }
    .pf-rules li.is-ok::before { border-color: #15803D; background-color: #15803D;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 12.5l4.5 4.5L19 7'/%3E%3C/svg%3E"); }
</style>

<div class="pf{{ session('success') ? ' has-flash' : '' }}">
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
            <p class="pf-photo-note">Tap the camera to change your photo.</p>
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

        <div class="pf-actions">
            <button type="button" class="pf-btn" onclick="document.getElementById('pfDetailsDialog').showModal()">
                <span class="iconify" data-icon="mdi:pencil-outline"></span>
                Edit Details
            </button>
            <button type="button" class="pf-btn is-ghost" onclick="document.getElementById('pfPasswordDialog').showModal()">
                <span class="iconify" data-icon="mdi:lock-reset"></span>
                Change Password
            </button>
        </div>

        <div class="pf-foot">
            <span class="iconify" data-icon="mdi:shield-check-outline" style="font-size: 1rem; color: #15803D"></span>
            <span>Last sign-in <b>{{ $pfLastLogin ? $pfLastLogin->diffForHumans() : 'not recorded' }}</b></span>
        </div>
    </aside>

    <div class="pf-main">
        {{-- Details --}}
        <dialog id="pfDetailsDialog" class="pf-dialog" aria-labelledby="pfDetailsTitle" onclick="if (event.target === this) this.close()">
            <form id="pfProfileForm" method="POST" action="{{ $pfAction }}" enctype="multipart/form-data" class="pf-sheet">
                @csrf
                @method('PUT')
                <div class="pf-mhead">
                    <span class="pf-icon"><span class="iconify" data-icon="mdi:account-edit-outline"></span></span>
                    <div>
                        <p class="pf-h" id="pfDetailsTitle">Edit Details</p>
                        <p class="pf-sub">How your name and contact details appear across the portal.</p>
                    </div>
                    <button type="button" class="pf-dialog-x" aria-label="Close" onclick="this.closest('dialog').close()"><span class="iconify" data-icon="mdi:close"></span></button>
                </div>
                <div class="pf-mbody">
                    <div class="pf-photo">
                        <img data-pf-avatar src="{{ $pfUser?->avatar_url }}" alt="">
                        <div class="pf-photo-text">
                            <b>Profile photo</b>
                            <span data-pf-photo-note>JPG, PNG, WEBP or GIF, up to 2 MB.</span>
                            @if ($pfUser?->avatar)
                                <label class="pf-remove">
                                    <input type="checkbox" name="remove_avatar" value="1">
                                    Remove current photo
                                </label>
                            @endif
                            @error('avatar')<p class="pf-err">{{ $message }}</p>@enderror
                        </div>
                        <label for="pfAvatar" class="pf-btn is-ghost">
                            <span class="iconify" data-icon="mdi:camera-outline"></span>
                            Change
                        </label>
                    </div>

                    <div>
                        <p class="pf-kicker">Name</p>
                        <div class="pf-grid is-3">
                            @foreach ([
                                ['first_name', 'First name', true],
                                ['middle_name', 'Middle name', false],
                                ['last_name', 'Last name', true],
                            ] as [$pfField, $pfFieldLabel, $pfRequired])
                                <div class="pf-field">
                                    <label for="pf_{{ $pfField }}">{{ $pfFieldLabel }} @unless($pfRequired)<small>(optional)</small>@endunless</label>
                                    <div class="pf-input">
                                        <input id="pf_{{ $pfField }}" name="{{ $pfField }}" type="text" value="{{ old($pfField, $pfUser?->{$pfField}) }}" @if($pfRequired) required @endif @if($loop->first) autofocus @endif>
                                    </div>
                                    @error($pfField)<p class="pf-err">{{ $message }}</p>@enderror
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="pf-kicker">Contact</p>
                        <div class="pf-grid is-2">
                            <div class="pf-field">
                                <label for="pf_email">Email</label>
                                <div class="pf-input has-lead">
                                    <span class="pf-lead"><span class="iconify" data-icon="mdi:email-outline"></span></span>
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
                                    <div class="pf-input has-lead">
                                        <span class="pf-lead"><span class="iconify" data-icon="mdi:phone-outline"></span></span>
                                        <input id="pf_phone" name="phone_number" type="text" value="{{ $pfPhoneValue }}" autocomplete="tel">
                                    </div>
                                @endif
                                @error('phone_number')<p class="pf-err">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pf-mfoot">
                    <button type="button" class="pf-btn is-ghost" onclick="this.closest('dialog').close()">Cancel</button>
                    <button type="submit" class="pf-btn is-primary">
                        <span class="iconify" data-icon="mdi:content-save-outline"></span>
                        Save Changes
                    </button>
                </div>
            </form>
        </dialog>

        <dialog id="pfPasswordDialog" class="pf-dialog is-narrow" aria-labelledby="pfPasswordTitle" onclick="if (event.target === this) this.close()">
            @include('partials.change-password')
        </dialog>

        {{-- Recent activity: this account's own trail - sign-ins, sign-outs,
             profile edits, task work - newest first. --}}
        <section class="pf-card pf-activity" style="--i: 3">
            <div class="pf-head">
                <div class="pf-title">
                    <span class="pf-icon"><span class="iconify" data-icon="mdi:history"></span></span>
                    <div>
                        <p class="pf-h">Recent Activity</p>
                        <p class="pf-sub">Sign-ins and changes on this account, newest first.</p>
                    </div>
                </div>
            </div>
            @if ($pfLog->isEmpty())
                <div class="pf-empty">
                    <span class="iconify" data-icon="mdi:timeline-clock-outline"></span>
                    Nothing yet. Your sign-ins and changes will show up here.
                </div>
            @else
                <ul class="pf-feed">
                    @foreach ($pfLog as $pfEntry)
                        @php $pfAt = optional($pfEntry->created_at)->timezone('Asia/Manila'); @endphp
                        <li>
                            <span class="pf-dot {{ $pfEntry->activity === \App\Models\ActivityLog::LOGIN ? 'is-in' : ($pfEntry->activity === \App\Models\ActivityLog::LOGOUT ? 'is-out' : '') }}">
                                <span class="iconify" data-icon="{{ match ($pfEntry->activity) {
                                    \App\Models\ActivityLog::LOGIN => 'mdi:login',
                                    \App\Models\ActivityLog::LOGOUT => 'mdi:logout',
                                    \App\Models\ActivityLog::ACCOUNT_UPDATED, \App\Models\ActivityLog::ACCOUNT_CREATED => 'mdi:account-edit-outline',
                                    default => 'mdi:checkbox-blank-circle-outline',
                                } }}"></span>
                            </span>
                            <div class="pf-what">
                                <b>{{ \App\Models\ActivityLog::LABELS[$pfEntry->activity] ?? \Illuminate\Support\Str::headline((string) $pfEntry->activity) }}</b>
                                @if (filled($pfEntry->description))<span title="{{ $pfEntry->description }}">{{ $pfEntry->description }}</span>@endif
                            </div>
                            @if ($pfAt)
                                <div class="pf-when">{{ $pfAt->diffForHumans() }}<small>{{ $pfAt->format('M j, g:i A') }}</small></div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</div>

<script>
    // Show the picked photo straight away, on the card and in the pop-up; it is
    // uploaded with Save Changes, which lives in the pop-up.
    document.getElementById('pfAvatar')?.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        document.querySelectorAll('#pfAvatarImg, [data-pf-avatar]').forEach(img => img.src = url);
        const note = document.querySelector('[data-pf-photo-note]');
        if (note) note.textContent = 'New photo picked. Press Save Changes to keep it.';
        const dialog = document.getElementById('pfDetailsDialog');
        if (dialog && !dialog.open) dialog.showModal();
    });
    // On load, not now: the student dashboard only reveals its Profile section
    // once the page is ready, and a pop-up opened inside a hidden section does
    // not show.
    @if ($pfOpenDetails)
        window.addEventListener('load', () => document.getElementById('pfDetailsDialog')?.showModal());
    @elseif ($pfOpenPassword)
        window.addEventListener('load', () => document.getElementById('pfPasswordDialog')?.showModal());
    @endif

    // On a wide screen the page fills the window down to the main area's bottom
    // padding, so Recent Activity takes the leftover height. Never shorter than
    // the content needs. Re-run when the student dashboard reveals the section
    // (its size changes from zero) and on resize.
    (function () {
        const pf = document.querySelector('.pf');
        if (!pf) return;
        const fit = () => {
            pf.style.height = '';
            if (window.innerWidth < 1024 || !pf.offsetParent) return;
            const main = pf.closest('main');
            const pad = main ? parseFloat(getComputedStyle(main).paddingBottom) || 0 : 16;
            const top = pf.getBoundingClientRect().top + window.scrollY + (main ? main.scrollTop : 0);
            const natural = pf.offsetHeight;
            pf.style.height = Math.max(natural, window.innerHeight - top - pad) + 'px';
        };
        let queued = false;
        const queue = () => { if (!queued) { queued = true; requestAnimationFrame(() => { queued = false; fit(); }); } };
        window.addEventListener('resize', queue);
        if (window.ResizeObserver && pf.parentElement) new ResizeObserver(queue).observe(pf.parentElement);
        queue();
    })();
</script>
