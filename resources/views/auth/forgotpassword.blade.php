<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SPC Hotel | Forgot Password</title>
  <link rel="icon" type="image/png" href="{{ asset('new_logo_in_chtm....png') }}" />
  <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,500;1,600&display=swap" rel="stylesheet" />
  <style>
    /* Same wine-and-gold system as landingpage.blade.php, self-contained for the
       same reason: public/css/app.css is a frozen Tailwind build. */
    :root {
      --wine-900: #4A0D1C;
      --wine-800: #5E1024;
      --wine-700: #7B1730;
      --wine-600: #9E1B3C;
      --rose-500: #C4425E;
      --gold-500: #C9A45C;
      --gold-300: #E4C97E;
      --gold-100: #F2E3BE;

      --cream: #FDF6F3;
      --cream-2: #FBEEE9;
      --surface: #FFFFFF;

      --ink: #000000;
      --ink-soft: #000000;
      --ink-muted: #000000;

      --r-card: 16px;
      --r-input: 12px;
      --r-pill: 999px;

      --ease: cubic-bezier(.16, 1, .3, 1);
      --gutter: clamp(18px, 3.2vw, 64px);
    }

    *, *::before, *::after { box-sizing: border-box; }

    body {
      margin: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      font-family: 'Manrope', system-ui, sans-serif;
      background: var(--cream);
      color: var(--ink);
      -webkit-font-smoothing: antialiased;
      overflow-x: hidden;
    }

    ::selection { background: var(--wine-700); color: #dadada }

    .hidden { display: none !important; }

    .display {
      font-family: 'Playfair Display', Georgia, serif;
      font-weight: 700;
      letter-spacing: -.01em;
    }

    @keyframes riseIn {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes scaleIn {
      0% { transform: scale(0); opacity: 0; }
      60% { transform: scale(1.12); }
      100% { transform: scale(1); opacity: 1; }
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .spinner { animation: spin .7s linear infinite; }

    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after {
        animation-duration: .001ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: .001ms !important;
      }
    }

    /* ---------- Navigation (mirrors the landing page) --------------------- */
    .nav {
      position: relative;
      z-index: 50;
      height: 72px;
      overflow: hidden;
      background: linear-gradient(94deg, var(--wine-900) 0%, var(--wine-800) 46%, var(--wine-700) 100%);
      border-bottom: 1px solid rgba(201, 164, 92, .38);
      box-shadow: 0 10px 34px rgba(74, 13, 28, .22);
    }
    .nav::after {
      content: '';
      position: absolute;
      top: -64px;
      right: -40px;
      width: 340px;
      height: 200px;
      border-radius: 0 0 0 100%;
      background: linear-gradient(210deg, rgba(228, 201, 126, .42), rgba(196, 66, 94, .30) 55%, transparent 78%);
      pointer-events: none;
    }
    .nav__inner {
      position: relative;
      z-index: 1;
      height: 72px;
      padding: 0 var(--gutter);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 24px;
    }
    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      min-width: 0;
      text-decoration: none;
    }
    .brand img { height: 42px; width: auto; object-fit: contain; }
    .brand__name {
      display: block;
      font-family: 'Playfair Display', Georgia, serif;
      font-size: 1.32rem;
      font-weight: 700;
      line-height: 1;
      color: #fff;
    }
    .brand__sub {
      display: block;
      margin-top: 4px;
      font-size: .58rem;
      font-weight: 700;
      text-transform: uppercase;
      color: var(--gold-300);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      border: 0;
      border-radius: var(--r-pill);
      font-family: 'Manrope', sans-serif;
      font-weight: 700;
      cursor: pointer;
      white-space: nowrap;
      text-decoration: none;
      transition: transform .2s var(--ease), box-shadow .2s var(--ease), background-color .2s ease;
    }
    .btn:active { transform: translateY(1px) scale(.99); }

    .btn--back {
      flex: 0 0 auto;
      padding: 9px 20px 9px 16px;
      font-size: .8rem;
      color: #000;
      background: linear-gradient(120deg, var(--gold-300), var(--gold-500));
      border: 1px solid var(--gold-300);
      box-shadow: 0 6px 18px rgba(74, 13, 28, .2);
    }
    .btn--back:hover { background: var(--gold-500); }

    .btn--submit {
      width: 100%;
      height: 50px;
      font-size: .82rem;
      text-transform: uppercase;
      color: #000;
      background: linear-gradient(120deg, var(--gold-300), var(--gold-500));
      border: 1px solid var(--gold-300);
      box-shadow: 0 12px 28px rgba(94, 16, 36, .26);
    }
    .btn--submit:hover { transform: translateY(-1px); background: var(--gold-500); }
    .btn--submit .iconify { font-size: 1.1rem; }

    /* ---------- Layout ---------------------------------------------------- */
    .page {
      flex: 1 0 auto;
      display: grid;
      grid-template-columns: 1fr 1fr;
    }

    /* Left: the landing hero's photograph under a wine wash. */
    .aside {
      position: relative;
      overflow: hidden;
      display: flex;
      align-items: flex-end;
      padding: 56px var(--gutter);
      color: #fff;
    }
    .aside > img {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .aside::before {
      content: '';
      position: absolute;
      inset: 0;
      z-index: 1;
      background: linear-gradient(180deg, rgba(74, 13, 28, .35) 0%, rgba(74, 13, 28, .78) 55%, rgba(74, 13, 28, .94) 100%);
    }
    .aside__copy {
      position: relative;
      z-index: 2;
      max-width: 30rem;
      animation: riseIn .8s var(--ease) both;
    }
    .eyebrow {
      display: flex;
      align-items: center;
      gap: 14px;
      margin: 0 0 18px;
      font-size: .66rem;
      font-weight: 800;
      text-transform: uppercase;
      color: var(--gold-300);
    }
    .eyebrow::after {
      content: '';
      flex: 0 1 84px;
      height: 1px;
      background: linear-gradient(90deg, var(--gold-500), transparent);
    }
    .aside h1 {
      margin: 0 0 16px;
      font-size: clamp(2rem, 3.2vw, 3rem);
      line-height: 1.08;
    }
    .aside h1 span {
      display: block;
      background: linear-gradient(96deg, var(--gold-300), var(--gold-500) 52%, var(--gold-300));
      -webkit-background-clip: text;
      background-clip: text;
      -webkit-text-fill-color: transparent;
      color: transparent;
    }
    .aside__lead {
      margin: 0 0 28px;
      font-size: .98rem;
      line-height: 1.68;
      color: rgba(255, 255, 255, .8);
    }
    .tips { margin: 0; padding: 0; list-style: none; }
    .tips li {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 12px 0;
      font-size: .82rem;
      line-height: 1.5;
      color: rgba(255, 255, 255, .82);
    }
    .tips li + li { border-top: 1px solid rgba(228, 201, 126, .22); }
    .tips__icon {
      flex: 0 0 auto;
      width: 40px;
      height: 40px;
      display: grid;
      place-items: center;
      border: 1px solid var(--gold-500);
      border-radius: var(--r-pill);
      color: var(--gold-300);
    }
    .tips__icon .iconify { font-size: 1.15rem; }

    /* Right: the form on cream, in a card like the landing value cards. */
    .main {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 56px var(--gutter);
    }
    .card {
      width: 100%;
      max-width: 30rem;
      padding: 38px 36px;
      border: 1px solid rgba(201, 164, 92, .32);
      border-radius: var(--r-card);
      background: linear-gradient(168deg, #fff 0%, var(--cream) 100%);
      box-shadow: 0 18px 46px rgba(94, 16, 36, .12);
      animation: riseIn .8s var(--ease) .1s both;
    }
    .card__head { text-align: center; margin-bottom: 28px; }
    .card__icon {
      width: 60px;
      height: 60px;
      margin: 0 auto 18px;
      display: grid;
      place-items: center;
      border-radius: var(--r-pill);
      border: 2px solid var(--gold-500);
      background: linear-gradient(140deg, var(--wine-800), var(--wine-600));
      color: var(--gold-300);
      box-shadow: inset 0 0 0 3px rgba(253, 246, 243, .9);
    }
    .card__icon .iconify { font-size: 1.6rem; }
    .card h2 {
      margin: 0 0 8px;
      font-size: clamp(1.5rem, 2.4vw, 1.9rem);
      line-height: 1.2;
      color: #000;
    }
    .card__lead {
      margin: 0;
      font-size: .9rem;
      line-height: 1.65;
      color: var(--ink-soft);
    }

    /* ---------- Fields (same as the landing login drawer) ------------------ */
    .field { margin-bottom: 16px; }
    .field:last-child { margin-bottom: 0; }
    .field__label {
      display: block;
      margin-bottom: 6px;
      font-size: .68rem;
      font-weight: 800;
      text-transform: uppercase;
      color: var(--ink-soft);
    }
    .field__wrap { position: relative; }
    .field__wrap > .iconify {
      position: absolute;
      top: 50%;
      left: 14px;
      transform: translateY(-50%);
      font-size: 1.1rem;
      color: var(--ink-muted);
      pointer-events: none;
    }
    .input-field {
      width: 100%;
      height: 48px;
      padding: 0 46px 0 40px;
      font-family: 'Manrope', sans-serif;
      font-size: .9rem;
      color: var(--ink);
      background: #fff;
      border: 1px solid #DCC8CD;
      border-radius: var(--r-input);
      transition: border-color .2s ease, box-shadow .2s ease;
    }
    .input-field::placeholder { color: var(--ink-muted); }
    .input-field:focus {
      outline: none;
      border-color: #111;
      box-shadow: 0 0 0 3px rgba(17, 17, 17, .10);
    }
    .input-field.error   { border-color: #C0392B; box-shadow: 0 0 0 3px rgba(192, 57, 43, .12); }
    .input-field.success { border-color: #2E7D4F; box-shadow: 0 0 0 3px rgba(46, 125, 79, .12); }
    .input-field.checking { border-color: var(--gold-500); box-shadow: 0 0 0 3px rgba(201, 164, 92, .18); }

    .field__status, .field__toggle {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      display: grid;
      place-items: center;
      width: 28px;
      height: 28px;
      font-size: 1.15rem;
    }
    .field__toggle {
      border: 0;
      background: none;
      color: var(--ink-muted);
      cursor: pointer;
    }
    .field__toggle:hover { color: var(--wine-700); }
    .status--checking { color: var(--gold-500); }
    .status--ok { color: #2E7D4F; }
    .status--bad { color: #C0392B; }

    .field__msg {
      display: flex;
      align-items: center;
      gap: 5px;
      margin: 7px 0 0;
      font-size: .72rem;
      font-weight: 600;
    }
    .field__msg .iconify { font-size: .9rem; flex: 0 0 auto; }
    .field__msg--checking { color: #9A7432; }
    .field__msg--bad { color: #C0392B; }
    .field__msg--ok { color: #2E7D4F; }

    .divider {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 18px;
      font-size: .64rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .12em;
      color: #000;
    }
    .divider::before, .divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: rgba(201, 164, 92, .4);
    }

    /* Password fields stay closed until the email checks out. */
    .reveal-section {
      max-height: 0;
      opacity: 0;
      overflow: hidden;
      transition: max-height .6s var(--ease), opacity .4s ease, margin .4s ease;
      /* Side and bottom room so the clip box doesn't cut the button's shadow. */
      margin: 0 -20px;
      padding: 0 20px;
    }
    .reveal-section.visible {
      max-height: 640px;
      opacity: 1;
      margin-top: 24px;
      margin-bottom: -20px;
      padding-bottom: 32px;
    }
    .reveal-section .btn--submit { margin-top: 8px; }

    /* ---------- Success ---------------------------------------------------- */
    .done { text-align: center; }
    .done__badge {
      width: 88px;
      height: 88px;
      margin: 0 auto 24px;
      display: grid;
      place-items: center;
      border-radius: var(--r-pill);
      border: 2px solid var(--gold-500);
      background: linear-gradient(140deg, var(--wine-800), var(--wine-600));
      color: var(--gold-300);
      box-shadow: inset 0 0 0 4px rgba(253, 246, 243, .9), 0 16px 36px rgba(94, 16, 36, .28);
      animation: scaleIn .6s var(--ease) both;
    }
    .done__badge .iconify { font-size: 2.6rem; }
    .done__note {
      display: flex;
      align-items: center;
      gap: 14px;
      margin: 24px 0;
      padding: 16px 18px;
      text-align: left;
      border: 1px solid rgba(201, 164, 92, .32);
      border-radius: var(--r-input);
      background: #fff;
    }
    .done__note .tips__icon { background: linear-gradient(140deg, var(--wine-800), var(--wine-600)); }
    .done__note strong { display: block; font-size: .86rem; color: #000; }
    .done__note span { font-size: .76rem; color: var(--ink-muted); }

    /* ---------- Footer (mirrors the landing page) ------------------------- */
    .footer {
      padding: 22px var(--gutter);
      background: var(--cream-2);
      border-top: 1px solid rgba(201, 164, 92, .34);
    }
    .footer__inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 18px;
      flex-wrap: wrap;
    }
    .footer__brand { display: flex; align-items: center; gap: 12px; }
    .footer__brand img { height: 36px; width: auto; object-fit: contain; }
    .footer__brand p { margin: 0; font-size: .68rem; font-weight: 700; color: #000; }
    .footer__brand span { display: block; margin-top: 2px; font-size: .64rem; font-weight: 500; color: var(--ink-muted); }
    .footer__meta { margin: 0; font-size: .66rem; line-height: 1.7; color: var(--ink-muted); }
    .footer__note { margin: 0; font-size: .66rem; font-weight: 700; color: var(--ink-soft); }

    /* ---------- Responsive ------------------------------------------------ */
    @media (max-width: 900px) {
      .page { grid-template-columns: 1fr; }
      .aside { display: none; }
      .main { padding: 36px var(--gutter) 48px; }
    }
    @media (max-width: 620px) {
      .brand__sub { display: none; }
      .btn--back .btn__label { display: none; }
      .btn--back { padding: 9px 12px; }
      .card { padding: 30px 22px; }
      .footer__inner { justify-content: center; text-align: center; }
    }
  </style>
@include('partials.no-horizontal-scroll')
</head>
<body>

  <!-- ==================== NAVIGATION ==================== -->
  <nav class="nav">
    <div class="nav__inner">
      <a href="{{ url('/') }}" class="brand">
        <img src="{{ asset('new_logo_in_chtm....png') }}" alt="College of Hospitality and Tourism Management logo" />
        <span>
          <span class="brand__name">SPC HOTEL</span>
          <span class="brand__sub">College of Hospitality &amp; Tourism Management</span>
        </span>
      </a>
      <a href="{{ route('login') }}" class="btn btn--back" aria-label="Back to Log In">
        <span class="iconify" data-icon="mdi:arrow-left"></span>
        <span class="btn__label">Back to Log In</span>
      </a>
    </div>
  </nav>

  <main class="page">

    <!-- ==================== LEFT: PHOTO ==================== -->
    <aside class="aside">
      <img src="{{ asset('chtm-building.png') }}" alt="" />
      <div class="aside__copy">
        <p class="eyebrow">Account Recovery</p>
        <h1 class="display">Reset your password. <span>We'll get you back in.</span></h1>
        <p class="aside__lead">It happens to the best of us. Confirm your email and choose a new password to return to the simulation.</p>
        <ul class="tips">
          <li>
            <span class="tips__icon"><span class="iconify" data-icon="mdi:shield-check-outline"></span></span>
            Your data is safe. We never share your credentials.
          </li>
          <li>
            <span class="tips__icon"><span class="iconify" data-icon="mdi:magnify"></span></span>
            We verify your email automatically as you type.
          </li>
          <li>
            <span class="tips__icon"><span class="iconify" data-icon="mdi:key-outline"></span></span>
            Choose a strong password you haven't used before.
          </li>
        </ul>
      </div>
    </aside>

    <!-- ==================== RIGHT: FORM ==================== -->
    <section class="main">
      <div class="card">

        <!-- ============ MAIN FORM ============ -->
        <div id="formPanel" class="{{ session('password_reset') ? 'hidden' : '' }}">
          <div class="card__head">
            <div class="card__icon"><span class="iconify" data-icon="mdi:lock-reset"></span></div>
            <h2 class="display">Forgot your password?</h2>
            <p class="card__lead">Enter the email address on your account and set your new password.</p>
          </div>

          <form id="resetForm" method="POST" action="{{ route('forgot-password.submit') }}">
            @csrf

            <!-- ===== EMAIL FIELD ===== -->
            <div class="field">
              <label for="emailInput" class="field__label">Email Address</label>
              <div class="field__wrap">
                <span class="iconify" data-icon="mdi:email-outline"></span>
                <input id="emailInput" name="email" type="email" placeholder="you@school.edu" required autocomplete="email"
                  value="{{ old('email') }}" class="input-field" />

                <span id="checkingIcon" class="field__status status--checking hidden">
                  <span class="iconify spinner" data-icon="mdi:loading"></span>
                </span>
                <span id="emailSuccessIcon" class="field__status status--ok hidden">
                  <span class="iconify" data-icon="mdi:check-circle"></span>
                </span>
                <span id="emailErrorIcon" class="field__status status--bad hidden">
                  <span class="iconify" data-icon="mdi:close-circle"></span>
                </span>
              </div>

              <p id="emailCheckingMsg" class="field__msg field__msg--checking hidden">
                <span class="iconify spinner" data-icon="mdi:loading"></span>
                <span>Checking your email...</span>
              </p>
              @error('email')
                <p class="field__msg field__msg--bad">
                  <span class="iconify" data-icon="mdi:alert-circle-outline"></span>
                  <span>{{ $message }}</span>
                </p>
              @enderror
              <p id="emailMissingMsg" class="field__msg field__msg--bad hidden">
                <span class="iconify" data-icon="mdi:close-circle"></span>
                <span>This account is not registered. Please contact your Faculty.</span>
              </p>
              <p id="emailExistsMsg" class="field__msg field__msg--ok hidden">
                <span class="iconify" data-icon="mdi:check-circle-outline"></span>
                <span>Email verified. You can reset your password.</span>
              </p>
            </div>

            {{-- Stays closed until the email is confirmed to belong to a registered,
                 active account, so there is no form to fill in before that. --}}
            <div id="passwordSection" class="reveal-section">
              <div class="divider">Set New Password</div>

              <div class="field">
                <label for="newPassword" class="field__label">New Password</label>
                <div class="field__wrap">
                  <span class="iconify" data-icon="mdi:lock-outline"></span>
                  <input id="newPassword" name="password" type="password" placeholder="Min. 8 characters" required class="input-field" />
                  <button type="button" class="field__toggle" onclick="togglePasswordVisibility('newPassword', this)" aria-label="Toggle password visibility">
                    <span class="iconify" data-icon="mdi:eye-off-outline"></span>
                  </button>
                </div>
                @error('password')
                  <p class="field__msg field__msg--bad">
                    <span class="iconify" data-icon="mdi:alert-circle-outline"></span>
                    <span>{{ $message }}</span>
                  </p>
                @enderror
              </div>

              <div class="field">
                <label for="confirmPassword" class="field__label">Confirm New Password</label>
                <div class="field__wrap">
                  <span class="iconify" data-icon="mdi:lock-check-outline"></span>
                  <input id="confirmPassword" name="password_confirmation" type="password" placeholder="Re-enter your new password" required class="input-field" />
                </div>
              </div>

              <button type="submit" id="resetBtn" class="btn btn--submit">
                <span class="iconify" data-icon="mdi:lock-reset"></span>
                Reset Password
              </button>
            </div>
          </form>
        </div>

        <!-- ============ SUCCESS STATE ============ -->
        <div id="successPanel" class="done {{ session('password_reset') ? '' : 'hidden' }}">
          <div class="done__badge"><span class="iconify" data-icon="mdi:check-bold"></span></div>
          <h2 class="display">Password Reset!</h2>
          <p class="card__lead">Your password has been updated. You can now log in with your new credentials.</p>
          <div class="done__note">
            <span class="tips__icon"><span class="iconify" data-icon="mdi:shield-check"></span></span>
            <div>
              <strong>Account Secured</strong>
              <span>Password updated just now</span>
            </div>
          </div>
          <a href="{{ route('login') }}" class="btn btn--submit">
            <span class="iconify" data-icon="mdi:login"></span>
            Log In Now
          </a>
        </div>

      </div>
    </section>
  </main>

  <!-- ==================== FOOTER ==================== -->
  <footer class="footer">
    <div class="footer__inner">
      <div class="footer__brand">
        <img src="{{ asset('new_logo_in_chtm....png') }}" alt="College of Hospitality and Tourism Management logo" />
        <div>
          <p>College of Hospitality &amp; Tourism Management</p>
          <span>SPC Hotel Simulation System</span>
        </div>
      </div>
      <p class="footer__meta">
        &copy; {{ date('Y') }} Southern de Oro Philippines College. All rights reserved.<br />
        Developed by: Jun Alexes Orao and Xyron Sandigan
      </p>
      <p class="footer__note">For educational purposes only</p>
    </div>
  </footer>

  <!-- ==================== SCRIPTS ==================== -->
  <script>
    const emailInput = document.getElementById('emailInput');
    const checkingIcon = document.getElementById('checkingIcon');
    const successIcon = document.getElementById('emailSuccessIcon');
    const errorIcon = document.getElementById('emailErrorIcon');
    const checkingMsg = document.getElementById('emailCheckingMsg');
    const missingMsg = document.getElementById('emailMissingMsg');
    const existsMsg = document.getElementById('emailExistsMsg');
    const passwordSection = document.getElementById('passwordSection');
    const resetForm = document.getElementById('resetForm');
    let debounceTimer = null;
    let isChecking = false;

    /* Closing the section also empties it: a password typed against one email must not
       ride along when the student switches to another. */
    function setPasswordSectionVisible(show) {
      passwordSection.classList.toggle('visible', show);
      if (!show) {
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';
      }
    }

    function resetEmailStatus() {
      emailInput.classList.remove('error', 'success', 'checking');
      [checkingIcon, successIcon, errorIcon, checkingMsg, missingMsg, existsMsg]
        .forEach(el => el.classList.add('hidden'));
      setPasswordSectionVisible(false);
    }

    async function checkEmail(email) {
      if (isChecking) return;
      isChecking = true;

      resetEmailStatus();
      emailInput.classList.add('checking');
      checkingIcon.classList.remove('hidden');
      checkingMsg.classList.remove('hidden');

      try {
        const response = await fetch(`{{ route('forgot-password.check-email') }}?email=${encodeURIComponent(email)}`);

        if (!response.ok) {
          throw new Error('Request failed');
        }

        const data = await response.json();

        checkingIcon.classList.add('hidden');
        checkingMsg.classList.add('hidden');
        emailInput.classList.remove('checking');

        if (data.exists) {
          emailInput.classList.add('success');
          successIcon.classList.remove('hidden');
          existsMsg.classList.remove('hidden');
          setPasswordSectionVisible(true);
        } else {
          emailInput.classList.add('error');
          errorIcon.classList.remove('hidden');
          missingMsg.classList.remove('hidden');
          setPasswordSectionVisible(false);
        }
      } catch (error) {
        checkingIcon.classList.add('hidden');
        checkingMsg.classList.add('hidden');
        emailInput.classList.remove('checking');
        setPasswordSectionVisible(false);
      } finally {
        isChecking = false;
      }
    }

    emailInput.addEventListener('input', function () {
      const email = this.value.trim();
      clearTimeout(debounceTimer);
      resetEmailStatus();

      if (!email) return;

      if (email.includes('@') && email.includes('.') && email.length > 5) {
        debounceTimer = setTimeout(() => {
          checkEmail(email);
        }, 600);
      }
    });

    emailInput.addEventListener('blur', function () {
      const email = this.value.trim();
      if (!email || isChecking) return;
      if (email.includes('@') && email.includes('.') && email.length > 5) {
        checkEmail(email);
      }
    });

    /* UX guard only — forgotPasswordSubmit() re-runs the same check server-side. */
    resetForm.addEventListener('submit', function (e) {
      if (!passwordSection.classList.contains('visible')) {
        e.preventDefault();
      }
    });

    function togglePasswordVisibility(inputId, btn) {
      const input = document.getElementById(inputId);
      const icon = btn.querySelector('.iconify');
      if (input.type === 'password') {
        input.type = 'text';
        icon.setAttribute('data-icon', 'mdi:eye-outline');
      } else {
        input.type = 'password';
        icon.setAttribute('data-icon', 'mdi:eye-off-outline');
      }
    }
  </script>
</body>
</html>
