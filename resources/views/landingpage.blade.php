<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HMS-Learn | Interactive Hospitality Simulation System</title>
  <link rel="icon" type="image/png" href="{{ asset('chtm-logoo.png') }}" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Manrope', 'sans-serif'] },
          colors: {
            brand: '#DB2777',
            'brand-light': '#F472B6',
            'brand-dark': '#9D174D',
            'brand-soft': '#FDF2F8',
            'rose-accent': '#FB7185',
            'plum-accent': '#A855F7',
          },
          animation: {
            'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
            'fade-in-up-2': 'fadeInUp 0.8s ease-out 0.2s forwards',
            'fade-in-up-4': 'fadeInUp 0.8s ease-out 0.4s forwards',
            'fade-in-up-6': 'fadeInUp 0.8s ease-out 0.6s forwards',
            'float': 'float 6s ease-in-out infinite',
            'float-delay': 'float 6s ease-in-out 2s infinite',
            'pulse-soft': 'pulseSoft 3s ease-in-out infinite',
            'glow-pulse': 'glowPulse 2.5s ease-in-out infinite',
          }
        }
      }
    }
  </script>
  <style>
    @keyframes fadeInUp {
      0% { opacity: 0; transform: translateY(30px); }
      100% { opacity: 1; transform: translateY(0); }
    }
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }
    @keyframes pulseSoft {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }
    @keyframes glowPulse {
      0%, 100% { box-shadow: 0 0 0 0 rgba(219,39,119,0.4); }
      50% { box-shadow: 0 0 0 12px rgba(219,39,119,0); }
    }
    @keyframes shimmer {
      0% { background-position: -200% 0; }
      100% { background-position: 200% 0; }
    }

    ::selection { background: #DB2777; color: #fff; }
    html { scroll-behavior: smooth; }
    body { font-family: 'Manrope', sans-serif; }

    .reveal {
      opacity: 0;
      transform: translateY(40px) scale(0.98);
      filter: blur(8px);
      transition: all 1s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .reveal.active {
      opacity: 1;
      transform: translateY(0) scale(1);
      filter: blur(0);
    }

    .gradient-text {
      background: linear-gradient(135deg, #F472B6, #DB2777, #9D174D);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .gradient-text-alt {
      background: linear-gradient(135deg, #FB7185, #DB2777, #A855F7);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .brand-gradient { background: linear-gradient(135deg, #F472B6, #DB2777, #9D174D); }
    .brand-gradient-alt { background: linear-gradient(135deg, #FB7185, #DB2777, #A855F7); }

    .glass {
      background: rgba(255,255,255,0.75);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border: 1px solid rgba(255,255,255,0.5);
    }

    .progress-bar {
      background: linear-gradient(90deg, #F472B6, #DB2777, #FB7185);
      background-size: 200% 100%;
      animation: shimmer 2s linear infinite;
    }

    .mobile-menu {
      transform: translateX(100%);
      transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .mobile-menu.open { transform: translateX(0); }

    .template-card {
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .template-card.selected {
      transform: translateY(-4px);
      box-shadow: 0 20px 40px -12px rgba(219,39,119,0.35);
    }

    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: #FDF2F8; }
    ::-webkit-scrollbar-thumb { background: #DB2777; border-radius: 99px; }
  </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased overflow-x-hidden">

  <!-- ==================== NAVBAR ==================== -->
  <nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-500">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
      <div class="flex items-center justify-between h-20">
        <a href="#" class="flex items-center gap-3 group">
          <img src="{{ asset('chtm-logoo.png') }}" alt="HMS-Learn logo" class="h-16 w-auto object-contain group-hover:scale-105 transition-transform duration-300 drop-shadow-sm" />
          <span class="text-lg font-bold tracking-tight">HMS<span class="text-brand">-Learn</span></span>
        </a>

        <div class="hidden md:flex items-center gap-1">
          <a href="#features" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-brand rounded-full hover:bg-brand/5 transition-all duration-200">Features</a>
          <a href="#departments" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-brand rounded-full hover:bg-brand/5 transition-all duration-200">Roles</a>
          <a href="#how-it-works" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-brand rounded-full hover:bg-brand/5 transition-all duration-200">How It Works</a>
          <a href="#faculty" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-brand rounded-full hover:bg-brand/5 transition-all duration-200">For Faculty</a>
          <a href="#team" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-brand rounded-full hover:bg-brand/5 transition-all duration-200">Team</a>
        </div>

        <div class="hidden md:flex items-center gap-3">
          <button onclick="window.location.href='{{ route('login') }}'" class="h-11 px-5 brand-gradient text-white text-xs font-bold uppercase tracking-wider rounded-full flex items-center gap-2 hover:scale-105 hover:-translate-y-0.5 transition-all duration-150 shadow-lg shadow-brand/20">
            <span class="iconify" data-icon="mdi:login"></span>
            Log In
          </button>
        </div>

        <button id="menuToggle" class="md:hidden w-10 h-10 flex items-center justify-center rounded-xl hover:bg-pink-50 transition-colors">
          <span class="iconify text-2xl" data-icon="mdi:menu"></span>
        </button>
      </div>
    </div>
  </nav>

  <!-- Mobile Menu -->
  <div id="mobileMenu" class="mobile-menu fixed top-0 right-0 bottom-0 w-72 z-50 bg-white shadow-2xl p-8 flex flex-col">
    <button id="menuClose" class="self-end w-10 h-10 flex items-center justify-center rounded-xl hover:bg-pink-50 mb-8">
      <span class="iconify text-2xl" data-icon="mdi:close"></span>
    </button>
    <div class="flex flex-col gap-4">
      <a href="#features" class="mobile-link text-lg font-medium text-slate-700 hover:text-brand transition-colors">Features</a>
      <a href="#departments" class="mobile-link text-lg font-medium text-slate-700 hover:text-brand transition-colors">Roles</a>
      <a href="#how-it-works" class="mobile-link text-lg font-medium text-slate-700 hover:text-brand transition-colors">How It Works</a>
      <a href="#faculty" class="mobile-link text-lg font-medium text-slate-700 hover:text-brand transition-colors">For Faculty</a>
      <a href="#team" class="mobile-link text-lg font-medium text-slate-700 hover:text-brand transition-colors">Team</a>
      <div class="border-t border-slate-100 pt-4 mt-2 space-y-3">
        <button onclick="closeMobileMenu(); window.location.href='{{ route('login') }}'" class="w-full h-12 brand-gradient text-white text-sm font-bold uppercase tracking-wider rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-brand/20">
          <span class="iconify" data-icon="mdi:login"></span> Log In
        </button>
      </div>
    </div>
  </div>
  <div id="menuOverlay" class="fixed inset-0 bg-black/30 z-40 hidden"></div>

  <!-- ==================== HERO ==================== -->
  <section class="relative min-h-screen flex items-center overflow-hidden bg-white">
    <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-gradient-to-bl from-pink-100 via-rose-50 to-transparent rounded-full blur-3xl opacity-60"></div>
    <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-gradient-to-tr from-purple-100 via-pink-50 to-transparent rounded-full blur-3xl opacity-40"></div>

    <div class="max-w-7xl mx-auto px-6 md:px-8 pt-28 pb-16 w-full">
      <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
        <!-- Left Text -->
        <div class="relative z-10">
          <div class="opacity-0 animate-fade-in-up inline-flex items-center gap-2 px-4 py-2 rounded-full border border-brand/15 bg-brand/5 mb-8">
            <div class="w-2 h-2 bg-brand rounded-full animate-pulse-soft"></div>
            <span class="text-xs font-bold uppercase tracking-widest text-brand">Hotel Simulation Learning</span>
          </div>

          <h1 class="opacity-0 animate-fade-in-up-2 text-5xl md:text-6xl lg:text-7xl font-extrabold leading-[0.92] tracking-tighter mb-6">
            Choose.<br/>
            <span class="gradient-text">Customize.</span><br/>
            <span class="gradient-text-alt">Simulate.</span>
          </h1>

          <p class="opacity-0 animate-fade-in-up-4 text-lg md:text-xl text-slate-500 font-light leading-relaxed max-w-lg mb-10">
            HMS-Learn lets hospitality students select from two default hotel templates, redesign them based on their team's unique hotel concept, and simulate real hotel operations and services — all while faculty oversee progress in real time.
          </p>

          <div class="opacity-0 animate-fade-in-up-6 flex flex-wrap gap-4">
            <button onclick="window.location.href='{{ route('login') }}'" class="h-14 px-8 brand-gradient text-white text-sm font-bold uppercase tracking-wider rounded-full flex items-center gap-2.5 hover:scale-105 hover:-translate-y-0.5 transition-all duration-150 shadow-lg shadow-brand/25">
              <span class="iconify text-lg" data-icon="mdi:login"></span>
              Log In
            </button>
          </div>

          <!-- Micro Stats -->
          <div class="opacity-0 animate-fade-in-up-6 mt-12 flex items-center gap-8">
            <div class="flex items-center gap-3">
              <div class="flex -space-x-2">
                <div class="w-8 h-8 rounded-full bg-brand/20 border-2 border-white flex items-center justify-center text-[10px] font-bold text-brand">A</div>
                <div class="w-8 h-8 rounded-full bg-rose-100 border-2 border-white flex items-center justify-center text-[10px] font-bold text-rose-accent">B</div>
                <div class="w-8 h-8 rounded-full bg-purple-100 border-2 border-white flex items-center justify-center text-[10px] font-bold text-plum-accent">C</div>
                <div class="w-8 h-8 rounded-full bg-amber-100 border-2 border-white flex items-center justify-center text-[10px] font-bold text-amber-600">+</div>
              </div>
              <p class="text-sm text-slate-500"><span class="font-semibold text-slate-700">50+</span> Students</p>
            </div>
            <div class="w-px h-8 bg-slate-200"></div>
            <div class="flex items-center gap-2">
              <span class="iconify text-amber-400 text-lg" data-icon="mdi:star"></span>
              <p class="text-sm text-slate-500"><span class="font-semibold text-slate-700">4.9</span> Rating</p>
            </div>
          </div>
        </div>

        <!-- Right: Template Selection & Customization Preview -->
        <div class="relative opacity-0 animate-fade-in-up-4">
          <div class="bg-white rounded-3xl shadow-2xl border border-slate-200/80 p-6 md:p-8">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h3 class="text-sm font-bold text-slate-700">Select Your Template</h3>
                <p class="text-xs text-slate-400">Grand Hotel — Group 3</p>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-brand/10 flex items-center justify-center">
                  <span class="iconify text-brand text-sm" data-icon="mdi:account-group"></span>
                </div>
                <span class="text-xs font-semibold text-slate-500">5 Members</span>
              </div>
            </div>

            <!-- Two Template Cards -->
            <div class="grid grid-cols-2 gap-3 mb-5">
              <!-- Template 1: Selected -->
              <div class="template-card selected relative bg-gradient-to-br from-brand-soft to-pink-50 rounded-2xl p-4 border-2 border-brand">
                <div class="absolute top-2 right-2 w-5 h-5 bg-brand rounded-full flex items-center justify-center">
                  <span class="iconify text-white text-xs" data-icon="mdi:check"></span>
                </div>
                <div class="w-10 h-10 bg-brand rounded-xl flex items-center justify-center mb-3">
                  <span class="iconify text-white text-xl" data-icon="mdi:office-building"></span>
                </div>
                <p class="text-xs font-bold text-slate-800">Classic Hotel</p>
                <p class="text-[10px] text-slate-500 mt-0.5">Business & City</p>
                <div class="mt-3 flex gap-1">
                  <div class="h-1 flex-1 bg-brand rounded-full"></div>
                  <div class="h-1 flex-1 bg-brand/40 rounded-full"></div>
                  <div class="h-1 flex-1 bg-brand/20 rounded-full"></div>
                </div>
              </div>

              <!-- Template 2 -->
              <div class="template-card relative bg-gradient-to-br from-purple-50 to-rose-50 rounded-2xl p-4 border-2 border-slate-200 hover:border-plum-accent/40">
                <div class="w-10 h-10 bg-plum-accent rounded-xl flex items-center justify-center mb-3">
                  <span class="iconify text-white text-xl" data-icon="mdi:beach"></span>
                </div>
                <p class="text-xs font-bold text-slate-800">Resort Hotel</p>
                <p class="text-[10px] text-slate-500 mt-0.5">Leisure & Spa</p>
                <div class="mt-3 flex gap-1">
                  <div class="h-1 flex-1 bg-plum-accent rounded-full"></div>
                  <div class="h-1 flex-1 bg-plum-accent/40 rounded-full"></div>
                  <div class="h-1 flex-1 bg-plum-accent/20 rounded-full"></div>
                </div>
              </div>
            </div>

            <!-- Customization Panel -->
            <div class="bg-slate-50 rounded-2xl p-4 mb-4">
              <div class="flex items-center gap-2 mb-3">
                <span class="iconify text-brand text-base" data-icon="mdi:pencil-ruler"></span>
                <p class="text-xs font-bold text-slate-700">Customize Your Concept</p>
              </div>
              <div class="space-y-2">
                <div class="flex items-center justify-between bg-white rounded-lg px-3 py-2 border border-slate-100">
                  <span class="text-[11px] text-slate-500">Hotel Name</span>
                  <span class="text-[11px] font-semibold text-slate-700">The Grand Azure</span>
                </div>
                <div class="flex items-center justify-between bg-white rounded-lg px-3 py-2 border border-slate-100">
                  <span class="text-[11px] text-slate-500">Theme</span>
                  <span class="text-[11px] font-semibold text-brand">Boutique Luxury</span>
                </div>
                <div class="flex items-center justify-between bg-white rounded-lg px-3 py-2 border border-slate-100">
                  <span class="text-[11px] text-slate-500">Room Count</span>
                  <span class="text-[11px] font-semibold text-slate-700">120</span>
                </div>
              </div>
            </div>

            <!-- Simulate Button -->
            <button class="w-full brand-gradient text-white text-xs font-bold uppercase tracking-wider rounded-xl py-3 flex items-center justify-center gap-2 animate-glow-pulse">
              <span class="iconify text-base" data-icon="mdi:play-circle"></span>
              Simulate Operations
            </button>
          </div>

          <div class="absolute -top-4 -right-4 animate-float">
            <div class="glass rounded-xl px-3 py-2 shadow-lg flex items-center gap-2">
              <span class="iconify text-brand text-lg" data-icon="mdi:palette-outline"></span>
              <span class="text-xs font-semibold text-slate-700">Customize</span>
            </div>
          </div>
          <div class="absolute -bottom-4 -left-4 animate-float-delay">
            <div class="glass rounded-xl px-3 py-2 shadow-lg flex items-center gap-2">
              <span class="iconify text-green-500 text-lg" data-icon="mdi:play-circle-outline"></span>
              <span class="text-xs font-semibold text-slate-700">Simulate Live</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== ROLES ==================== -->
  <section id="departments" class="py-24 md:py-32 px-6 md:px-8 bg-slate-50">
    <div class="max-w-7xl mx-auto">
      <div class="reveal text-center max-w-3xl mx-auto mb-16 md:mb-20">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-brand/15 bg-brand/5 mb-6">
          <span class="iconify text-brand text-sm" data-icon="mdi:office-building-outline"></span>
          <span class="text-xs font-bold uppercase tracking-widest text-brand">Hotel Roles</span>
        </div>
        <h2 class="text-4xl md:text-5xl font-bold tracking-tight mb-6">Five Roles, <span class="gradient-text">One Simulation</span></h2>
        <p class="text-lg text-slate-500 font-light leading-relaxed">Each student is assigned to a real hotel role within their team's customized hotel. Operations are organized, simulated, and evaluated — just like a real hotel.</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Room Management -->
        <div class="reveal dept-card group bg-white rounded-3xl p-8 border border-slate-100 hover:border-brand/30 hover:shadow-2xl cursor-default transition-all duration-300">
          <div class="w-16 h-16 bg-brand/10 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-brand group-hover:scale-110 transition-all duration-300">
            <span class="iconify text-brand text-3xl group-hover:text-white transition-colors" data-icon="mdi:bed-outline"></span>
          </div>
          <h3 class="text-xl font-bold tracking-tight mb-2">Room Management</h3>
          <p class="text-slate-500 font-light text-sm leading-relaxed mb-4">Handle check-ins, room assignments, VIP setups, minibar restocking, and guest room preparation.</p>
          <div class="flex flex-wrap gap-1.5">
            <span class="px-2 py-0.5 bg-brand/5 text-brand text-[10px] font-bold rounded-full">Check-in</span>
            <span class="px-2 py-0.5 bg-brand/5 text-brand text-[10px] font-bold rounded-full">VIP Setup</span>
            <span class="px-2 py-0.5 bg-brand/5 text-brand text-[10px] font-bold rounded-full">Room Prep</span>
          </div>
        </div>

        <!-- Front Desk -->
        <div class="reveal dept-card group bg-white rounded-3xl p-8 border border-slate-100 hover:border-rose-accent/30 hover:shadow-2xl cursor-default transition-all duration-300">
          <div class="w-16 h-16 bg-rose-accent/10 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-rose-accent group-hover:scale-110 transition-all duration-300">
            <span class="iconify text-rose-accent text-3xl group-hover:text-white transition-colors" data-icon="mdi:desk"></span>
          </div>
          <h3 class="text-xl font-bold tracking-tight mb-2">Front Desk</h3>
          <p class="text-slate-500 font-light text-sm leading-relaxed mb-4">Manage guest reception, reservations, key card preparation, concierge requests, and billing inquiries.</p>
          <div class="flex flex-wrap gap-1.5">
            <span class="px-2 py-0.5 bg-rose-accent/5 text-rose-accent text-[10px] font-bold rounded-full">Reception</span>
            <span class="px-2 py-0.5 bg-rose-accent/5 text-rose-accent text-[10px] font-bold rounded-full">Reservations</span>
            <span class="px-2 py-0.5 bg-rose-accent/5 text-rose-accent text-[10px] font-bold rounded-full">Billing</span>
          </div>
        </div>

        <!-- Maintenance -->
        <div class="reveal dept-card group bg-white rounded-3xl p-8 border border-slate-100 hover:border-plum-accent/30 hover:shadow-2xl cursor-default transition-all duration-300">
          <div class="w-16 h-16 bg-plum-accent/10 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-plum-accent group-hover:scale-110 transition-all duration-300">
            <span class="iconify text-plum-accent text-3xl group-hover:text-white transition-colors" data-icon="mdi:broom"></span>
          </div>
          <h3 class="text-xl font-bold tracking-tight mb-2">Maintenance</h3>
          <p class="text-slate-500 font-light text-sm leading-relaxed mb-4">Oversee repairs, equipment maintenance, facility inspections, and safety compliance.</p>
          <div class="flex flex-wrap gap-1.5">
            <span class="px-2 py-0.5 bg-plum-accent/5 text-plum-accent text-[10px] font-bold rounded-full">Repairs</span>
            <span class="px-2 py-0.5 bg-plum-accent/5 text-plum-accent text-[10px] font-bold rounded-full">Inspection</span>
            <span class="px-2 py-0.5 bg-plum-accent/5 text-plum-accent text-[10px] font-bold rounded-full">Safety</span>
          </div>
        </div>

        <!-- Housekeeping Services -->
        <div class="reveal dept-card group bg-white rounded-3xl p-8 border border-slate-100 hover:border-teal-400/30 hover:shadow-2xl cursor-default transition-all duration-300">
          <div class="w-16 h-16 bg-teal-500/10 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-teal-500 group-hover:scale-110 transition-all duration-300">
            <span class="iconify text-teal-500 text-3xl group-hover:text-white transition-colors" data-icon="mdi:sparkles"></span>
          </div>
          <h3 class="text-xl font-bold tracking-tight mb-2">Housekeeping Services</h3>
          <p class="text-slate-500 font-light text-sm leading-relaxed mb-4">Manage deep cleaning, linen services, amenity restocking, and sanitation standards.</p>
          <div class="flex flex-wrap gap-1.5">
            <span class="px-2 py-0.5 bg-teal-500/5 text-teal-500 text-[10px] font-bold rounded-full">Cleaning</span>
            <span class="px-2 py-0.5 bg-teal-500/5 text-teal-500 text-[10px] font-bold rounded-full">Linen</span>
            <span class="px-2 py-0.5 bg-teal-500/5 text-teal-500 text-[10px] font-bold rounded-full">Sanitation</span>
          </div>
        </div>

        <!-- Restaurant -->
        <div class="reveal dept-card group bg-white rounded-3xl p-8 border border-slate-100 hover:border-amber-400/30 hover:shadow-2xl cursor-default transition-all duration-300">
          <div class="w-16 h-16 bg-amber-500/10 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-amber-500 group-hover:scale-110 transition-all duration-300">
            <span class="iconify text-amber-500 text-3xl group-hover:text-white transition-colors" data-icon="mdi:silverware-fork-knife"></span>
          </div>
          <h3 class="text-xl font-bold tracking-tight mb-2">Restaurant</h3>
          <p class="text-slate-500 font-light text-sm leading-relaxed mb-4">Coordinate banquet setups, menu planning, food preparation, room service, and dining reservations.</p>
          <div class="flex flex-wrap gap-1.5">
            <span class="px-2 py-0.5 bg-amber-500/5 text-amber-600 text-[10px] font-bold rounded-full">Banquet</span>
            <span class="px-2 py-0.5 bg-amber-500/5 text-amber-600 text-[10px] font-bold rounded-full">Menu</span>
            <span class="px-2 py-0.5 bg-amber-500/5 text-amber-600 text-[10px] font-bold rounded-full">Room Service</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== KEY FEATURES ==================== -->
  <section id="features" class="py-24 md:py-32 px-6 md:px-8 bg-white">
    <div class="max-w-7xl mx-auto">
      <div class="reveal text-center max-w-3xl mx-auto mb-16 md:mb-20">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-brand/15 bg-brand/5 mb-6">
          <span class="iconify text-brand text-sm" data-icon="mdi:star-four-points"></span>
          <span class="text-xs font-bold uppercase tracking-widest text-brand">Key Features</span>
        </div>
        <h2 class="text-4xl md:text-5xl font-bold tracking-tight mb-6">Built for <span class="gradient-text">Real Learning</span></h2>
        <p class="text-lg text-slate-500 font-light leading-relaxed">Every feature guides students from template selection through customization to full hotel operations simulation — while giving faculty complete visibility into performance.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="reveal group relative bg-brand-soft rounded-3xl p-8 border border-brand/10 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-brand/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
          <div class="relative">
            <div class="w-12 h-12 brand-gradient rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
              <span class="iconify text-white text-2xl" data-icon="mdi:view-grid-outline"></span>
            </div>
            <h3 class="text-lg font-bold tracking-tight mb-2">Two Default Templates</h3>
            <p class="text-slate-500 font-light text-sm leading-relaxed">Start quickly with two professionally designed hotel templates. Each serves as a solid foundation for your team's project.</p>
          </div>
        </div>

        <div class="reveal group relative bg-purple-50 rounded-3xl p-8 border border-plum-accent/10 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-plum-accent/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
          <div class="relative">
            <div class="w-12 h-12 bg-plum-accent rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
              <span class="iconify text-white text-2xl" data-icon="mdi:pencil-ruler"></span>
            </div>
            <h3 class="text-lg font-bold tracking-tight mb-2">Concept Customization</h3>
            <p class="text-slate-500 font-light text-sm leading-relaxed">Redesign and tailor your chosen template to match your team's unique hotel concept — from branding to room layouts and service style.</p>
          </div>
        </div>

        <div class="reveal group relative bg-amber-50 rounded-3xl p-8 border border-amber-400/10 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-amber-400/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
          <div class="relative">
            <div class="w-12 h-12 bg-amber-500 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
              <span class="iconify text-white text-2xl" data-icon="mdi:play-circle-outline"></span>
            </div>
            <h3 class="text-lg font-bold tracking-tight mb-2">Operations Simulation</h3>
            <p class="text-slate-500 font-light text-sm leading-relaxed">Once your hotel is customized, simulate real-world operations and services across all departments — just like running an actual hotel.</p>
          </div>
        </div>

        <div class="reveal group relative bg-rose-50 rounded-3xl p-8 border border-rose-accent/10 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-rose-accent/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
          <div class="relative">
            <div class="w-12 h-12 bg-rose-accent rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
              <span class="iconify text-white text-2xl" data-icon="mdi:account-multiple-outline"></span>
            </div>
            <h3 class="text-lg font-bold tracking-tight mb-2">Group Collaboration</h3>
            <p class="text-slate-500 font-light text-sm leading-relaxed">Students work in teams, each assigned to a role within the customized hotel. Coordinate and run services together — in real time.</p>
          </div>
        </div>

        <div class="reveal group relative bg-brand-soft rounded-3xl p-8 border border-brand/10 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-brand/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
          <div class="relative">
            <div class="w-12 h-12 brand-gradient rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
              <span class="iconify text-white text-2xl" data-icon="mdi:chart-timeline-variant-shimmer"></span>
            </div>
            <h3 class="text-lg font-bold tracking-tight mb-2">Progress Tracking</h3>
            <p class="text-slate-500 font-light text-sm leading-relaxed">Visual progress bars and completion stats for every student, group, and role throughout the simulation. No one falls behind unnoticed.</p>
          </div>
        </div>

        <div class="reveal group relative bg-rose-50 rounded-3xl p-8 border border-rose-accent/10 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
          <div class="absolute top-0 right-0 w-32 h-32 bg-rose-accent/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
          <div class="relative">
            <div class="w-12 h-12 bg-rose-accent rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
              <span class="iconify text-white text-2xl" data-icon="mdi:shield-check-outline"></span>
            </div>
            <h3 class="text-lg font-bold tracking-tight mb-2">Role-Based Access</h3>
            <p class="text-slate-500 font-light text-sm leading-relaxed">Students manage their hotel's operations, faculty oversee everything. Each role has the right level of control and visibility.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== HOW IT WORKS ==================== -->
  <section id="how-it-works" class="py-24 md:py-32 px-6 md:px-8 bg-slate-950 text-white">
    <div class="max-w-7xl mx-auto">
      <div class="reveal text-center max-w-3xl mx-auto mb-20">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-white/10 bg-white/5 mb-6">
          <span class="iconify text-brand-light text-sm" data-icon="mdi:lightning-bolt"></span>
          <span class="text-xs font-bold uppercase tracking-widest text-slate-300">How It Works</span>
        </div>
        <h2 class="text-4xl md:text-5xl font-bold tracking-tight mb-6">Three Simple Steps, <span class="gradient-text">A Full Hotel Experience</span></h2>
      </div>

      <div class="grid md:grid-cols-2 gap-16 items-start">
        <!-- Student Side -->
        <div>
          <div class="reveal flex items-center gap-3 mb-8">
            <div class="w-10 h-10 brand-gradient rounded-xl flex items-center justify-center">
              <span class="iconify text-white text-xl" data-icon="mdi:account-outline"></span>
            </div>
            <h3 class="text-2xl font-bold">For Students</h3>
          </div>
          <div class="space-y-6">
            <div class="reveal flex gap-4">
              <div class="flex-shrink-0 w-10 h-10 bg-brand/20 rounded-xl flex items-center justify-center text-brand-light font-bold">1</div>
              <div>
                <h4 class="font-semibold mb-1">Get Assigned to a Group</h4>
                <p class="text-slate-400 font-light text-sm leading-relaxed">Your faculty creates groups and assigns you to a hotel role — Room Management, Front Desk, Maintenance, or Restaurant.</p>
              </div>
            </div>
            <div class="reveal flex gap-4">
              <div class="flex-shrink-0 w-10 h-10 bg-brand/20 rounded-xl flex items-center justify-center text-brand-light font-bold">2</div>
              <div>
                <h4 class="font-semibold mb-1">Choose a Default Template</h4>
                <p class="text-slate-400 font-light text-sm leading-relaxed">Select from two professionally designed hotel templates that will serve as the foundation for your team's project.</p>
              </div>
            </div>
            <div class="reveal flex gap-4">
              <div class="flex-shrink-0 w-10 h-10 bg-brand/20 rounded-xl flex items-center justify-center text-brand-light font-bold">3</div>
              <div>
                <h4 class="font-semibold mb-1">Customize & Redesign</h4>
                <p class="text-slate-400 font-light text-sm leading-relaxed">Tailor the template to match your team's unique hotel concept — adjust branding, layout, services, and operational details to bring your vision to life.</p>
              </div>
            </div>
            <div class="reveal flex gap-4">
              <div class="flex-shrink-0 w-10 h-10 bg-brand/20 rounded-xl flex items-center justify-center text-brand-light font-bold">4</div>
              <div>
                <h4 class="font-semibold mb-1">Simulate Operations</h4>
                <p class="text-slate-400 font-light text-sm leading-relaxed">Run realistic hotel operations and services within your customized hotel. Manage tasks across roles and track your team's performance throughout the simulation.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Faculty Side -->
        <div>
          <div class="reveal flex items-center gap-3 mb-8">
            <div class="w-10 h-10 bg-plum-accent rounded-xl flex items-center justify-center">
              <span class="iconify text-white text-xl" data-icon="mdi:school-outline"></span>
            </div>
            <h3 class="text-2xl font-bold">For Faculty</h3>
          </div>
          <div class="space-y-6">
            <div class="reveal flex gap-4">
              <div class="flex-shrink-0 w-10 h-10 bg-plum-accent/20 rounded-xl flex items-center justify-center text-plum-accent font-bold">1</div>
              <div>
                <h4 class="font-semibold mb-1">Create Groups</h4>
                <p class="text-slate-400 font-light text-sm leading-relaxed">Set up student groups, name them (e.g., "Grand Hotel — Group 3"), and assign members to specific hotel roles.</p>
              </div>
            </div>
            <div class="reveal flex gap-4">
              <div class="flex-shrink-0 w-10 h-10 bg-plum-accent/20 rounded-xl flex items-center justify-center text-plum-accent font-bold">2</div>
              <div>
                <h4 class="font-semibold mb-1">Guide Template Selection</h4>
                <p class="text-slate-400 font-light text-sm leading-relaxed">Oversee each group's choice of default template and review their customized hotel concepts before simulation begins.</p>
              </div>
            </div>
            <div class="reveal flex gap-4">
              <div class="flex-shrink-0 w-10 h-10 bg-plum-accent/20 rounded-xl flex items-center justify-center text-plum-accent font-bold">3</div>
              <div>
                <h4 class="font-semibold mb-1">Monitor the Simulation</h4>
                <p class="text-slate-400 font-light text-sm leading-relaxed">View real-time dashboards showing task completion rates per student, per group, and per role as they run their hotel operations.</p>
              </div>
            </div>
            <div class="reveal flex gap-4">
              <div class="flex-shrink-0 w-10 h-10 bg-plum-accent/20 rounded-xl flex items-center justify-center text-plum-accent font-bold">4</div>
              <div>
                <h4 class="font-semibold mb-1">Evaluate Results</h4>
                <p class="text-slate-400 font-light text-sm leading-relaxed">Review simulation outcomes, completion times, and overall performance. Export reports for grading and feedback sessions.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== FACULTY DASHBOARD PREVIEW ==================== -->
  <section id="faculty" class="py-24 md:py-32 px-6 md:px-8 bg-brand-soft">
    <div class="max-w-7xl mx-auto">
      <div class="reveal text-center max-w-3xl mx-auto mb-16">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-brand/15 bg-white/50 mb-6">
          <span class="iconify text-brand text-sm" data-icon="mdi:monitor-dashboard"></span>
          <span class="text-xs font-bold uppercase tracking-widest text-brand">Faculty Dashboard</span>
        </div>
        <h2 class="text-4xl md:text-5xl font-bold tracking-tight mb-6">Full Visibility, <span class="gradient-text">Zero Guesswork</span></h2>
        <p class="text-lg text-slate-500 font-light leading-relaxed">See every student's progress at a glance — from template selection to simulation performance. Our dashboard gives faculty the insights they need to guide and evaluate effectively.</p>
      </div>

      <div class="reveal">
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-200/80 overflow-hidden">
          <div class="px-6 md:px-8 py-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 brand-gradient rounded-lg flex items-center justify-center">
                <span class="iconify text-white text-sm" data-icon="mdi:view-dashboard-outline"></span>
              </div>
              <div>
                <h3 class="text-sm font-bold text-slate-700">Faculty Dashboard</h3>
                <p class="text-xs text-slate-400">Semester Overview — 3 Active Groups</p>
              </div>
            </div>
            <div class="hidden sm:flex items-center gap-2">
              <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
              <span class="text-xs text-slate-400">Live</span>
            </div>
          </div>

          <div class="p-6 md:p-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
              <div class="bg-brand-soft rounded-2xl p-4">
                <p class="text-xs font-medium text-slate-400 mb-1">Total Students</p>
                <p class="text-2xl font-bold text-brand">24</p>
              </div>
              <div class="bg-rose-50 rounded-2xl p-4">
                <p class="text-xs font-medium text-slate-400 mb-1">Active Groups</p>
                <p class="text-2xl font-bold text-rose-accent">3</p>
              </div>
              <div class="bg-purple-50 rounded-2xl p-4">
                <p class="text-xs font-medium text-slate-400 mb-1">Templates Used</p>
                <p class="text-2xl font-bold text-plum-accent">2</p>
              </div>
              <div class="bg-amber-50 rounded-2xl p-4">
                <p class="text-xs font-medium text-slate-400 mb-1">Avg. Completion</p>
                <p class="text-2xl font-bold text-amber-600">78%</p>
              </div>
            </div>

            <h4 class="text-sm font-bold text-slate-700 mb-4">Simulation Progress — Group 1: The Grand Azure</h4>
            <div class="space-y-3">
              <div class="flex items-center gap-4 bg-slate-50 rounded-xl p-3">
                <div class="w-8 h-8 bg-brand/10 rounded-full flex items-center justify-center text-xs font-bold text-brand">MS</div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between mb-1">
                    <p class="text-sm font-semibold text-slate-700">Maria Santos</p>
                    <span class="text-xs font-bold text-brand">92%</span>
                  </div>
                  <div class="w-full bg-slate-200 rounded-full h-2">
                    <div class="progress-bar h-2 rounded-full" style="width: 92%"></div>
                  </div>
                  <p class="text-[10px] text-slate-400 mt-1">Room Management — 12/13 tasks done</p>
                </div>
              </div>
              <div class="flex items-center gap-4 bg-slate-50 rounded-xl p-3">
                <div class="w-8 h-8 bg-rose-accent/10 rounded-full flex items-center justify-center text-xs font-bold text-rose-accent">JD</div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between mb-1">
                    <p class="text-sm font-semibold text-slate-700">John Dela Cruz</p>
                    <span class="text-xs font-bold text-rose-accent">85%</span>
                  </div>
                  <div class="w-full bg-slate-200 rounded-full h-2">
                    <div class="h-2 rounded-full bg-gradient-to-r from-rose-accent to-brand" style="width: 85%"></div>
                  </div>
                  <p class="text-[10px] text-slate-400 mt-1">Front Desk — 11/13 tasks done</p>
                </div>
              </div>
              <div class="flex items-center gap-4 bg-slate-50 rounded-xl p-3">
                <div class="w-8 h-8 bg-plum-accent/10 rounded-full flex items-center justify-center text-xs font-bold text-plum-accent">AR</div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between mb-1">
                    <p class="text-sm font-semibold text-slate-700">Ana Reyes</p>
                    <span class="text-xs font-bold text-plum-accent">67%</span>
                  </div>
                  <div class="w-full bg-slate-200 rounded-full h-2">
                    <div class="h-2 rounded-full bg-gradient-to-r from-plum-accent to-brand" style="width: 67%"></div>
                  </div>
                  <p class="text-[10px] text-slate-400 mt-1">Maintenance — 8/12 tasks done</p>
                </div>
              </div>
              <div class="flex items-center gap-4 bg-slate-50 rounded-xl p-3">
                <div class="w-8 h-8 bg-amber-500/10 rounded-full flex items-center justify-center text-xs font-bold text-amber-600">CM</div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between mb-1">
                    <p class="text-sm font-semibold text-slate-700">Carlo Mendez</p>
                    <span class="text-xs font-bold text-amber-600">54%</span>
                  </div>
                  <div class="w-full bg-slate-200 rounded-full h-2">
                    <div class="h-2 rounded-full bg-gradient-to-r from-amber-400 to-amber-600" style="width: 54%"></div>
                  </div>
                  <p class="text-[10px] text-amber-600 mt-1 font-medium">Restaurant — 7/13 tasks · Needs attention</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== TESTIMONIALS ==================== -->
  <section class="py-24 md:py-32 px-6 md:px-8 bg-white">
    <div class="max-w-7xl mx-auto">
      <div class="reveal text-center max-w-3xl mx-auto mb-16">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-brand/15 bg-brand/5 mb-6">
          <span class="iconify text-brand text-sm" data-icon="mdi:quote-open"></span>
          <span class="text-xs font-bold uppercase tracking-widest text-brand">What Users Say</span>
        </div>
        <h2 class="text-4xl md:text-5xl font-bold tracking-tight mb-6">Feedback from <span class="gradient-text">Real Users</span></h2>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="reveal bg-brand-soft rounded-3xl p-8 border border-brand/10 hover:-translate-y-1 transition-all duration-300">
          <div class="flex items-center gap-1 mb-4">
            <span class="iconify text-amber-400 text-lg" data-icon="mdi:star"></span>
            <span class="iconify text-amber-400 text-lg" data-icon="mdi:star"></span>
            <span class="iconify text-amber-400 text-lg" data-icon="mdi:star"></span>
            <span class="iconify text-amber-400 text-lg" data-icon="mdi:star"></span>
            <span class="iconify text-amber-400 text-lg" data-icon="mdi:star"></span>
          </div>
          <p class="text-slate-600 font-light leading-relaxed mb-6">"Being able to pick a template and redesign it around our own hotel concept made the project feel truly ours. The simulation felt like running a real hotel!"</p>
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full brand-gradient flex items-center justify-center text-white text-xs font-bold">MS</div>
            <div>
              <p class="font-semibold text-sm">Maria Santos</p>
              <p class="text-slate-400 text-xs">Student — Room Management</p>
            </div>
          </div>
        </div>

        <div class="reveal bg-rose-50 rounded-3xl p-8 border border-rose-accent/10 hover:-translate-y-1 transition-all duration-300">
          <div class="flex items-center gap-1 mb-4">
            <span class="iconify text-amber-400 text-lg" data-icon="mdi:star"></span>
            <span class="iconify text-amber-400 text-lg" data-icon="mdi:star"></span>
            <span class="iconify text-amber-400 text-lg" data-icon="mdi:star"></span>
            <span class="iconify text-amber-400 text-lg" data-icon="mdi:star"></span>
            <span class="iconify text-amber-400 text-lg" data-icon="mdi:star"></span>
          </div>
          <p class="text-slate-600 font-light leading-relaxed mb-6">"Finally, I can see each student's contribution throughout the simulation. The progress dashboard saves me hours of tracking manually."</p>
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-rose-accent flex items-center justify-center text-white text-xs font-bold">PL</div>
            <div>
              <p class="font-semibold text-sm">Prof. Lopez</p>
              <p class="text-slate-400 text-xs">Faculty — Hotel Operations</p>
            </div>
          </div>
        </div>

        <div class="reveal bg-purple-50 rounded-3xl p-8 border border-plum-accent/10 hover:-translate-y-1 transition-all duration-300">
          <div class="flex items-center gap-1 mb-4">
            <span class="iconify text-amber-400 text-lg" data-icon="mdi:star"></span>
            <span class="iconify text-amber-400 text-lg" data-icon="mdi:star"></span>
            <span class="iconify text-amber-400 text-lg" data-icon="mdi:star"></span>
            <span class="iconify text-amber-400 text-lg" data-icon="mdi:star"></span>
            <span class="iconify text-amber-400 text-lg" data-icon="mdi:star-half-full"></span>
          </div>
          <p class="text-slate-600 font-light leading-relaxed mb-6">"Customizing our hotel's concept and then simulating its operations taught me so much about coordination. The whole experience was incredibly hands-on."</p>
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-plum-accent flex items-center justify-center text-white text-xs font-bold">CM</div>
            <div>
              <p class="font-semibold text-sm">Carlo Mendez</p>
              <p class="text-slate-400 text-xs">Student — Restaurant</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== TEAM ==================== -->
  <section id="team" class="py-24 md:py-32 px-6 md:px-8 bg-slate-50">
    <div class="max-w-7xl mx-auto">
      <div class="reveal text-center max-w-3xl mx-auto mb-16">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-brand/15 bg-brand/5 mb-6">
          <span class="iconify text-brand text-sm" data-icon="mdi:account-group-outline"></span>
          <span class="text-xs font-bold uppercase tracking-widest text-brand">Development Team</span>
        </div>
        <h2 class="text-4xl md:text-5xl font-bold tracking-tight mb-6">Meet the <span class="gradient-text">Creators</span></h2>
        <p class="text-lg text-slate-500 font-light leading-relaxed">The team behind HMS-Learn — dedicated to bringing real-world hotel operations into the classroom.</p>
      </div>

      <!-- [Team section continues unchanged from original] -->
    </div>
  </section>

  <script>
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

    // Navbar background on scroll
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
      if (window.scrollY > 20) {
        navbar.classList.add('bg-white', 'shadow-sm', 'border-b', 'border-slate-100');
      } else {
        navbar.classList.remove('bg-white', 'shadow-sm', 'border-b', 'border-slate-100');
      }
    });
  </script>
</body>
</html>