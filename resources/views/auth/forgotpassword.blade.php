<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hotel Management System — Forgot Password</title>
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
    body { font-family: 'Manrope', sans-serif; }

    .brand-gradient { background: linear-gradient(135deg, #F472B6, #DB2777, #9D174D); }
    .brand-gradient-alt { background: linear-gradient(135deg, #FB7185, #DB2777, #A855F7); }

    .gradient-text {
      background: linear-gradient(135deg, #F472B6, #DB2777, #9D174D);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .input-field { transition: all 0.2s ease; }
    .input-field:focus {
      border-color: #DB2777;
      box-shadow: 0 0 0 3px rgba(219, 39, 119, 0.12);
    }
    .input-field.error {
      border-color: #EF4444;
      box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
    .input-field.success {
      border-color: #22C55E;
      box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
    }
    .input-field.checking {
      border-color: #F59E0B;
      box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
    }
    .input-field:disabled {
      background: #F8FAFC;
      color: #64748B;
      cursor: not-allowed;
      opacity: 0.8;
    }

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

    .strength-bar { transition: width 0.4s ease, background 0.4s ease; }

    @keyframes spin { to { transform: rotate(360deg); } }
    .spinner { animation: spin 0.7s linear infinite; }

    @keyframes scaleIn {
      0% { transform: scale(0); opacity: 0; }
      60% { transform: scale(1.15); }
      100% { transform: scale(1); opacity: 1; }
    }
    @keyframes fadeUp {
      0% { transform: translateY(20px); opacity: 0; }
      100% { transform: translateY(0); opacity: 1; }
    }
    .anim-scale { animation: scaleIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .anim-fade-1 { animation: fadeUp 0.5s ease-out 0.3s forwards; opacity: 0; }
    .anim-fade-2 { animation: fadeUp 0.5s ease-out 0.5s forwards; opacity: 0; }
    .anim-fade-3 { animation: fadeUp 0.5s ease-out 0.7s forwards; opacity: 0; }
    .anim-fade-4 { animation: fadeUp 0.5s ease-out 0.9s forwards; opacity: 0; }

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

    /* Reveal animation for password fields */
    .reveal-section {
      max-height: 0;
      opacity: 0;
      overflow: hidden;
      transition: max-height 0.6s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s ease, margin 0.4s ease;
      margin-top: 0;
    }
    .reveal-section.visible {
      max-height: 600px;
      opacity: 1;
      margin-top: 1.5rem;
    }

    /* Status icon fade */
    .status-icon {
      transition: opacity 0.2s ease, transform 0.2s ease;
    }
    .status-icon.hidden {
      opacity: 0;
      transform: scale(0.8);
      pointer-events: none;
      position: absolute;
    }
    .status-icon.visible {
      opacity: 1;
      transform: scale(1);
      position: relative;
    }

    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: #FDF2F8; }
    ::-webkit-scrollbar-thumb { background: #DB2777; border-radius: 99px; }
  </style>
</head>
<body class="min-h-screen bg-white flex">

  <!-- Toast Container -->
  <div id="toastContainer" class="fixed top-6 right-6 z-[200] flex flex-col gap-3 pointer-events-none"></div>


  <!-- ==================== LEFT PANEL ==================== -->
  <div class="hidden lg:flex lg:w-[45%] relative overflow-hidden brand-gradient-alt items-center justify-center p-12 xl:p-16">
    <div class="absolute inset-0">
      <div class="float-a absolute top-16 left-16 w-20 h-20 border-2 border-white/10 rounded-3xl"></div>
      <div class="float-b absolute top-32 right-24 w-14 h-14 bg-white/5 rounded-2xl"></div>
      <div class="float-c absolute bottom-32 left-24 w-16 h-16 border-2 border-white/10 rounded-full"></div>
      <div class="float-a absolute bottom-20 right-16 w-24 h-24 bg-white/5 rounded-3xl"></div>
      <div class="float-b absolute top-1/2 left-8 w-10 h-10 bg-white/10 rounded-xl"></div>
      <div class="float-c absolute top-1/3 right-12 w-12 h-12 border border-white/10 rounded-2xl"></div>
    </div>
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

    <div class="relative z-10 max-w-sm text-center">
      <img src="{{ asset('chtm-logoo.png') }}" alt="Hotel Management System logo" class="h-16 w-auto object-contain drop-shadow-sm mx-auto mb-8" />

      <h1 class="text-4xl font-extrabold text-white tracking-tight mb-3">Reset Your<br/>Password</h1>
      <p class="text-base text-white/60 font-light leading-relaxed mb-10">No worries — it happens to the best of us. We'll get you back into your account in no time.</p>

      <div class="space-y-3 text-left">
        <div class="flex items-center gap-3 px-4 py-3 bg-white/5 rounded-xl border border-white/10 backdrop-blur-sm">
          <span class="iconify text-white/70 text-lg" data-icon="mdi:shield-check-outline"></span>
          <p class="text-xs text-white/60 font-light">Your data is safe — we never share your credentials</p>
        </div>
        <div class="flex items-center gap-3 px-4 py-3 bg-white/5 rounded-xl border border-white/10 backdrop-blur-sm">
          <span class="iconify text-white/70 text-lg" data-icon="mdi:magnify"></span>
          <p class="text-xs text-white/60 font-light">We automatically verify your email as you type</p>
        </div>
        <div class="flex items-center gap-3 px-4 py-3 bg-white/5 rounded-xl border border-white/10 backdrop-blur-sm">
          <span class="iconify text-white/70 text-lg" data-icon="mdi:key-outline"></span>
          <p class="text-xs text-white/60 font-light">Choose a strong password you haven't used before</p>
        </div>
      </div>
    </div>
  </div>


  <!-- ==================== RIGHT PANEL ==================== -->
  <div class="w-full lg:w-[55%] flex flex-col min-h-screen">
    <!-- Top bar -->
    <div class="flex items-center justify-between px-6 md:px-12 py-5 border-b border-slate-100">
      <div class="flex items-center gap-2.5 lg:hidden">
        <img src="{{ asset('chtm-logoo.png') }}" alt="Hotel Management System logo" class="h-10 w-auto object-contain drop-shadow-sm" />
        <span class="text-sm font-bold tracking-tight">Hotel Management System</span>
      </div>
      <div class="hidden lg:block"></div>
      <a href="{{ route('login') }}" class="text-sm text-brand font-semibold hover:text-brand-dark transition-colors flex items-center gap-1">
        <span class="iconify" data-icon="mdi:arrow-left"></span>
        Back to Log In
      </a>
    </div>

    <!-- Form container -->
    <div class="flex-1 flex items-center justify-center px-6 md:px-12 py-8 md:py-12">
      <div class="w-full max-w-md">

        <!-- ============ MAIN FORM ============ -->
        <div id="formPanel" class="{{ session('password_reset') ? 'hidden' : '' }}">
          <div class="text-center mb-8">
            <div class="w-14 h-14 bg-brand-soft rounded-2xl flex items-center justify-center mb-5 mx-auto">
              <span class="iconify text-brand text-3xl" data-icon="mdi:lock-reset"></span>
            </div>
            <h2 class="text-3xl font-bold tracking-tight mb-2">Forgot your password?</h2>
            <p class="text-slate-500 font-light leading-relaxed">Enter your @hms.edu email and set your new password.</p>
          </div>

          <form id="resetForm" method="POST" action="{{ route('forgot-password.submit') }}" class="space-y-0">
            @csrf

            <!-- ===== EMAIL FIELD ===== -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Email Address</label>
              <div class="relative">
                <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:email-outline"></span>
                <input id="emailInput" name="email" type="email" placeholder="you@hms.edu" required autocomplete="email"
                  value="{{ old('email') }}"
                  class="input-field w-full h-12 pl-10 pr-11 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none" />

                <!-- Status icons inside input -->
                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 flex items-center">
                  <!-- Checking spinner -->
                  <div id="checkingIcon" class="status-icon hidden">
                    <span class="iconify spinner text-amber-500 text-lg" data-icon="mdi:loading"></span>
                  </div>
                  <!-- Success -->
                  <div id="emailSuccessIcon" class="status-icon hidden">
                    <div class="w-7 h-7 bg-green-100 rounded-lg flex items-center justify-center">
                      <span class="iconify text-green-600 text-base" data-icon="mdi:check-circle"></span>
                    </div>
                  </div>
                  <!-- Error -->
                  <div id="emailErrorIcon" class="status-icon hidden">
                    <div class="w-7 h-7 bg-red-100 rounded-lg flex items-center justify-center">
                      <span class="iconify text-red-500 text-base" data-icon="mdi:close-circle"></span>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Messages -->
              <div id="emailCheckingMsg" class="hidden flex items-center gap-1">
                <span class="iconify spinner text-amber-500 text-xs" data-icon="mdi:loading"></span>
                <span class="text-[10px] text-amber-600 font-medium">Checking your email...</span>
              </div>
              @error('email')
                <p class="text-[10px] text-red-500 font-medium flex items-center gap-1">
                  <span class="iconify text-xs" data-icon="mdi:alert-circle-outline"></span>
                  <span>{{ $message }}</span>
                </p>
              @enderror
              <p id="emailMissingMsg" class="text-[10px] text-red-500 font-medium hidden flex items-center gap-1">
                <span class="iconify text-xs" data-icon="mdi:alert-circle-outline"></span>
                <span>No account found with this email address.</span>
              </p>
              <p id="emailExistsMsg" class="text-[10px] text-green-600 font-medium hidden flex items-center gap-1">
                <span class="iconify text-xs" data-icon="mdi:check-circle-outline"></span>
                <span>Email verified. You can reset your password.</span>
              </p>
              <p class="text-[10px] text-slate-400 font-medium flex items-center gap-1">
                <span class="iconify text-xs" data-icon="mdi:information-outline"></span>
                <span>We only accept @hms.edu email addresses.</span>
              </p>
            </div>
            <!-- ===== PASSWORD FIELDS ===== -->
            <div id="passwordSection" class="space-y-4 mt-6">
              <div class="space-y-4">
                <!-- Divider -->
                <div class="flex items-center gap-3">
                  <div class="flex-1 h-px bg-slate-200"></div>
                  <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Set New Password</span>
                  <div class="flex-1 h-px bg-slate-200"></div>
                </div>

                <!-- New Password -->
                <div>
                  <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">New Password</label>
                  <div class="relative">
                    <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:lock-outline"></span>
                    <input id="newPassword" name="password" type="password" placeholder="Min. 8 characters" required
                      class="input-field w-full h-12 pl-10 pr-11 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none" />
                    <button type="button" onclick="togglePasswordVisibility('newPassword', this)" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                      <span class="iconify text-lg" data-icon="mdi:eye-off-outline"></span>
                    </button>
                  </div>
                  @error('password')
                    <p class="text-[10px] text-red-500 font-medium mt-1.5 flex items-center gap-1">
                      <span class="iconify text-xs" data-icon="mdi:alert-circle-outline"></span>
                      <span>{{ $message }}</span>
                    </p>
                  @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                  <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Confirm New Password</label>
                  <div class="relative">
                    <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:lock-check-outline"></span>
                    <input id="confirmPassword" name="password_confirmation" type="password" placeholder="Re-enter your new password" required
                      class="input-field w-full h-12 pl-10 pr-11 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none" />
                  </div>
                </div>

                <!-- Reset Button -->
                <button type="submit" id="resetBtn"
                  class="w-full h-12 brand-gradient text-white text-sm font-bold uppercase tracking-wider rounded-xl hover:scale-[1.02] hover:-translate-y-0.5 transition-all duration-150 shadow-lg shadow-brand/20 flex items-center justify-center gap-2 mt-2">
                  <span class="iconify text-lg" data-icon="mdi:lock-reset"></span>
                  Reset Password
                </button>
              </div>
            </div>

          </form>
        </div>


        <!-- ============ SUCCESS STATE ============ -->
        <div id="successPanel" class="{{ session('password_reset') ? '' : 'hidden' }}">
          <div class="text-center py-6">
            <div class="anim-scale w-24 h-24 mx-auto mb-8 brand-gradient rounded-full flex items-center justify-center shadow-xl shadow-brand/30">
              <span class="iconify text-white text-5xl" data-icon="mdi:check-bold"></span>
            </div>
            <h2 class="anim-fade-1 text-3xl font-bold tracking-tight mb-3">Password Reset!</h2>
            <p class="anim-fade-2 text-slate-500 font-light leading-relaxed max-w-sm mx-auto mb-8">
              Your password has been successfully updated. You can now log in with your new credentials.
            </p>
            <div class="anim-fade-3 bg-brand-soft rounded-2xl p-5 text-left max-w-sm mx-auto mb-8 border border-brand/10">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                  <span class="iconify text-green-600 text-xl" data-icon="mdi:shield-check"></span>
                </div>
                <div>
                  <p class="text-sm font-semibold text-slate-800">Account Secured</p>
                  <p class="text-xs text-slate-400 font-light">Password updated just now</p>
                </div>
              </div>
            </div>
            <div class="anim-fade-4 flex flex-col gap-3 max-w-sm mx-auto">
              <a href="{{ route('login') }}"
                class="h-12 brand-gradient text-white text-sm font-bold uppercase tracking-wider rounded-xl flex items-center justify-center gap-2 hover:scale-[1.02] transition-all shadow-lg shadow-brand/20">
                <span class="iconify text-lg" data-icon="mdi:login"></span>
                Log In Now
              </a>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Footer -->
    <div class="px-6 md:px-12 py-4 border-t border-slate-100">
      <p class="text-[10px] text-slate-400 text-center">&copy; 2025 Hotel Management System. All rights reserved.</p>
    </div>
  </div>


  <!-- ==================== SCRIPTS ==================== -->
  <script>
    const emailInput = document.getElementById('emailInput');
    const checkingIcon = document.getElementById('checkingIcon');
    const successIcon = document.getElementById('emailSuccessIcon');
    const errorIcon = document.getElementById('emailErrorIcon');
    const checkingMsg = document.getElementById('emailCheckingMsg');
    const missingMsg = document.getElementById('emailMissingMsg');
    const existsMsg = document.getElementById('emailExistsMsg');
    let debounceTimer = null;
    let isChecking = false;

    function resetEmailStatus() {
      emailInput.classList.remove('error', 'success', 'checking');
      checkingIcon.classList.add('hidden');
      checkingIcon.classList.remove('visible');
      successIcon.classList.add('hidden');
      successIcon.classList.remove('visible');
      errorIcon.classList.add('hidden');
      errorIcon.classList.remove('visible');
      checkingMsg.classList.add('hidden');
      missingMsg.classList.add('hidden');
      existsMsg.classList.add('hidden');
    }

    async function checkEmail(email) {
      if (isChecking) return;
      isChecking = true;

      resetEmailStatus();
      emailInput.classList.add('checking');
      checkingIcon.classList.remove('hidden');
      checkingIcon.classList.add('visible');
      checkingMsg.classList.remove('hidden');

      try {
        const response = await fetch(`{{ route('forgot-password.check-email') }}?email=${encodeURIComponent(email)}`);

        if (!response.ok) {
          throw new Error('Request failed');
        }

        const data = await response.json();

        checkingIcon.classList.add('hidden');
        checkingIcon.classList.remove('visible');
        checkingMsg.classList.add('hidden');
        emailInput.classList.remove('checking');

        if (data.exists) {
          emailInput.classList.add('success');
          successIcon.classList.remove('hidden');
          successIcon.classList.add('visible');
          existsMsg.classList.remove('hidden');
        } else {
          emailInput.classList.add('error');
          errorIcon.classList.remove('hidden');
          errorIcon.classList.add('visible');
          missingMsg.classList.remove('hidden');
        }
      } catch (error) {
        checkingIcon.classList.add('hidden');
        checkingIcon.classList.remove('visible');
        checkingMsg.classList.add('hidden');
        emailInput.classList.remove('checking');
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