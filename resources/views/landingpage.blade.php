<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hotel Management System | Interactive Hospitality Simulation System</title>
  <link rel="icon" type="image/png" href="{{ asset('chtm-logoo.png') }}" />
  <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
  <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(24px); }
      to { opacity: 1; transform: translateY(0); }
    }

    ::selection { background: #DB2777; color: #fff; }
    html { scroll-behavior: smooth; scroll-padding-top: 5rem; }
    body { font-family: 'Manrope', sans-serif; }

    .brand-gradient { background: linear-gradient(135deg, #F472B6, #DB2777, #9D174D); }
    .brand-gradient-dark { background: linear-gradient(135deg, #9D174D, #DB2777); }
    .gradient-text {
      background: linear-gradient(135deg, #F472B6, #DB2777, #9D174D);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .landing-nav {
      background: linear-gradient(90deg, #9D174D, #BE185D, #DB2777);
      box-shadow: 0 8px 30px rgba(76, 5, 36, .22);
    }
    .landing-nav .nav-link {
      border-bottom: 2px solid transparent;
      transition: color .2s ease, border-color .2s ease;
    }
    .landing-nav .nav-link.active {
      color: #fff;
      border-bottom-color: #fff;
    }

    .hero {
      min-height: 560px;
      padding-top: 72px;
      display: flex;
      align-items: center;
      background-image:
        linear-gradient(90deg, rgba(15,23,42,.92) 0%, rgba(15,23,42,.78) 40%, rgba(15,23,42,.30) 72%, rgba(15,23,42,.12) 100%),
        url('{{ asset('chtm-building.png') }}');
      background-size: cover;
      background-position: center;
    }
    .hero-copy { animation: fadeInUp .8s ease-out both; }
    .hero-kicker {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      padding: .45rem .75rem;
      border: 1px solid rgba(255,255,255,.25);
      border-radius: 999px;
      background: rgba(255,255,255,.10);
      color: rgba(255,255,255,.88);
      font-size: .68rem;
      font-weight: 800;
      letter-spacing: .13em;
      text-transform: uppercase;
      backdrop-filter: blur(8px);
    }
    .hero-rule {
      width: 80px;
      height: 3px;
      border-radius: 99px;
      background: linear-gradient(90deg, #F472B6, #DB2777);
    }

    .module-card {
      min-height: 190px;
      border: 1px solid #F1F5F9;
      background: #fff;
      box-shadow: 0 8px 30px rgba(15,23,42,.06);
      transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }
    .module-card:hover {
      transform: translateY(-6px);
      border-color: rgba(219,39,119,.25);
      box-shadow: 0 18px 40px rgba(219,39,119,.12);
    }
    .module-icon {
      width: 58px;
      height: 58px;
      border-radius: 16px;
      display: grid;
      place-items: center;
      margin: 0 auto 1rem;
      background: #FDF2F8;
      color: #DB2777;
      transition: .25s ease;
    }
    .module-card:hover .module-icon {
      color: #fff;
      background: linear-gradient(135deg, #F472B6, #DB2777, #9D174D);
      transform: scale(1.06);
    }

    .audience-strip {
      background: linear-gradient(105deg, #9D174D, #BE185D 52%, #DB2777);
      box-shadow: 0 16px 36px rgba(157,23,77,.22);
    }
    .audience-item + .audience-item { border-left: 1px solid rgba(255,255,255,.22); }

    .reveal {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity .7s ease, transform .7s ease;
    }
    .reveal.active { opacity: 1; transform: translateY(0); }

    .mobile-menu {
      transform: translateX(100%);
      transition: transform .35s cubic-bezier(.16,1,.3,1);
    }
    .mobile-menu.open { transform: translateX(0); }

    .login-overlay {
      opacity: 0;
      visibility: hidden;
      transition: opacity .35s ease, visibility .35s ease;
    }
    .login-overlay.open { opacity: 1; visibility: visible; }
    .login-drawer {
      transform: translateX(100%);
      transition: transform .45s cubic-bezier(.16,1,.3,1);
    }
    .login-drawer.open { transform: translateX(0); }
    body.login-open { overflow: hidden; }
    .input-field { transition: border-color .2s ease, box-shadow .2s ease; }
    .input-field:focus {
      border-color: #DB2777;
      box-shadow: 0 0 0 3px rgba(219,39,119,.12);
      outline: none;
    }

    html {
      scrollbar-width: none;
      -ms-overflow-style: none;
    }
    html::-webkit-scrollbar { display: none; }
    body::-webkit-scrollbar { display: none; }

    @media (max-width: 767px) {
      .hero {
        min-height: 610px;
        background-image:
          linear-gradient(90deg, rgba(15,23,42,.91), rgba(15,23,42,.68)),
          url('{{ asset('chtm-building.png') }}');
        background-position: 58% center;
      }
      .audience-item + .audience-item {
        border-left: 0;
        border-top: 1px solid rgba(255,255,255,.22);
      }
    }
  </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased overflow-x-hidden">

  <!-- ==================== NAVBAR ==================== -->
  <nav id="navbar" class="landing-nav fixed top-0 left-0 right-0 z-50 text-white">
    <div class="max-w-7xl mx-auto px-5 md:px-8">
      <div class="h-[72px] flex items-center justify-between gap-6">
        <a href="#home" class="flex items-center gap-3 min-w-0">
          <img src="{{ asset('chtm-logoo.png') }}" alt="Hotel Management System logo" class="h-11 w-auto object-contain" />
          <div class="min-w-0">
            <span class="block text-sm sm:text-base font-extrabold tracking-tight leading-none truncate">Hotel Management System</span>
           
          </div>
        </a>

        <div class="hidden lg:flex items-center gap-1">
          <a href="#home" class="nav-link active px-3 py-2 text-xs font-semibold text-white">Home</a>
          <a href="#features" class="nav-link px-3 py-2 text-xs font-semibold text-white/75 hover:text-white">Features</a>
          <button type="button" onclick="openLoginPanel()" class="ml-2 px-4 py-2 text-xs font-extrabold text-brand bg-white rounded-lg shadow-md hover:bg-pink-50 hover:scale-105 transition-all">Login</button>
        </div>

       

        <button id="menuToggle" class="lg:hidden w-10 h-10 shrink-0 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 transition-colors" aria-label="Open menu">
          <span class="iconify text-2xl" data-icon="mdi:menu"></span>
        </button>
      </div>
    </div>
  </nav>

  <div id="mobileMenu" class="mobile-menu fixed top-0 right-0 bottom-0 w-72 z-[60] bg-white shadow-2xl p-7 flex flex-col">
    <button id="menuClose" class="self-end w-10 h-10 flex items-center justify-center rounded-xl hover:bg-pink-50 mb-8" aria-label="Close menu">
      <span class="iconify text-2xl" data-icon="mdi:close"></span>
    </button>
    <div class="flex flex-col gap-2">
      <a href="#home" class="mobile-link px-3 py-3 rounded-lg text-sm font-bold text-slate-700 hover:bg-brand-soft hover:text-brand">Home</a>
      <a href="#features" class="mobile-link px-3 py-3 rounded-lg text-sm font-bold text-slate-700 hover:bg-brand-soft hover:text-brand">Features</a>
      <button type="button" onclick="closeMobileMenu(); openLoginPanel();" class="mt-4 h-12 brand-gradient text-white text-sm font-bold uppercase tracking-wider rounded-xl flex items-center justify-center gap-2">
        <span class="iconify" data-icon="mdi:login"></span> Login to System
      </button>
    </div>
  </div>
  <div id="menuOverlay" class="fixed inset-0 bg-black/40 z-[55] hidden"></div>

  <!-- ==================== HERO ==================== -->
  <section id="home" class="hero">
    <div class="max-w-7xl mx-auto px-5 md:px-8 w-full">
      <div class="hero-copy max-w-2xl py-16">
        <div class="hero-kicker mb-5">
          <span class="iconify text-base" data-icon="mdi:school-outline"></span>
          College of Hospitality &amp; Tourism Management
        </div>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-[1.04] mb-5">
          Hotel Management<br>
          <span class="gradient-text">Simulation System</span>
        </h1>
        <p class="text-white text-lg font-bold mb-1">Built for Hospitality Education</p>
        <p class="text-pink-200 text-sm font-bold italic mb-5">Exclusively for CHTM Students and Faculty</p>
        <div class="hero-rule mb-5"></div>
        <p id="about" class="text-sm sm:text-base text-white/75 leading-relaxed max-w-xl mb-7">
          A web-based learning platform where students build hotel concepts, manage realistic operations, and collaborate across departments with faculty guidance.
        </p>
        <p class="mt-4 flex items-center gap-2 text-[10px] text-white/65 font-medium">
          <span class="iconify text-sm" data-icon="mdi:shield-check-outline"></span>
          Secure educational access for authorized CHTM users.
        </p>
      </div>
    </div>
  </section>

  <!-- ==================== MODULES ==================== -->
  <section id="features" class="py-12 md:py-16 bg-white">
    <div class="max-w-7xl mx-auto px-5 md:px-8">
      <div class="reveal text-center mb-10">
        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-900 uppercase">
          Design. <span class="gradient-text">Simulate.</span> Manage.
        </h2>
        <p class="mt-3 text-sm text-slate-500 max-w-xl mx-auto">Practice real hotel operations through interactive simulation activities designed for CHTM learning and collaboration.</p>
      </div>

      <div id="modules" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <article class="module-card reveal rounded-2xl p-5 text-center">
          <div class="module-icon"><span class="iconify text-3xl" data-icon="mdi:desk"></span></div>
          <h3 class="text-sm font-extrabold text-slate-800 mb-2">Front Desk</h3>
          <p class="text-[11px] leading-relaxed text-slate-500">Manage reservations, check-ins, check-outs, and guest services.</p>
        </article>
        <article class="module-card reveal rounded-2xl p-5 text-center">
          <div class="module-icon"><span class="iconify text-3xl" data-icon="mdi:bed-outline"></span></div>
          <h3 class="text-sm font-extrabold text-slate-800 mb-2">Room Management</h3>
          <p class="text-[11px] leading-relaxed text-slate-500">Oversee room status, availability, rates, and preparation.</p>
        </article>
        <article class="module-card reveal rounded-2xl p-5 text-center">
          <div class="module-icon"><span class="iconify text-3xl" data-icon="mdi:silverware-fork-knife"></span></div>
          <h3 class="text-sm font-extrabold text-slate-800 mb-2">Restaurant Services</h3>
          <p class="text-[11px] leading-relaxed text-slate-500">Handle menus, orders, tables, and customer service.</p>
        </article>
        <article class="module-card reveal rounded-2xl p-5 text-center">
          <div class="module-icon"><span class="iconify text-3xl" data-icon="mdi:broom"></span></div>
          <h3 class="text-sm font-extrabold text-slate-800 mb-2">Housekeeping</h3>
          <p class="text-[11px] leading-relaxed text-slate-500">Assign tasks and maintain room cleanliness standards.</p>
        </article>
        <article class="module-card reveal rounded-2xl p-5 text-center">
          <div class="module-icon"><span class="iconify text-3xl" data-icon="mdi:tools"></span></div>
          <h3 class="text-sm font-extrabold text-slate-800 mb-2">Maintenance</h3>
          <p class="text-[11px] leading-relaxed text-slate-500">Track maintenance requests and manage repair activities.</p>
        </article>
        <article class="module-card reveal rounded-2xl p-5 text-center">
          <div class="module-icon"><span class="iconify text-3xl" data-icon="mdi:chart-box-outline"></span></div>
          <h3 class="text-sm font-extrabold text-slate-800 mb-2">Reports &amp; Analytics</h3>
          <p class="text-[11px] leading-relaxed text-slate-500">Review performance, progress, and simulation results.</p>
        </article>
      </div>

      <div class="audience-strip reveal mt-8 rounded-2xl overflow-hidden grid grid-cols-1 md:grid-cols-3 text-white">
        <div class="audience-item px-6 py-5 flex items-center gap-4">
          <div class="w-12 h-12 rounded-full border border-white/50 flex items-center justify-center shrink-0">
            <span class="iconify text-2xl" data-icon="mdi:school-outline"></span>
          </div>
          <div>
            <p class="text-[11px] font-extrabold uppercase tracking-wide">For CHTM Students</p>
            <p class="text-[10px] text-white/70 leading-relaxed mt-1">Collaborate with your team and experience real hotel operations.</p>
          </div>
        </div>
        <div class="audience-item px-6 py-5 flex items-center gap-4">
          <div class="w-12 h-12 rounded-full border border-white/50 flex items-center justify-center shrink-0">
            <span class="iconify text-2xl" data-icon="mdi:account-tie-outline"></span>
          </div>
          <div>
            <p class="text-[11px] font-extrabold uppercase tracking-wide">For Faculty</p>
            <p class="text-[10px] text-white/70 leading-relaxed mt-1">Create activities, assign tasks, and monitor student progress.</p>
          </div>
        </div>
        <div class="audience-item px-6 py-5 flex items-center gap-4">
          <div class="w-12 h-12 rounded-full border border-white/50 flex items-center justify-center shrink-0">
            <span class="iconify text-2xl" data-icon="mdi:shield-lock-outline"></span>
          </div>
          <div>
            <p class="text-[11px] font-extrabold uppercase tracking-wide">Secure &amp; Private</p>
            <p class="text-[10px] text-white/70 leading-relaxed mt-1">Restricted educational access keeps student data protected.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== LOGIN SLIDE PANEL ==================== -->
  <div id="loginOverlay" class="login-overlay fixed inset-0 z-[100] bg-black/40 backdrop-blur-sm" onclick="closeLoginPanel()" aria-hidden="true"></div>
  <aside id="loginDrawer" class="login-drawer fixed top-0 right-0 bottom-0 z-[110] w-full max-w-md bg-white shadow-2xl flex flex-col" role="dialog" aria-modal="true" aria-labelledby="loginDrawerTitle">
    <div class="brand-gradient px-6 py-5 flex items-center justify-between shrink-0">
      <div class="flex items-center gap-3">
        <img src="{{ asset('chtm-logoo.png') }}" alt="Hotel Management System" class="h-10 w-auto object-contain" />
        <div>
          <p id="loginDrawerTitle" class="text-white font-bold text-lg leading-tight">Welcome back</p>
          <p class="text-white/70 text-xs">Log in to your Hotel Management System account</p>
        </div>
      </div>
      <button type="button" onclick="closeLoginPanel()" class="w-10 h-10 rounded-xl bg-white/15 hover:bg-white/25 flex items-center justify-center text-white transition-colors" aria-label="Close login">
        <span class="iconify text-xl" data-icon="mdi:close"></span>
      </button>
    </div>

    <div class="flex-1 overflow-hidden flex flex-col min-h-0">
      <div class="flex-1 min-h-0 overflow-hidden">
        <img
          src="{{ asset('images/hotel/try.jpg') }}"
          alt="Hotel campus building"
          class="block w-full h-full object-cover object-center"
          hr class="border-0 border-t border-slate-200 m-0 shrink-0" />
        <hr class="border-0 border-t border-slate-200 m-0 shrink-0" />
        />
      </div>
      <hr>

      <div class="shrink-0 p-6 pb-10">
      <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
        @csrf

        @if ($errors->any())
          <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
          </div>
        @endif

        <div>
          <label for="landingLoginEmail" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Email Address</label>
          <div class="relative">
            <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:email-outline"></span>
            <input id="landingLoginEmail" name="email" type="email" placeholder="you@school.edu" required value="{{ old('email') }}"
              class="input-field w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm" />
          </div>
        </div>

        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label for="landingLoginPassword" class="text-xs font-bold uppercase tracking-wider text-slate-500">Password</label>
            <a href="{{ route('forgot-password') }}" class="text-xs text-brand font-semibold hover:text-brand-dark transition-colors">Forgot password?</a>
          </div>
          <div class="relative">
            <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:lock-outline"></span>
            <input id="landingLoginPassword" name="password" type="password" placeholder="Enter your password" required
              class="input-field w-full h-12 pl-10 pr-11 bg-slate-50 border border-slate-200 rounded-xl text-sm" />
            <button type="button" onclick="toggleLandingPassword()" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors" aria-label="Toggle password visibility">
              <span id="landingPasswordToggleIcon" class="iconify text-lg" data-icon="mdi:eye-off-outline"></span>
            </button>
          </div>
        </div>

        <label class="flex items-center gap-2.5 cursor-pointer">
          <input name="remember" type="checkbox" class="w-4 h-4 rounded border-slate-300 text-brand focus:ring-brand/30" />
          <span class="text-sm text-slate-500">Keep me logged in</span>
        </label>

        <button type="submit" class="w-full h-12 brand-gradient text-white text-sm font-bold uppercase tracking-wider rounded-xl hover:scale-[1.02] transition-transform shadow-lg shadow-brand/20 flex items-center justify-center gap-2">
          <span class="iconify text-lg" data-icon="mdi:login"></span>
          Log In
        </button>
      </form>

      <p class="mt-6 text-center text-xs text-slate-400 leading-relaxed">
        Students and faculty use the same login. Your role is assigned automatically after sign in.
      </p>
      </div>
    </div>
  </aside>

  <footer class="border-t border-slate-200 bg-slate-50 py-5 px-5">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
      <div class="flex items-center gap-3">
        <img src="{{ asset('chtm-logoo.png') }}" alt="CHTM logo" class="h-9 w-auto object-contain" />
        <div>
          <p class="text-[10px] font-bold text-slate-700">College of Hospitality &amp; Tourism Management</p>
          <p class="text-[9px] text-slate-400">Hotel Management Simulation System</p>
        </div>
      </div>
      <p class="text-[10px] text-slate-400">&copy; {{ date('Y') }} Hotel Management System. All rights reserved <br>
        Developed by: Jun Alexes Orao and Xyron Sandigan
      </p>
      <p class="text-[10px] font-semibold text-slate-500">For educational purposes only</p>
    </div>
  </footer>

  <script>
    // Login slide panel
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

    // Mobile menu toggle
    const menuToggle = document.getElementById('menuToggle');
    const menuClose = document.getElementById('menuClose');
    const mobileMenu = document.getElementById('mobileMenu');
    const menuOverlay = document.getElementById('menuOverlay');

    function openMobileMenu() {
      mobileMenu.classList.add('open');
      menuOverlay.classList.remove('hidden');
    }
    function closeMobileMenu() {
      mobileMenu.classList.remove('open');
      menuOverlay.classList.add('hidden');
    }
    menuToggle.addEventListener('click', openMobileMenu);
    menuClose.addEventListener('click', closeMobileMenu);
    menuOverlay.addEventListener('click', closeMobileMenu);
    document.querySelectorAll('.mobile-link').forEach(link => {
      link.addEventListener('click', closeMobileMenu);
    });

    // Reveal on scroll
    const reveals = document.querySelectorAll('.reveal');
    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('active');
          revealObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    reveals.forEach(el => revealObserver.observe(el));

    // Desktop nav underline follows active section
    const navLinks = document.querySelectorAll('.landing-nav .nav-link');
    const sectionIds = ['home', 'features'];

    function setActiveNav(id) {
      navLinks.forEach(link => {
        const isActive = link.getAttribute('href') === '#' + id;
        link.classList.toggle('active', isActive);
        link.classList.toggle('text-white', isActive);
        link.classList.toggle('text-white/75', !isActive);
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