<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hotel Management System — Log In</title>
  <link rel="icon" type="image/png" href="{{ asset('chtm-logoo.png') }}" />
  <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
  <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    ::selection { background: #DB2777; color: #fff; }
    html, body {
      margin: 0;
      padding: 0;
      width: 100%;
      height: 100%;
      overflow: hidden;
    }
    body {
      font-family: 'Manrope', sans-serif;
      position: fixed;
      inset: 0;
      overflow: hidden;
    }
    /* Belt-and-suspenders: never paint a page scrollbar on auth */
    html::-webkit-scrollbar,
    body::-webkit-scrollbar {
      width: 0 !important;
      height: 0 !important;
      display: none !important;
    }
    html, body {
      scrollbar-width: none;
      -ms-overflow-style: none;
    }
    .auth-shell {
      width: 100%;
      height: 100%;
      overflow: hidden;
      display: flex;
    }
    .auth-panel {
      overflow: hidden;
      min-width: 0;
      contain: paint;
    }
    .form-stack {
      position: relative;
      overflow: hidden;
      width: 100%;
    }

    .brand-gradient { background: linear-gradient(135deg, #F472B6, #DB2777, #9D174D); }
    .brand-gradient-alt { background: linear-gradient(135deg, #FB7185, #DB2777, #A855F7); }

    .gradient-text {
      background: linear-gradient(135deg, #F472B6, #DB2777, #9D174D);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    /* Form panel slide — keep inactive panels out of document overflow */
    .form-panel {
      transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .form-panel.hidden-left,
    .form-panel.hidden-right {
      opacity: 0;
      pointer-events: none;
      position: absolute;
      left: 0;
      right: 0;
      top: 0;
      visibility: hidden;
      height: 0;
      overflow: hidden;
    }
    .form-panel.hidden-left { transform: translateX(-30px); }
    .form-panel.hidden-right { transform: translateX(30px); }
    .form-panel.active {
      opacity: 1;
      transform: translateX(0);
      pointer-events: all;
      position: relative;
      visibility: visible;
      height: auto;
      overflow: visible;
    }

    /* Input focus ring */
    .input-field {
      transition: all 0.2s ease;
    }
    .input-field:focus {
      border-color: #DB2777;
      box-shadow: 0 0 0 3px rgba(219, 39, 119, 0.12);
    }

    /* Floating labels */
    .float-label {
      position: relative;
    }
    .float-label input:focus + label,
    .float-label input:not(:placeholder-shown) + label {
      transform: translateY(-28px) scale(0.85);
      color: #DB2777;
      background: white;
      padding: 0 6px;
    }
    .float-label label {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      transition: all 0.2s ease;
      pointer-events: none;
      color: #94A3B8;
      font-size: 14px;
    }

    /* Decorative floating shapes */
    @keyframes floatA {
      0%, 100% { transform: translate(0, 0) rotate(0deg); }
      33% { transform: translate(15px, -20px) rotate(5deg); }
      66% { transform: translate(-10px, 10px) rotate(-3deg); }
    }
    @keyframes floatB {
      0%, 100% { transform: translate(0, 0) rotate(0deg); }
      33% { transform: translate(-20px, 15px) rotate(-5deg); }
      66% { transform: translate(10px, -10px) rotate(3deg); }
    }
    @keyframes floatC {
      0%, 100% { transform: translate(0, 0) rotate(0deg); }
      50% { transform: translate(10px, 15px) rotate(8deg); }
    }
    .float-a { animation: floatA 8s ease-in-out infinite; }
    .float-b { animation: floatB 10s ease-in-out infinite; }
    .float-c { animation: floatC 6s ease-in-out infinite; }

    /* Password strength */
    .strength-bar {
      transition: width 0.4s ease, background 0.4s ease;
    }

    /* Toast */
    @keyframes toastIn {
      0% { transform: translateY(20px); opacity: 0; }
      100% { transform: translateY(0); opacity: 1; }
    }
    @keyframes toastOut {
      0% { transform: translateY(0); opacity: 1; }
      100% { transform: translateY(-20px); opacity: 0; }
    }
    .toast-in { animation: toastIn 0.4s ease-out forwards; }
    .toast-out { animation: toastOut 0.3s ease-in forwards; }

    /* Role card */
    .role-card input:checked + div {
      border-color: #DB2777;
      background: #FDF2F8;
      box-shadow: 0 0 0 3px rgba(219, 39, 119, 0.12);
    }
    .role-card input:checked + div .role-icon {
      background: linear-gradient(135deg, #F472B6, #DB2777);
      color: white;
    }
    .role-card input:checked + div .role-label {
      color: #DB2777;
    }

    /* Spinner */
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    .spinner {
      animation: spin 0.7s linear infinite;
    }
  </style>
</head>
<body class="bg-white">

  <div class="auth-shell">
  <!-- ==================== TOAST ==================== -->
  <div id="toastContainer" class="fixed top-6 right-6 z-[200] flex flex-col gap-3 pointer-events-none"></div>


  <!-- ==================== LEFT PANEL — Visual ==================== -->
  <div class="auth-panel hidden lg:flex lg:w-1/2 h-full relative overflow-hidden brand-gradient-alt items-center justify-center p-10">
    <!-- Decorative shapes -->
    <div class="absolute inset-0">
      <div class="float-a absolute top-16 left-16 w-20 h-20 border-2 border-white/10 rounded-3xl"></div>
      <div class="float-b absolute top-32 right-24 w-14 h-14 bg-white/5 rounded-2xl"></div>
      <div class="float-c absolute bottom-32 left-24 w-16 h-16 border-2 border-white/10 rounded-full"></div>
      <div class="float-a absolute bottom-20 right-16 w-24 h-24 bg-white/5 rounded-3xl"></div>
      <div class="float-b absolute top-1/2 left-8 w-10 h-10 bg-white/10 rounded-xl"></div>
      <div class="float-c absolute top-1/3 right-12 w-12 h-12 border border-white/10 rounded-2xl"></div>
    </div>

    <!-- Grid pattern overlay -->
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

    <div class="relative z-10 max-w-md text-center">
      <!-- Logo -->
      <img src="{{ asset('chtm-logoo.png') }}" alt="Hotel Management System logo" class="h-16 w-auto object-contain drop-shadow-sm mx-auto mb-8" />

      <h1 class="text-3xl font-extrabold text-white tracking-tight mb-4">Hotel Management System</h1>
      <p class="text-lg text-white/70 font-light leading-relaxed mb-10">Interactive Hospitality Task Management — drag, drop, and manage hotel tasks across departments.</p>

      <!-- Role chips -->
      
    </div>
  </div>


  <!-- ==================== RIGHT PANEL — Auth Forms ==================== -->
  <div class="auth-panel w-full lg:w-1/2 h-full flex items-center justify-center p-5 md:p-8 relative overflow-hidden">
    <!-- Background decoration (no blur — filters expand scroll overflow) -->
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
      <div class="absolute -top-16 -right-16 w-56 h-56 bg-gradient-to-bl from-pink-50 to-transparent rounded-full opacity-60"></div>
      <div class="absolute -bottom-16 -left-16 w-44 h-44 bg-gradient-to-tr from-purple-50 to-transparent rounded-full opacity-40"></div>
    </div>

    <div class="form-stack w-full max-w-md relative z-10">
      <!-- Mobile logo -->
      <div class="flex items-center gap-2.5 mb-5 lg:hidden">
        <img src="{{ asset('chtm-logoo.png') }}" alt="Hotel Management System logo" class="h-9 w-auto object-contain drop-shadow-sm" />
        <span class="text-lg font-bold tracking-tight">Hotel Management System</span>
      </div>

      <!-- Back to home -->
      <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-brand transition-colors mb-5">
        <span class="iconify" data-icon="mdi:arrow-left"></span>
        Back
      </a>

      <!-- ============ LOGIN FORM ============ -->
      <div id="loginPanel" class="form-panel active">
        <div class="mb-6">
          <h2 class="text-3xl font-bold tracking-tight mb-2">Welcome back</h2>
          <p class="text-slate-500 font-light">Log in to manage your hotel tasks and track progress.</p>
        </div>

        <form id="loginForm" method="POST" action="{{ route('login.submit') }}" class="space-y-4">
          @csrf

          @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
              {{ $errors->first() }}
            </div>
          @endif
          <!-- Email -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Email Address</label>
            <div class="relative">
              <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:email-outline"></span>
              <input id="loginEmail" name="email" type="email" placeholder="you@school.edu" required value="{{ old('email') }}"
                class="input-field w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none" />
            </div>
          </div>

          <!-- Password -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Password</label>
              <a href="{{ route('forgot-password') }}" class="text-xs text-brand font-semibold hover:text-brand-dark transition-colors">Forgot password?</a>
            </div>
            <div class="relative">
              <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:lock-outline"></span>
              <input id="loginPassword" name="password" type="password" placeholder="Enter your password" required
                class="input-field w-full h-12 pl-10 pr-11 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none" />
              <button type="button" onclick="togglePasswordVisibility('loginPassword', this)" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                <span class="iconify text-lg" data-icon="mdi:eye-off-outline"></span>
              </button>
            </div>
          </div>

          <!-- Remember me -->
          <label class="flex items-center gap-2.5 cursor-pointer">
            <input name="remember" type="checkbox" class="w-4 h-4 rounded border-slate-300 text-brand focus:ring-brand/30" />
            <span class="text-sm text-slate-500">Keep me logged in</span>
          </label>

          <!-- Submit -->
          <button type="submit" id="loginBtn"
            class="w-full h-12 brand-gradient text-white text-sm font-bold uppercase tracking-wider rounded-xl hover:scale-[1.02] hover:-translate-y-0.5 transition-all duration-150 shadow-lg shadow-brand/20 flex items-center justify-center gap-2">
            <span id="loginBtnText">Log In</span>
            <span id="loginSpinner" class="hidden">
              <span class="iconify spinner text-lg" data-icon="mdi:loading"></span>
            </span>
          </button>
        </form>
      </div>


      <!-- ============ FORGOT PASSWORD ============ -->
      <div id="forgotPanel" class="form-panel hidden-left">
        <div class="mb-8">
          <button onclick="switchToLogin()" class="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-brand transition-colors mb-6">
            <span class="iconify" data-icon="mdi:arrow-left"></span>
            Back to Log In
          </button>
          <h2 class="text-3xl font-bold tracking-tight mb-2">Reset password</h2>
          <p class="text-slate-500 font-light">Enter your email and we'll send you a link to reset your password.</p>
        </div>

        <form onsubmit="handleForgot(event)" class="space-y-5">
          <!-- Email -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Email Address</label>
            <div class="relative">
              <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:email-outline"></span>
              <input type="email" placeholder="you@school.edu" required
                class="input-field w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none" />
            </div>
          </div>

          <!-- Submit -->
          <button type="submit"
            class="w-full h-12 brand-gradient text-white text-sm font-bold uppercase tracking-wider rounded-xl hover:scale-[1.02] hover:-translate-y-0.5 transition-all duration-150 shadow-lg shadow-brand/20 flex items-center justify-center gap-2">
            <span class="iconify text-lg" data-icon="mdi:email-send-outline"></span>
            Send Reset Link
          </button>
        </form>

        <div class="mt-8 p-4 bg-amber-50 border border-amber-200 rounded-xl">
          <div class="flex gap-3">
            <span class="iconify text-amber-500 text-xl flex-shrink-0 mt-0.5" data-icon="mdi:information-outline"></span>
            <div>
              <p class="text-sm font-semibold text-amber-700 mb-0.5">Check your inbox</p>
              <p class="text-xs text-amber-600/80 font-light">If an account exists with this email, you'll receive a password reset link within a few minutes.</p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
  </div>


  <!-- ==================== SCRIPTS ==================== -->
  <script>
    function switchToLogin() {
      document.getElementById('forgotPanel').classList.remove('active');
      document.getElementById('forgotPanel').classList.add('hidden-left');
      document.getElementById('loginPanel').classList.remove('hidden-left');
      document.getElementById('loginPanel').classList.remove('hidden-right');
      document.getElementById('loginPanel').classList.add('active');
    }

    function showForgotPassword() {
      document.getElementById('loginPanel').classList.remove('active');
      document.getElementById('loginPanel').classList.add('hidden-right');
      document.getElementById('forgotPanel').classList.remove('hidden-left');
      document.getElementById('forgotPanel').classList.add('active');
    }

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

    function showToast(message, type = 'success') {
      const container = document.getElementById('toastContainer');
      const toast = document.createElement('div');

      const icons = {
        success: 'mdi:check-circle',
        error: 'mdi:alert-circle',
        info: 'mdi:information',
      };
      const colors = {
        success: { bg: 'bg-green-50', border: 'border-green-200', icon: 'text-green-500', text: 'text-green-700' },
        error: { bg: 'bg-red-50', border: 'border-red-200', icon: 'text-red-500', text: 'text-red-700' },
        info: { bg: 'bg-blue-50', border: 'border-blue-200', icon: 'text-blue-500', text: 'text-blue-700' },
      };

      const c = colors[type];
      toast.className = `toast-in pointer-events-auto flex items-center gap-3 px-5 py-3.5 ${c.bg} border ${c.border} rounded-xl shadow-lg`;
      toast.innerHTML = `
        <span class="iconify ${c.icon} text-xl" data-icon="${icons[type]}"></span>
        <p class="text-sm font-semibold ${c.text}">${message}</p>
      `;

      container.appendChild(toast);

      setTimeout(() => {
        toast.classList.remove('toast-in');
        toast.classList.add('toast-out');
        setTimeout(() => toast.remove(), 300);
      }, 4000);
    }

    function handleForgot(e) {
      e.preventDefault();
      showToast('Reset link sent! Check your inbox.', 'info');
    }
  </script>
</body>
</html>