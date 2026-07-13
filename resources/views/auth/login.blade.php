<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HMS-Learn — Log In</title>
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
        }
      }
    }
  </script>
  <style>
    ::selection { background: #DB2777; color: #fff; }
    html { scroll-behavior: smooth; }
    body { font-family: 'Manrope', sans-serif; }

    .brand-gradient { background: linear-gradient(135deg, #F472B6, #DB2777, #9D174D); }
    .brand-gradient-alt { background: linear-gradient(135deg, #FB7185, #DB2777, #A855F7); }

    .gradient-text {
      background: linear-gradient(135deg, #F472B6, #DB2777, #9D174D);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    /* Form panel slide */
    .form-panel {
      transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .form-panel.hidden-left {
      opacity: 0;
      transform: translateX(-30px);
      pointer-events: none;
      position: absolute;
      inset: 0;
    }
    .form-panel.hidden-right {
      opacity: 0;
      transform: translateX(30px);
      pointer-events: none;
      position: absolute;
      inset: 0;
    }
    .form-panel.active {
      opacity: 1;
      transform: translateX(0);
      pointer-events: all;
      position: relative;
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

    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: #FDF2F8; }
    ::-webkit-scrollbar-thumb { background: #DB2777; border-radius: 99px; }
  </style>
</head>
<body class="min-h-screen bg-white flex">

  <!-- ==================== TOAST ==================== -->
  <div id="toastContainer" class="fixed top-6 right-6 z-[200] flex flex-col gap-3 pointer-events-none"></div>


  <!-- ==================== LEFT PANEL — Visual ==================== -->
  <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden brand-gradient-alt items-center justify-center p-12">
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
      <img src="{{ asset('chtm-logoo.png') }}" alt="HMS-Learn logo" class="h-16 w-auto object-contain drop-shadow-sm mx-auto mb-8" />

      <h1 class="text-4xl font-extrabold text-white tracking-tight mb-4">HMS<span class="text-white/70">-Learn</span></h1>
      <p class="text-lg text-white/70 font-light leading-relaxed mb-10">Interactive Hospitality Task Management — assign, track, and manage hotel tasks across departments.</p>

      <!-- Role chips -->
      <div class="flex flex-wrap justify-center gap-2 mb-10">
        <div class="flex items-center gap-2 px-3 py-2 bg-white/10 rounded-xl border border-white/10 backdrop-blur-sm">
          <span class="iconify text-white text-sm" data-icon="mdi:bed-outline"></span>
          <span class="text-xs font-semibold text-white/90">Room Mgmt</span>
        </div>
        <div class="flex items-center gap-2 px-3 py-2 bg-white/10 rounded-xl border border-white/10 backdrop-blur-sm">
          <span class="iconify text-white text-sm" data-icon="mdi:desk"></span>
          <span class="text-xs font-semibold text-white/90">Front Desk</span>
        </div>
        <div class="flex items-center gap-2 px-3 py-2 bg-white/10 rounded-xl border border-white/10 backdrop-blur-sm">
          <span class="iconify text-white text-sm" data-icon="mdi:broom"></span>
          <span class="text-xs font-semibold text-white/90">Maintenance</span>
        </div>
        <div class="flex items-center gap-2 px-3 py-2 bg-white/10 rounded-xl border border-white/10 backdrop-blur-sm">
          <span class="iconify text-white text-sm" data-icon="mdi:silverware-fork-knife"></span>
          <span class="text-xs font-semibold text-white/90">Restaurant</span>
        </div>
      </div>

      <!-- Testimonial -->
      <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 text-left">
        <p class="text-sm text-white/80 font-light leading-relaxed italic mb-3">"Working through department templates made it feel like running a real hotel. I finally understood how teams coordinate."</p>
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center text-xs font-bold text-white">MS</div>
          <div>
            <p class="text-xs font-semibold text-white">Maria Santos</p>
            <p class="text-[10px] text-white/50">Student — Room Management</p>
          </div>
        </div>
      </div>
    </div>
  </div>


  <!-- ==================== RIGHT PANEL — Auth Forms ==================== -->
  <div class="w-full lg:w-1/2 flex items-center justify-center p-6 md:p-12 relative">
    <!-- Background decoration -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-pink-50 to-transparent rounded-full blur-3xl opacity-60"></div>
    <div class="absolute bottom-0 left-0 w-48 h-48 bg-gradient-to-tr from-purple-50 to-transparent rounded-full blur-3xl opacity-40"></div>

    <div class="w-full max-w-md relative z-10">
      <!-- Mobile logo -->
      <div class="flex items-center gap-2.5 mb-8 lg:hidden">
        <img src="{{ asset('chtm-logoo.png') }}" alt="HMS-Learn logo" class="h-10 w-auto object-contain drop-shadow-sm" />
        <span class="text-xl font-bold tracking-tight">HMS<span class="text-brand">-Learn</span></span>
      </div>

      <!-- Back to home -->
      <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-brand transition-colors mb-8">
        <span class="iconify" data-icon="mdi:arrow-left"></span>
        Back
      </a>

      <!-- ============ LOGIN FORM ============ -->
      <div id="loginPanel" class="form-panel active">
        <div class="mb-8">
          <h2 class="text-3xl font-bold tracking-tight mb-2">Welcome back</h2>
          <p class="text-slate-500 font-light">Log in to manage your hotel tasks and track progress.</p>
        </div>

        <form id="loginForm" method="POST" action="{{ route('login.submit') }}" class="space-y-5">
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
        <!-- Switch to signup -->
        <p class="text-center text-sm text-slate-400 mt-8">
          Don't have an account?
          <a href="{{ route('signup') }}" class="text-brand font-semibold hover:text-brand-dark transition-colors">Create one</a>
        </p>
      </div>


      <!-- ============ SIGNUP FORM ============ -->
      <div id="signupPanel" class="form-panel hidden-right">
        <div class="mb-8">
          <h2 class="text-3xl font-bold tracking-tight mb-2">Create account</h2>
          <p class="text-slate-500 font-light">Join HMS-Learn and start managing hotel tasks with your team.</p>
        </div>

        <form id="signupForm" onsubmit="handleSignup(event)" class="space-y-4">
          <!-- Full Name -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Full Name</label>
            <div class="relative">
              <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:account-outline"></span>
              <input id="signupName" type="text" placeholder="Juan Dela Cruz" required
                class="input-field w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none" />
            </div>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">School Email</label>
            <div class="relative">
              <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:email-outline"></span>
              <input id="signupEmail" type="email" placeholder="you@school.edu" required
                class="input-field w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none" />
            </div>
          </div>

          <!-- Role Selection -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">I am a</label>
            <div class="grid grid-cols-2 gap-3">
              <label class="role-card cursor-pointer">
                <input type="radio" name="signupRole" value="student" class="sr-only" checked />
                <div class="flex flex-col items-center gap-2 p-4 bg-slate-50 border-2 border-slate-200 rounded-2xl transition-all duration-200 hover:border-brand/30">
                  <div class="role-icon w-10 h-10 bg-slate-200 rounded-xl flex items-center justify-center transition-all duration-200">
                    <span class="iconify text-slate-500 text-xl" data-icon="mdi:account-outline"></span>
                  </div>
                  <span class="role-label text-sm font-semibold text-slate-500 transition-colors">Student</span>
                </div>
              </label>
              <label class="role-card cursor-pointer">
                <input type="radio" name="signupRole" value="faculty" class="sr-only" />
                <div class="flex flex-col items-center gap-2 p-4 bg-slate-50 border-2 border-slate-200 rounded-2xl transition-all duration-200 hover:border-plum-accent/30">
                  <div class="role-icon w-10 h-10 bg-slate-200 rounded-xl flex items-center justify-center transition-all duration-200">
                    <span class="iconify text-slate-500 text-xl" data-icon="mdi:school-outline"></span>
                  </div>
                  <span class="role-label text-sm font-semibold text-slate-500 transition-colors">Faculty</span>
                </div>
              </label>
            </div>
          </div>

          <!-- Student ID (conditional) -->
          <div id="studentIdField">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Student ID</label>
            <div class="relative">
              <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:badge-account-outline"></span>
              <input id="signupStudentId" type="text" placeholder="e.g. 2025-00123"
                class="input-field w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none" />
            </div>
          </div>

          <!-- Password -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Password</label>
            <div class="relative">
              <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:lock-outline"></span>
              <input id="signupPassword" type="password" placeholder="Min. 8 characters" required
                oninput="checkPasswordStrength(this.value)"
                class="input-field w-full h-12 pl-10 pr-11 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none" />
              <button type="button" onclick="togglePasswordVisibility('signupPassword', this)" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                <span class="iconify text-lg" data-icon="mdi:eye-off-outline"></span>
              </button>
            </div>
            <!-- Password strength -->
            <div class="mt-2">
              <div class="flex gap-1.5">
                <div class="h-1 flex-1 bg-slate-200 rounded-full overflow-hidden">
                  <div id="strengthBar1" class="strength-bar h-full w-0 rounded-full"></div>
                </div>
                <div class="h-1 flex-1 bg-slate-200 rounded-full overflow-hidden">
                  <div id="strengthBar2" class="strength-bar h-full w-0 rounded-full"></div>
                </div>
                <div class="h-1 flex-1 bg-slate-200 rounded-full overflow-hidden">
                  <div id="strengthBar3" class="strength-bar h-full w-0 rounded-full"></div>
                </div>
                <div class="h-1 flex-1 bg-slate-200 rounded-full overflow-hidden">
                  <div id="strengthBar4" class="strength-bar h-full w-0 rounded-full"></div>
                </div>
              </div>
              <p id="strengthText" class="text-[10px] font-medium mt-1.5 text-slate-400"></p>
            </div>
          </div>

          <!-- Confirm Password -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Confirm Password</label>
            <div class="relative">
              <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:lock-check-outline"></span>
              <input id="signupConfirm" type="password" placeholder="Re-enter your password" required
                oninput="checkPasswordMatch()"
                class="input-field w-full h-12 pl-10 pr-11 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none" />
              <span id="matchIcon" class="absolute right-3.5 top-1/2 -translate-y-1/2 hidden">
                <span class="iconify text-lg" data-icon=""></span>
              </span>
            </div>
            <p id="matchText" class="text-[10px] font-medium mt-1.5 hidden"></p>
          </div>

          <!-- Terms -->
          <label class="flex items-start gap-2.5 cursor-pointer">
            <input type="checkbox" id="termsCheck" class="w-4 h-4 mt-0.5 rounded border-slate-300 text-brand focus:ring-brand/30" />
            <span class="text-xs text-slate-500 leading-relaxed">I agree to the <a href="#" class="text-brand font-semibold hover:underline">Terms of Service</a> and <a href="#" class="text-brand font-semibold hover:underline">Privacy Policy</a></span>
          </label>

          <!-- Submit -->
          <button type="submit" id="signupBtn"
            class="w-full h-12 brand-gradient text-white text-sm font-bold uppercase tracking-wider rounded-xl hover:scale-[1.02] hover:-translate-y-0.5 transition-all duration-150 shadow-lg shadow-brand/20 flex items-center justify-center gap-2">
            <span id="signupBtnText">Create Account</span>
            <span id="signupSpinner" class="hidden">
              <span class="iconify spinner text-lg" data-icon="mdi:loading"></span>
            </span>
          </button>
        </form>

        <!-- Switch to login -->
        <p class="text-center text-sm text-slate-400 mt-6">
          Already have an account?
          <button onclick="switchToLogin()" class="text-brand font-semibold hover:text-brand-dark transition-colors">Log in</button>
        </p>
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


  <!-- ==================== SCRIPTS ==================== -->
  <script>
    // === Panel Switching ===
    function switchToSignup() {
      document.getElementById('loginPanel').classList.remove('active');
      document.getElementById('loginPanel').classList.add('hidden-left');
      document.getElementById('signupPanel').classList.remove('hidden-right');
      document.getElementById('signupPanel').classList.add('active');
      document.getElementById('forgotPanel').classList.remove('active');
      document.getElementById('forgotPanel').classList.add('hidden-left');
    }

    function switchToLogin() {
      document.getElementById('signupPanel').classList.remove('active');
      document.getElementById('signupPanel').classList.add('hidden-right');
      document.getElementById('forgotPanel').classList.remove('active');
      document.getElementById('forgotPanel').classList.add('hidden-left');
      document.getElementById('loginPanel').classList.remove('hidden-left');
      document.getElementById('loginPanel').classList.add('active');
    }

    function showForgotPassword() {
      document.getElementById('loginPanel').classList.remove('active');
      document.getElementById('loginPanel').classList.add('hidden-right');
      document.getElementById('forgotPanel').classList.remove('hidden-left');
      document.getElementById('forgotPanel').classList.add('active');
    }

    // === Toggle Password Visibility ===
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

    // === Password Strength ===
    function checkPasswordStrength(password) {
      let score = 0;
      if (password.length >= 8) score++;
      if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
      if (/\d/.test(password)) score++;
      if (/[^a-zA-Z0-9]/.test(password)) score++;

      const bars = [
        document.getElementById('strengthBar1'),
        document.getElementById('strengthBar2'),
        document.getElementById('strengthBar3'),
        document.getElementById('strengthBar4'),
      ];
      const text = document.getElementById('strengthText');

      const levels = [
        { width: '0%', color: '#E2E8F0', label: '', textColor: '#94A3B8' },
        { width: '100%', color: '#EF4444', label: 'Weak — add uppercase, numbers & symbols', textColor: '#EF4444' },
        { width: '100%', color: '#F59E0B', label: 'Fair — add more variety', textColor: '#F59E0B' },
        { width: '100%', color: '#3B82F6', label: 'Good — almost there', textColor: '#3B82F6' },
        { width: '100%', color: '#22C55E', label: 'Strong — you\'re all set!', textColor: '#22C55E' },
      ];

      bars.forEach((bar, i) => {
        if (i < score) {
          bar.style.width = levels[score].width;
          bar.style.background = levels[score].color;
        } else {
          bar.style.width = '0%';
          bar.style.background = 'transparent';
        }
      });

      if (password.length === 0) {
        text.textContent = '';
      } else {
        text.textContent = levels[score].label;
        text.style.color = levels[score].textColor;
      }
    }

    // === Password Match ===
    function checkPasswordMatch() {
      const pass = document.getElementById('signupPassword').value;
      const confirm = document.getElementById('signupConfirm').value;
      const icon = document.getElementById('matchIcon');
      const text = document.getElementById('matchText');

      if (confirm.length === 0) {
        icon.classList.add('hidden');
        text.classList.add('hidden');
        return;
      }

      icon.classList.remove('hidden');
      text.classList.remove('hidden');

      const iconEl = icon.querySelector('.iconify');

      if (pass === confirm) {
        iconEl.setAttribute('data-icon', 'mdi:check-circle');
        iconEl.style.color = '#22C55E';
        text.textContent = 'Passwords match';
        text.style.color = '#22C55E';
      } else {
        iconEl.setAttribute('data-icon', 'mdi:close-circle');
        iconEl.style.color = '#EF4444';
        text.textContent = 'Passwords don\'t match';
        text.style.color = '#EF4444';
      }
    }

    // === Role toggle: show/hide Student ID ===
    document.querySelectorAll('input[name="signupRole"]').forEach(radio => {
      radio.addEventListener('change', function() {
        const studentField = document.getElementById('studentIdField');
        if (this.value === 'student') {
          studentField.style.display = '';
        } else {
          studentField.style.display = 'none';
        }
      });
    });

    // === Toast Notifications ===
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

    // === Handle Login ===
    function handleLogin(e) {
      e.preventDefault();
      const email = document.getElementById('loginEmail').value;
      const password = document.getElementById('loginPassword').value;
      const btn = document.getElementById('loginBtn');
      const btnText = document.getElementById('loginBtnText');
      const spinner = document.getElementById('loginSpinner');

      // Show loading
      btn.disabled = true;
      btnText.textContent = 'Logging in...';
      spinner.classList.remove('hidden');

      // Simulate API call
      setTimeout(() => {
        btn.disabled = false;
        btnText.textContent = 'Log In';
        spinner.classList.add('hidden');

        if (email && password.length >= 8) {
          showToast('Welcome back! Redirecting to your dashboard...', 'success');
        } else {
          showToast('Invalid credentials. Please try again.', 'error');
        }
      }, 1500);
    }

    // === Handle Signup ===
    function handleSignup(e) {
      e.preventDefault();
      const name = document.getElementById('signupName').value;
      const email = document.getElementById('signupEmail').value;
      const password = document.getElementById('signupPassword').value;
      const confirm = document.getElementById('signupConfirm').value;
      const terms = document.getElementById('termsCheck').checked;
      const btn = document.getElementById('signupBtn');
      const btnText = document.getElementById('signupBtnText');
      const spinner = document.getElementById('signupSpinner');

      // Validation
      if (!terms) {
        showToast('Please agree to the Terms of Service.', 'error');
        return;
      }
      if (password !== confirm) {
        showToast('Passwords don\'t match.', 'error');
        return;
      }
      if (password.length < 8) {
        showToast('Password must be at least 8 characters.', 'error');
        return;
      }

      // Show loading
      btn.disabled = true;
      btnText.textContent = 'Creating account...';
      spinner.classList.remove('hidden');

      // Simulate API call
      setTimeout(() => {
        btn.disabled = false;
        btnText.textContent = 'Create Account';
        spinner.classList.add('hidden');

        showToast('Account created! Please check your email to verify.', 'success');
        setTimeout(() => switchToLogin(), 2000);
      }, 2000);
    }

    // === Handle Forgot Password ===
    function handleForgot(e) {
      e.preventDefault();
      showToast('Reset link sent! Check your inbox.', 'info');
    }
  </script>
</body>
</html>