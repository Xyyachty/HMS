<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SPC Hotel | Interactive Hospitality Simulation System</title>
  <link rel="icon" type="image/png" href="{{ asset('new_logo_in_chtm....png') }}" />
  <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
  <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,500;1,600&display=swap" rel="stylesheet" />
  <style>
    /* ==========================================================================
       SPC Hotel landing page.

       Self-contained on purpose. public/css/app.css is a frozen Tailwind build
       with no rebuild step wired up, so every rule this page needs lives here
       rather than depending on a class surviving in that file.
       ========================================================================== */

    :root {
      /* Wine and gold, carried over from the system's existing pink brand and
         deepened. Gold is the single accent. */
      --wine-900: #4A0D1C;
      --wine-800: #5E1024;
      --wine-700: #7B1730;
      --wine-600: #9E1B3C;
      --rose-500: #C4425E;
      --rose-300: #E08CA0;
      --gold-500: #C9A45C;
      --gold-300: #E4C97E;
      --gold-100: #F2E3BE;

      --cream: #FDF6F3;
      --cream-2: #FBEEE9;
      --surface: #FFFFFF;

      --ink: #2A1118;
      --ink-soft: #6B4A54;
      --ink-muted: #7A6068;

      /* One radius scale: cards 16px, inputs 12px, interactive elements pill. */
      --r-card: 16px;
      --r-input: 12px;
      --r-pill: 999px;

      --ease: cubic-bezier(.16, 1, .3, 1);
      /* Full-bleed layout: no max-width. The only thing holding content off the
         viewport edge is this gutter, which grows with the screen instead of
         leaving a fixed band of dead background on each side. */
      --gutter: clamp(18px, 3.2vw, 64px);
    }

    *, *::before, *::after { box-sizing: border-box; }

    html {
      scroll-behavior: smooth;
      scroll-padding-top: 5.5rem;
      scrollbar-width: none;
      -ms-overflow-style: none;
    }
    html::-webkit-scrollbar, body::-webkit-scrollbar { display: none; }

    body {
      margin: 0;
      font-family: 'Manrope', system-ui, sans-serif;
      background: var(--cream);
      color: var(--ink);
      -webkit-font-smoothing: antialiased;
      overflow-x: hidden;
    }

    ::selection { background: var(--wine-700); color: #dadada }

    .display {
      font-family: 'Playfair Display', Georgia, serif;
      font-weight: 700;
      letter-spacing: -.01em;
    }

    .shell {
      width: 100%;
      margin: 0 auto;
      padding: 0 var(--gutter);
    }

    /* ---------- Motion ---------------------------------------------------- */
    @keyframes riseIn {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .reveal {
      opacity: 0;
      transform: translateY(18px);
      transition: opacity .7s var(--ease), transform .7s var(--ease);
    }
    .reveal.active { opacity: 1; transform: translateY(0); }

    @media (prefers-reduced-motion: reduce) {
      html { scroll-behavior: auto; }
      *, *::before, *::after {
        animation-duration: .001ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: .001ms !important;
      }
      .reveal { opacity: 1; transform: none; }
    }

    /* ---------- Navigation ------------------------------------------------ */
    .nav {
      position: fixed;
      inset: 0 0 auto 0;
      z-index: 50;
      height: 72px;
      /* Clips the gold sweep below, which is offset past the right edge. */
      overflow: hidden;
      background: linear-gradient(94deg, var(--wine-900) 0%, var(--wine-800) 46%, var(--wine-700) 100%);
      border-bottom: 1px solid rgba(201, 164, 92, .38);
      box-shadow: 0 10px 34px rgba(74, 13, 28, .22);
    }
    /* The gold sweep the reference carries across the top right. Gradients and
       a radius, so there is no hand-drawn artwork to maintain. */
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
    .nav__links { display: flex; align-items: center; gap: 4px; }
    .nav__link {
      position: relative;
      padding: 8px 14px;
      font-size: .82rem;
      font-weight: 600;
      color: rgba(255, 255, 255, .76);
      text-decoration: none;
      transition: color .2s ease;
    }
    .nav__link::after {
      content: '';
      position: absolute;
      left: 14px;
      right: 14px;
      bottom: 0;
      height: 2px;
      border-radius: 2px;
      background: var(--gold-300);
      transform: scaleX(0);
      transition: transform .25s var(--ease);
    }
    .nav__link:hover { color: #fff; }
    .nav__link.active { color: #fff; }
    .nav__link.active::after { transform: scaleX(1); }

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

    .btn--login {
      margin-left: 10px;
      padding: 9px 20px 9px 16px;
      font-size: .8rem;
      color: var(--wine-900);
      background: linear-gradient(120deg, var(--gold-300), var(--gold-500));
      border: 1px solid var(--gold-300);
      box-shadow: 0 6px 18px rgba(74, 13, 28, .2);
    }
    .btn--login:hover { background: var(--gold-500); }

    .btn--primary {
      padding: 17px 34px;
      font-size: .95rem;
      color: #fff;
      background: linear-gradient(120deg, var(--wine-800), var(--wine-600));
      box-shadow: 0 14px 34px rgba(94, 16, 36, .30);
    }
    .btn--primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 20px 42px rgba(94, 16, 36, .36);
    }
    .btn--primary .iconify { font-size: 1.1rem; }

    .nav__burger {
      display: none;
      width: 42px;
      height: 42px;
      align-items: center;
      justify-content: center;
      border: 1px solid rgba(228, 201, 126, .4);
      border-radius: var(--r-input);
      background: rgba(255, 255, 255, .1);
      color: #fff;
      cursor: pointer;
      transition: background-color .2s ease;
    }
    .nav__burger:hover { background: rgba(255, 255, 255, .2); }

    /* ---------- Mobile drawer --------------------------------------------- */
    .mobile-menu {
      position: fixed;
      top: 0; right: 0; bottom: 0;
      width: min(300px, 84vw);
      z-index: 60;
      padding: 26px;
      display: flex;
      flex-direction: column;
      background: var(--surface);
      box-shadow: -20px 0 60px rgba(42, 17, 24, .24);
      transform: translateX(100%);
      transition: transform .38s var(--ease);
    }
    .mobile-menu.open { transform: translateX(0); }
    .mobile-menu__close {
      align-self: flex-end;
      width: 42px; height: 42px;
      margin-bottom: 26px;
      display: flex; align-items: center; justify-content: center;
      border: 0;
      border-radius: var(--r-input);
      background: var(--cream-2);
      color: var(--wine-800);
      cursor: pointer;
    }
    .mobile-link {
      padding: 13px 14px;
      border-radius: var(--r-input);
      font-size: .92rem;
      font-weight: 700;
      color: var(--ink);
      text-decoration: none;
      transition: background-color .2s ease, color .2s ease;
    }
    .mobile-link:hover { background: var(--cream-2); color: var(--wine-700); }
    .menu-overlay {
      position: fixed;
      inset: 0;
      z-index: 55;
      background: rgba(42, 17, 24, .44);
    }
    .menu-overlay[hidden] { display: none; }

    /* ---------- Hero ------------------------------------------------------ */
    .hero {
      position: relative;
      padding-top: 72px;
      background: var(--cream);
      overflow: hidden;
    }
    /* The hero drops the gutter so the photograph runs to the right edge of the
       viewport. The copy column carries the gutter itself instead. */
    .hero > .shell { padding: 0; }
    .hero__grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      align-items: stretch;
      min-height: 560px;
    }
    .hero__copy {
      position: relative;
      z-index: 2;
      /* A grid item defaults to min-width:auto, so a long heading word could
         widen this column past its 1fr share. Pinning it to 0 keeps the copy
         inside the track no matter how the title wraps. */
      min-width: 0;
      /* The right pad is the clearance between the last glyph and the curved
         divider, which now starts at the column boundary. */
      padding: 76px clamp(44px, 4.5vw, 88px) 132px var(--gutter);
      animation: riseIn .8s var(--ease) both;
      align-self: center;
    }
    .hero__eyebrow {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 22px;
      font-size: .66rem;
      font-weight: 800;
      text-transform: uppercase;
      color: var(--wine-600);
    }
    .hero__eyebrow::after {
      content: '';
      flex: 0 1 84px;
      height: 1px;
      background: linear-gradient(90deg, var(--gold-500), transparent);
    }
    .hero__title {
      margin: 0 0 22px;
      /* Was clamp(2.9rem, 5.4vw, 4.5rem). At 5.4vw the longest line grew with
         the viewport while the column stayed at 1fr, so the heading outran the
         copy panel. This scale keeps the longest line inside the track. */
      font-size: clamp(2.2rem, 3.4vw, 3.6rem);
      line-height: 1.06;
      /* Caps the measure on ultra-wide screens so the line breaks stay where
         the design puts them instead of running out into one long line. */
      max-width: 22ch;
      overflow-wrap: break-word;
      color: var(--wine-800);
    }
    .hero__title span {
      display: block;
      background: linear-gradient(96deg, var(--gold-500), #B8873C 52%, var(--gold-500));
      -webkit-background-clip: text;
      background-clip: text;
      -webkit-text-fill-color: transparent;
      color: transparent;
    }
    .hero__lead {
      max-width: 34rem;
      margin: 0 0 16px;
      font-size: 1.04rem;
      line-height: 1.68;
      color: var(--ink-soft);
    }
    .hero__rule {
      width: 72px;
      height: 2px;
      margin: 0 0 30px;
      border-radius: 2px;
      background: linear-gradient(90deg, var(--wine-600), var(--gold-500));
    }

    .hero__figure {
      position: relative;
      min-height: 560px;
      overflow: hidden;
    }
    .hero__figure img {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: 50% center;
    }
    /* The curved divider: a cream ellipse whose right edge is the ")" and whose
       other three edges sit flush against the copy panel's own cream, so only
       the arc is ever visible.

       left:0 is load-bearing. It used to be -24%, which put the ellipse's widest
       point 115px into the photo but its boundary LEFT of the figure near the
       figure's top and bottom edges. overflow:hidden erased the cream there, so
       the arc died into a dead-straight vertical run along the column edge --
       the "|)" look. Anchored at 0, the narrowest point of the arc is still
       0.55 * width inside the figure, so the curve stays continuous top to
       bottom and there is no flat edge left to see. */
    .hero__figure::before {
      content: '';
      position: absolute;
      top: -10%;
      left: 0;
      width: 20%;
      height: 120%;
      z-index: 1;
      border-radius: 0 100% 100% 0 / 0 50% 50% 0;
      background: var(--cream);
    }
    /* Pulling the figure left is what carries the ")" in toward the colon: the
       arc can only be drawn where the photo is, so the photo has to start left
       of the column boundary for the curve to sit there. The cream above always
       covers that overhang -- its narrowest reach is well right of the title,
       which .hero__title's max-width:22ch holds back -- so the heading never
       meets the photograph. Keep those two in step if either is retuned.

       Two steps rather than one: at 901-1200px the columns are tighter, so a
       smaller pull keeps the same clearance between text and curve. Below 901px
       the hero is a single stacked column and the figure gets no pull at all. */
    @media (min-width: 901px) {
      .hero__figure { margin-left: -12%; width: 112%; }
    }
    @media (min-width: 1201px) {
      .hero__figure { margin-left: -22%; width: 122%; }
    }
    /* The title's font stops growing at 3.6rem near 1700px while its column
       keeps widening, so past that point the heading takes up a steadily
       smaller share of the copy panel and the curve can move in further
       without closing the gap on the text. */
    @media (min-width: 1800px) {
      .hero__figure { margin-left: -28%; width: 128%; }
    }
    .hero__motto {
      position: absolute;
      right: 0;
      /* Clears the value cards, which ride up over the hero's lower edge.
         Sitting at 0 left the last line of the motto behind them. */
      bottom: 88px;
      z-index: 2;
      max-width: 300px;
      padding: 30px 36px 38px;
      border-top-left-radius: 120px 70px;
      background: linear-gradient(140deg, var(--wine-800) 8%, var(--wine-700) 92%);
      box-shadow: -16px -16px 48px rgba(74, 13, 28, .26);
    }
    .hero__motto p {
      margin: 0;
      font-family: 'Playfair Display', Georgia, serif;
      font-style: italic;
      font-weight: 500;
      font-size: 1.3rem;
      line-height: 1.42;
      color: var(--gold-100);
    }
    .hero__motto::before {
      content: '';
      position: absolute;
      top: 30px;
      left: 36px;
      width: 40px;
      height: 1px;
      background: var(--gold-500);
    }

    /* ---------- Value cards ----------------------------------------------- */
    .values {
      position: relative;
      z-index: 3;
      margin-top: 0;
      padding-top: clamp(36px, 4vw, 64px);
      padding-bottom: 72px;
      background: var(--surface);
    }
    .values__grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
    }
    .value-card {
      display: flex;
      align-items: flex-start;
      gap: 16px;
      padding: 24px 22px;
      border: 1px solid rgba(201, 164, 92, .32);
      border-radius: var(--r-card);
      background: linear-gradient(168deg, #fff 0%, var(--cream) 100%);
      box-shadow: 0 12px 34px rgba(94, 16, 36, .09);
      transition: transform .25s var(--ease), box-shadow .25s var(--ease), border-color .25s ease;
    }
    .value-card:hover {
      transform: translateY(-5px);
      border-color: var(--gold-500);
      box-shadow: 0 20px 44px rgba(94, 16, 36, .16);
    }
    .value-card__icon {
      flex: 0 0 auto;
      width: 54px;
      height: 54px;
      display: grid;
      place-items: center;
      border-radius: var(--r-pill);
      border: 2px solid var(--gold-500);
      background: linear-gradient(140deg, var(--wine-800), var(--wine-600));
      color: var(--gold-300);
      box-shadow: inset 0 0 0 3px rgba(253, 246, 243, .9);
    }
    .value-card__icon .iconify { font-size: 1.45rem; }
    .value-card h3 {
      margin: 2px 0 7px;
      font-family: 'Playfair Display', Georgia, serif;
      font-size: 1.02rem;
      font-weight: 700;
      line-height: 1.26;
      color: var(--wine-800);
    }
    .value-card p {
      margin: 0;
      font-size: .8rem;
      line-height: 1.6;
      color: var(--ink-soft);
    }

    /* ---------- Modules --------------------------------------------------- */
    /* Deliberately not cards: the value row above already uses elevation, so
       these are grouped by a gold numeral and negative space instead. */
    .modules { padding: 78px 0 84px; background: var(--surface); }
    .section-head { max-width: 40rem; margin: 0 0 54px; }
    .section-head h2 {
      margin: 0 0 14px;
      font-size: clamp(1.75rem, 3.1vw, 2.5rem);
      line-height: 1.16;
      color: var(--wine-800);
    }
    .section-head h2 em {
      font-style: italic;
      font-weight: 600;
      color: var(--rose-500);
    }
    .section-head p {
      margin: 0;
      font-size: .95rem;
      line-height: 1.7;
      color: var(--ink-soft);
    }

    .modules__grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 4px 44px;
    }
    .module {
      position: relative;
      padding: 26px 0 30px 62px;
      border-top: 1px solid rgba(201, 164, 92, .34);
    }
    .module__no {
      position: absolute;
      top: 24px;
      left: 0;
      font-family: 'Playfair Display', Georgia, serif;
      font-size: 1.5rem;
      font-weight: 600;
      line-height: 1;
      color: var(--gold-500);
    }
    .module h3 {
      display: flex;
      align-items: center;
      gap: 9px;
      margin: 0 0 8px;
      font-size: .97rem;
      font-weight: 800;
      color: var(--wine-800);
    }
    .module h3 .iconify { font-size: 1.15rem; color: var(--rose-500); }
    .module p {
      margin: 0;
      font-size: .83rem;
      line-height: 1.66;
      color: var(--ink-soft);
    }

    /* ---------- Audience band --------------------------------------------- */
    .audience {
      padding: 0 0 84px;
      background: var(--surface);
    }
    .audience__band {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      overflow: hidden;
      border-radius: var(--r-card);
      background: linear-gradient(104deg, var(--wine-900), var(--wine-700) 54%, var(--wine-600));
      box-shadow: 0 18px 46px rgba(74, 13, 28, .26);
    }
    .audience__item {
      display: flex;
      align-items: center;
      gap: 18px;
      padding: 30px 30px;
    }
    .audience__item + .audience__item { border-left: 1px solid rgba(228, 201, 126, .26); }
    .audience__icon {
      flex: 0 0 auto;
      width: 50px;
      height: 50px;
      display: grid;
      place-items: center;
      border: 1px solid var(--gold-500);
      border-radius: var(--r-pill);
      color: var(--gold-300);
    }
    .audience__icon .iconify { font-size: 1.4rem; }
    .audience__item h3 {
      margin: 0 0 6px;
      font-size: .8rem;
      font-weight: 800;
      text-transform: uppercase;
      color: #fff;
    }
    .audience__item p {
      margin: 0;
      font-size: .78rem;
      line-height: 1.6;
      color: rgba(255, 255, 255, .74);
    }

    /* ---------- Login drawer ---------------------------------------------- */
    .login-overlay {
      position: fixed;
      inset: 0;
      z-index: 100;
      background: rgba(42, 17, 24, .48);
      backdrop-filter: blur(4px);
      opacity: 0;
      visibility: hidden;
      transition: opacity .35s ease, visibility .35s ease;
    }
    .login-overlay.open { opacity: 1; visibility: visible; }

    .login-drawer {
      position: fixed;
      top: 0; right: 0; bottom: 0;
      z-index: 110;
      width: 100%;
      max-width: 27rem;
      display: flex;
      flex-direction: column;
      background: var(--surface);
      box-shadow: -24px 0 70px rgba(42, 17, 24, .3);
      transform: translateX(100%);
      transition: transform .45s var(--ease);
    }
    .login-drawer.open { transform: translateX(0); }
    body.login-open { overflow: hidden; }

    .login-drawer__head {
      flex: 0 0 auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 20px 24px;
      background: linear-gradient(112deg, var(--wine-900), var(--wine-700));
      border-bottom: 1px solid rgba(201, 164, 92, .4);
    }
    .login-drawer__head img { height: 40px; width: auto; object-fit: contain; }
    .login-drawer__head h2 {
      margin: 0;
      font-family: 'Playfair Display', Georgia, serif;
      font-size: 1.16rem;
      font-weight: 700;
      color: #fff;
    }
    .login-drawer__head p {
      margin: 3px 0 0;
      font-size: .72rem;
      color: var(--gold-300);
    }
    .login-drawer__close {
      flex: 0 0 auto;
      width: 40px; height: 40px;
      display: grid; place-items: center;
      border: 0;
      border-radius: var(--r-input);
      background: rgba(255, 255, 255, .16);
      color: #fff;
      cursor: pointer;
      transition: background-color .2s ease;
    }
    .login-drawer__close:hover { background: rgba(255, 255, 255, .28); }

    .login-drawer__photo {
      flex: 1 1 auto;
      min-height: 0;
      overflow: hidden;
      border-bottom: 1px solid var(--cream-2);
    }
    .login-drawer__photo img {
      display: block;
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .login-drawer__body { flex: 0 0 auto; padding: 24px 24px 38px; }

    .field { margin-bottom: 16px; }
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
      padding: 0 16px 0 40px;
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
    .input-field--pw { padding-right: 46px; }
    .field__toggle {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      display: grid;
      place-items: center;
      width: 28px; height: 28px;
      border: 0;
      background: none;
      color: var(--ink-muted);
      cursor: pointer;
    }
    .field__toggle:hover { color: var(--wine-700); }
    .link-quiet {
      font-size: .72rem;
      font-weight: 700;
      color: var(--wine-600);
      text-decoration: none;
    }
    .link-quiet:hover { color: var(--wine-800); text-decoration: underline; }
    .link-quiet--under { display: block; margin-top: 8px; text-align: right; }

    .check {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 20px;
      font-size: .84rem;
      color: var(--ink-soft);
      cursor: pointer;
    }
    .check input { width: 16px; height: 16px; accent-color: var(--wine-600); }

    .btn--submit {
      width: 100%;
      height: 50px;
      font-size: .82rem;
      text-transform: uppercase;
      color: var(--wine-900);
      background: linear-gradient(120deg, var(--gold-300), var(--gold-500));
      border: 1px solid var(--gold-300);
      box-shadow: 0 12px 28px rgba(94, 16, 36, .26);
    }
    .btn--submit:hover { transform: translateY(-1px); background: var(--gold-500); }

    .form-error {
      margin-bottom: 16px;
      padding: 12px 16px;
      border: 1px solid #E8B4B4;
      border-radius: var(--r-input);
      background: #FDF0F0;
      font-size: .84rem;
      color: #9A2B2B;
    }
    .form-note {
      margin: 20px 0 0;
      font-size: .74rem;
      line-height: 1.6;
      text-align: center;
      color: var(--ink-muted);
    }

    /* ---------- Footer ---------------------------------------------------- */
    .footer {
      padding: 26px 0;
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
    .footer__brand p { margin: 0; font-size: .68rem; font-weight: 700; color: var(--wine-800); }
    .footer__brand span { display: block; margin-top: 2px; font-size: .64rem; font-weight: 500; color: var(--ink-muted); }
    .footer__meta { margin: 0; font-size: .66rem; line-height: 1.7; color: var(--ink-muted); }
    .footer__note { margin: 0; font-size: .66rem; font-weight: 700; color: var(--ink-soft); }

    /* ---------- Responsive ------------------------------------------------ */
    /* 901-1200px had no hero rule: columns stayed 1fr 1fr while the title was
       still sized off the viewport, so the heading outgrew the copy column just
       as the layout got tightest. Give the copy the larger share here. */
    @media (max-width: 1200px) {
      .hero__grid { grid-template-columns: 1.08fr 1fr; min-height: 520px; }
      .hero__copy { padding: 60px clamp(36px, 3.6vw, 52px) 116px var(--gutter); }
      .hero__figure { min-height: 520px; }
      .hero__lead { max-width: 30rem; }
    }

    @media (max-width: 1080px) {
      .values__grid { grid-template-columns: repeat(2, 1fr); }
      .modules__grid { grid-template-columns: repeat(2, 1fr); gap: 4px 34px; }
    }

    @media (max-width: 900px) {
      .nav__links { display: none; }
      .nav__burger { display: flex; }

      .hero__grid { grid-template-columns: 1fr; min-height: 0; }
      .hero__copy {
        order: 2;
        padding: 40px var(--gutter) 108px;
        text-align: left;
      }
      /* Already edge to edge on one column, so no negative margin to undo a
         gutter with: the hero's shell has none. */
      .hero__figure {
        order: 1;
        min-height: 340px;
        border-radius: 0;
      }
      /* The sweep is a desktop device; on one column it would cut the photo. */
      .hero__figure::before { display: none; }
      .hero__motto {
        /* Stacked, the cards no longer overlap the photo. */
        bottom: 0;
        max-width: 240px;
        padding: 20px 24px 24px;
        border-top-left-radius: 90px 54px;
      }
      .hero__motto p { font-size: 1.05rem; }
      .hero__motto::before { display: none; }

      .audience__band { grid-template-columns: 1fr; }
      .audience__item + .audience__item {
        border-left: 0;
        border-top: 1px solid rgba(228, 201, 126, .26);
      }
    }

    @media (max-width: 620px) {
      .brand__sub { display: none; }
      .values { padding-top: 32px; }
      .values__grid { grid-template-columns: 1fr; }
      .modules__grid { grid-template-columns: 1fr; }
      .hero__figure { min-height: 280px; }
      .hero__motto { max-width: 208px; padding: 16px 18px 20px; }
      .hero__motto p { font-size: .95rem; }
      .btn--primary { width: 100%; }
      .footer__inner { justify-content: center; text-align: center; }
    }
  </style>
</head>
<body>

  <!-- ==================== NAVIGATION ==================== -->
  <nav class="nav">
    <div class="shell nav__inner">
      <a href="#home" class="brand">
        <img src="{{ asset('new_logo_in_chtm....png') }}" alt="College of Hospitality and Tourism Management logo" />
        <span>
          <span class="brand__name">SPC HOTEL</span>
          <span class="brand__sub">College of Hospitality &amp; Tourism Management</span>
        </span>
      </a>

      <div class="nav__links">
        <a href="#home" class="nav__link active">Home</a>
        <a href="#features" class="nav__link">Features</a>
        <button type="button" class="btn btn--login" onclick="openLoginPanel()">
          <span class="iconify" data-icon="mdi:account-outline"></span>
          Login
        </button>
      </div>

      <button id="menuToggle" class="nav__burger" aria-label="Open menu">
        <span class="iconify" style="font-size:1.5rem" data-icon="mdi:menu"></span>
      </button>
    </div>
  </nav>

  <div id="mobileMenu" class="mobile-menu">
    <button id="menuClose" class="mobile-menu__close" aria-label="Close menu">
      <span class="iconify" style="font-size:1.4rem" data-icon="mdi:close"></span>
    </button>
    <a href="#home" class="mobile-link">Home</a>
    <a href="#features" class="mobile-link">Features</a>
    <button type="button" class="btn btn--primary" style="margin-top:18px"
            onclick="closeMobileMenu(); openLoginPanel();">
      <span class="iconify" data-icon="mdi:login"></span>
      Login
    </button>
  </div>
  <div id="menuOverlay" class="menu-overlay" hidden></div>

  <!-- ==================== HERO ==================== -->
  <section id="home" class="hero">
    <div class="shell">
      <div class="hero__grid">
        <div class="hero__copy">
          <p class="hero__eyebrow">College of Hospitality &amp; Tourism Management</p>
          <h1 class="hero__title display">
            Hotel Management System:
            <span>A Web-Based Simulation Platform for Hospitality Students.</span>
          </h1>
          <p class="hero__lead">
            An interactive platform where students build hotel concepts, manage real operations,
            and collaborate across departments with faculty guidance.
          </p>
          <div class="hero__rule"></div>
          <button type="button" class="btn btn--primary" onclick="openLoginPanel()">
            Get Started
            <span class="iconify" data-icon="mdi:arrow-right"></span>
          </button>
        </div>

        <div class="hero__figure">
          <img src="{{ asset('chtm-building.png') }}"
               alt="SPC Hotel building illuminated at dusk" fetchpriority="high" />
          <div class="hero__motto">
            <p>People.<br />Places.<br />Better Futures.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== VALUE CARDS ==================== -->
  <section class="values">
    <div class="shell">
      <div class="values__grid">
        <article class="value-card reveal">
          <div class="value-card__icon"><span class="iconify" data-icon="mdi:room-service-outline"></span></div>
          <div>
            <h3>Real-World Hotel Operations</h3>
            <p>Experience actual hotel workflows and scenarios.</p>
          </div>
        </article>
        <article class="value-card reveal">
          <div class="value-card__icon"><span class="iconify" data-icon="mdi:school-outline"></span></div>
          <div>
            <h3>Hands-On Learning</h3>
            <p>Apply knowledge through interactive simulation.</p>
          </div>
        </article>
        <article class="value-card reveal">
          <div class="value-card__icon"><span class="iconify" data-icon="mdi:account-group-outline"></span></div>
          <div>
            <h3>Collaborative &amp; Safe</h3>
            <p>Work across departments in a risk-free environment.</p>
          </div>
        </article>
        <article class="value-card reveal">
          <div class="value-card__icon"><span class="iconify" data-icon="mdi:chart-line"></span></div>
          <div>
            <h3>Build Skills for the Future</h3>
            <p>Develop competencies for a successful hospitality career.</p>
          </div>
        </article>
      </div>
    </div>
  </section>

  <!-- ==================== MODULES ==================== -->
  <section id="features" class="modules">
    <div class="shell">
      <div class="section-head reveal" id="about">
        <h2 class="display">Design it, <em>simulate it,</em> then manage it.</h2>
        <p>Six departments students run themselves, built around the operations a working hotel
          actually depends on.</p>
      </div>

      <div class="modules__grid">
        <article class="module reveal">
          <span class="module__no display">01</span>
          <h3><span class="iconify" data-icon="mdi:desk"></span> Front Desk</h3>
          <p>Manage reservations, check-ins, check-outs, and guest services.</p>
        </article>
        <article class="module reveal">
          <span class="module__no display">02</span>
          <h3><span class="iconify" data-icon="mdi:bed-outline"></span> Room Management</h3>
          <p>Oversee room status, availability, rates, and preparation.</p>
        </article>
        <article class="module reveal">
          <span class="module__no display">03</span>
          <h3><span class="iconify" data-icon="mdi:silverware-fork-knife"></span> Restaurant Services</h3>
          <p>Handle menus, orders, tables, and customer service.</p>
        </article>
        <article class="module reveal">
          <span class="module__no display">04</span>
          <h3><span class="iconify" data-icon="mdi:broom"></span> Housekeeping</h3>
          <p>Assign tasks and maintain room cleanliness standards.</p>
        </article>
        <article class="module reveal">
          <span class="module__no display">05</span>
          <h3><span class="iconify" data-icon="mdi:tools"></span> Maintenance</h3>
          <p>Track maintenance requests and manage repair activities.</p>
        </article>
        <article class="module reveal">
          <span class="module__no display">06</span>
          <h3><span class="iconify" data-icon="mdi:chart-box-outline"></span> Reports &amp; Analytics</h3>
          <p>Review performance, progress, and simulation results.</p>
        </article>
      </div>
    </div>
  </section>

  <!-- ==================== AUDIENCE BAND ==================== -->
  <section class="audience">
    <div class="shell">
      <div class="audience__band reveal">
        <div class="audience__item">
          <div class="audience__icon"><span class="iconify" data-icon="mdi:school-outline"></span></div>
          <div>
            <h3>For CHTM Students</h3>
            <p>Collaborate with your team and experience real hotel operations.</p>
          </div>
        </div>
        <div class="audience__item">
          <div class="audience__icon"><span class="iconify" data-icon="mdi:account-tie-outline"></span></div>
          <div>
            <h3>For Faculty</h3>
            <p>Create activities, assign tasks, and monitor student progress.</p>
          </div>
        </div>
        <div class="audience__item">
          <div class="audience__icon"><span class="iconify" data-icon="mdi:shield-lock-outline"></span></div>
          <div>
            <h3>Secure &amp; Private</h3>
            <p>Restricted educational access keeps student data protected.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== LOGIN SLIDE PANEL ==================== -->
  <div id="loginOverlay" class="login-overlay" onclick="closeLoginPanel()" aria-hidden="true"></div>
  <aside id="loginDrawer" class="login-drawer" role="dialog" aria-modal="true" aria-labelledby="loginDrawerTitle">
    <div class="login-drawer__head">
      <div style="display:flex;align-items:center;gap:12px;min-width:0">
        <img src="{{ asset('new_logo_in_chtm....png') }}" alt="College of Hospitality and Tourism Management logo" />
        <div style="min-width:0">
          <h2 id="loginDrawerTitle">Welcome</h2>
          <p>Log in to your SPC Hotel account</p>
        </div>
      </div>
      <button type="button" class="login-drawer__close" onclick="closeLoginPanel()" aria-label="Close login">
        <span class="iconify" style="font-size:1.25rem" data-icon="mdi:close"></span>
      </button>
    </div>

    <div class="login-drawer__photo">
      <img src="{{ asset('SPC_GATE.png') }}" alt="SPC Hotel entrance" />
    </div>

    <div class="login-drawer__body">
      <form method="POST" action="{{ route('login.submit') }}">
        @csrf

        @if ($errors->any())
          <div class="form-error">{{ $errors->first() }}</div>
        @endif

        <div class="field">
          <label for="landingLoginEmail" class="field__label">Email Address</label>
          <div class="field__wrap">
            <span class="iconify" data-icon="mdi:email-outline"></span>
            <input id="landingLoginEmail" name="email" type="email" placeholder="you@school.edu"
                   required value="{{ old('email') }}" class="input-field" />
          </div>
        </div>

        <div class="field">
          <label for="landingLoginPassword" class="field__label">Password</label>
          <div class="field__wrap">
            <span class="iconify" data-icon="mdi:lock-outline"></span>
            <input id="landingLoginPassword" name="password" type="password" placeholder="Enter your password"
                   required class="input-field input-field--pw" />
            <button type="button" class="field__toggle" onclick="toggleLandingPassword()"
                    aria-label="Toggle password visibility">
              <span id="landingPasswordToggleIcon" class="iconify" style="font-size:1.1rem" data-icon="mdi:eye-off-outline"></span>
            </button>
          </div>
          {{-- Under the field it belongs to, not beside its label: someone who
               cannot remember the password reaches for this after trying to type
               one, so it is where their eye already is. --}}
          <a href="{{ route('forgot-password') }}" class="link-quiet link-quiet--under">Forgot password?</a>
        </div>

        <label class="check">
          <input name="remember" type="checkbox" />
          <span>Keep me logged in</span>
        </label>

        <button type="submit" class="btn btn--submit">
          <span class="iconify" style="font-size:1.1rem" data-icon="mdi:login"></span>
          Log In
        </button>
      </form>

      <p class="form-note">
        Students and faculty use the same login. Your role is assigned automatically after sign in.
      </p>
    </div>
  </aside>

  <!-- ==================== FOOTER ==================== -->
  <footer class="footer">
    <div class="shell footer__inner">
      <div class="footer__brand">
        <img src="{{ asset('new_logo_in_chtm....png') }}" alt="College of Hospitality and Tourism Management logo" />
        <div>
          <p>College of Hospitality &amp; Tourism Management</p>
          <span>SPC Hotel Simulation System</span>
        </div>
      </div>
      <p class="footer__meta">
        &copy; {{ date('Y') }} SPC Hotel. All rights reserved.<br />
        Developed by: Jun Alexes Orao and Xyron Sandigan
      </p>
      <p class="footer__note">For educational purposes only</p>
    </div>
  </footer>

  <script>
    // ---------- Login slide panel ----------
    const loginOverlay = document.getElementById('loginOverlay');
    const loginDrawer = document.getElementById('loginDrawer');

    function openLoginPanel() {
      loginOverlay.classList.add('open');
      loginDrawer.classList.add('open');
      document.body.classList.add('login-open');
      setTimeout(() => document.getElementById('landingLoginEmail')?.focus(), 400);
    }
    function closeLoginPanel() {
      loginOverlay.classList.remove('open');
      loginDrawer.classList.remove('open');
      document.body.classList.remove('login-open');
    }
    function toggleLandingPassword() {
      const input = document.getElementById('landingLoginPassword');
      const icon = document.getElementById('landingPasswordToggleIcon');
      if (!input || !icon) return;
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      icon.setAttribute('data-icon', show ? 'mdi:eye-outline' : 'mdi:eye-off-outline');
    }
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && loginDrawer.classList.contains('open')) closeLoginPanel();
    });
    document.addEventListener('DOMContentLoaded', () => {
      const params = new URLSearchParams(window.location.search);
      if (params.get('login') === '1' || @json($errors->any())) {
        openLoginPanel();
        if (params.get('login') === '1') {
          params.delete('login');
          const qs = params.toString();
          history.replaceState({}, '', window.location.pathname + (qs ? '?' + qs : '') + window.location.hash);
        }
      }
    });

    // ---------- Mobile menu ----------
    const menuToggle = document.getElementById('menuToggle');
    const menuClose = document.getElementById('menuClose');
    const mobileMenu = document.getElementById('mobileMenu');
    const menuOverlay = document.getElementById('menuOverlay');

    function openMobileMenu() {
      mobileMenu.classList.add('open');
      menuOverlay.hidden = false;
    }
    function closeMobileMenu() {
      mobileMenu.classList.remove('open');
      menuOverlay.hidden = true;
    }
    menuToggle.addEventListener('click', openMobileMenu);
    menuClose.addEventListener('click', closeMobileMenu);
    menuOverlay.addEventListener('click', closeMobileMenu);
    document.querySelectorAll('.mobile-link').forEach(link => {
      link.addEventListener('click', closeMobileMenu);
    });

    // ---------- Reveal on scroll ----------
    // IntersectionObserver rather than a scroll listener: it fires once per
    // element and costs nothing per frame.
    const reveals = document.querySelectorAll('.reveal');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reduceMotion) {
      reveals.forEach(el => el.classList.add('active'));
    } else {
      const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('active');
            revealObserver.unobserve(entry.target);
          }
        });
      }, { threshold: 0.12 });
      reveals.forEach(el => revealObserver.observe(el));
    }

    // ---------- Nav underline follows the active section ----------
    const navLinks = document.querySelectorAll('.nav__link');
    const sectionIds = ['home', 'features'];

    function setActiveNav(id) {
      navLinks.forEach(link => {
        link.classList.toggle('active', link.getAttribute('href') === '#' + id);
      });
    }

    navLinks.forEach(link => {
      link.addEventListener('click', () => {
        const id = (link.getAttribute('href') || '').replace('#', '');
        if (id) setActiveNav(id);
      });
    });

    const sectionObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) setActiveNav(entry.target.id);
      });
    }, { rootMargin: '-40% 0px -50% 0px', threshold: 0 });

    sectionIds.forEach(id => {
      const el = document.getElementById(id);
      if (el) sectionObserver.observe(el);
    });
  </script>
</body>
</html>
