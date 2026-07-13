<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HMS-Learn — Sign Up</title>
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

    @keyframes panelIn {
      0% { opacity: 0; transform: translateY(30px) scale(0.98); filter: blur(8px); }
      100% { opacity: 1; transform: translateY(0) scale(1); filter: blur(0); }
    }
    .panel-enter { animation: panelIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

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
    <!-- Decorative shapes -->
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
      <!-- Logo -->
      <img src="{{ asset('chtm-logoo.png') }}" alt="HMS-Learn logo" class="h-16 w-auto object-contain drop-shadow-sm mx-auto mb-8" />

      <h1 class="text-4xl font-extrabold text-white tracking-tight mb-3">HMS<span class="text-white/70">-Learn</span></h1>
      <p class="text-base text-white/60 font-light leading-relaxed mb-10">Interactive Hospitality Task Management System for Students</p>

      <!-- Feature list -->
      <div class="text-left space-y-4 mb-10">
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
            <span class="iconify text-white text-sm" data-icon="mdi:clipboard-check-outline"></span>
          </div>
          <div class="text-left">
            <p class="text-sm font-semibold text-white/90">Department Tasks</p>
            <p class="text-xs text-white/50 font-light">Organize and complete tasks across hotel departments</p>
          </div>
        </div>
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
            <span class="iconify text-white text-sm" data-icon="mdi:account-group-outline"></span>
          </div>
          <div class="text-left">
            <p class="text-sm font-semibold text-white/90">Group Collaboration</p>
            <p class="text-xs text-white/50 font-light">Work with your team in real hotel departments</p>
          </div>
        </div>
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
            <span class="iconify text-white text-sm" data-icon="mdi:chart-timeline-variant-shimmer"></span>
          </div>
          <div class="text-left">
            <p class="text-sm font-semibold text-white/90">Track Your Progress</p>
            <p class="text-xs text-white/50 font-light">Faculty monitors your task completion live</p>
          </div>
        </div>
      </div>

      <!-- Role chips -->
      <div class="flex flex-wrap justify-center gap-2">
        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-white/10 rounded-lg border border-white/10 backdrop-blur-sm">
          <span class="iconify text-white text-xs" data-icon="mdi:bed-outline"></span>
          <span class="text-[11px] font-semibold text-white/80">Rooms</span>
        </div>
        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-white/10 rounded-lg border border-white/10 backdrop-blur-sm">
          <span class="iconify text-white text-xs" data-icon="mdi:desk"></span>
          <span class="text-[11px] font-semibold text-white/80">Front Desk</span>
        </div>
        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-white/10 rounded-lg border border-white/10 backdrop-blur-sm">
          <span class="iconify text-white text-xs" data-icon="mdi:broom"></span>
          <span class="text-[11px] font-semibold text-white/80">Maintenance</span>
        </div>
        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-white/10 rounded-lg border border-white/10 backdrop-blur-sm">
          <span class="iconify text-white text-xs" data-icon="mdi:silverware-fork-knife"></span>
          <span class="text-[11px] font-semibold text-white/80">Restaurant</span>
        </div>
      </div>
    </div>
  </div>


  <!-- ==================== RIGHT PANEL — Sign Up Form ==================== -->
  <div class="w-full lg:w-[55%] flex flex-col min-h-screen">
    <!-- Top bar -->
    <div class="flex items-center justify-between px-6 md:px-12 py-5 border-b border-slate-100">
      <!-- Mobile logo -->
      <div class="flex items-center gap-2.5 lg:hidden">
        <img src="{{ asset('chtm-logoo.png') }}" alt="HMS-Learn logo" class="h-10 w-auto object-contain drop-shadow-sm" />
        <span class="text-lg font-bold tracking-tight">HMS<span class="text-brand">-Learn</span></span>
      </div>
      <div class="hidden lg:block"></div>
      <p class="text-sm text-slate-400">
        Already have an account?
        <a href="{{ route('login') }}" class="text-brand font-semibold hover:text-brand-dark transition-colors">Log in</a>
      </p>
    </div>

    <!-- Form container -->
    <div class="flex-1 flex items-center justify-center px-6 md:px-12 py-8 md:py-12">
      <div class="w-full max-w-lg relative">

        <!-- ============ SIGN UP FORM ============ -->
        <div id="signupPanel" class="panel-enter">
          <div class="mb-8">
            <h2 class="text-3xl font-bold tracking-tight mb-2">Create your account</h2>
            <p class="text-slate-500 font-light">Join HMS-Learn and start managing hotel tasks with your team.</p>
          </div>

          <form id="signupForm" onsubmit="handleSignup(event)" class="space-y-4">
            <!-- School ID -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">School ID <span class="text-brand">*</span></label>
              <div class="relative">
                <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:badge-account-outline"></span>
                <input id="schoolId" type="text" placeholder="e.g. 2025-00123" required
                  class="input-field w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none" />
              </div>
              <p id="schoolIdError" class="text-[10px] text-red-500 font-medium mt-1 hidden">Please enter your School ID</p>
            </div>

            <!-- Name Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- First Name -->
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">First Name <span class="text-brand">*</span></label>
                <div class="relative">
                  <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:account-outline"></span>
                  <input id="firstName" type="text" placeholder="Juan" required
                    class="input-field w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none" />
                </div>
                <p id="firstNameError" class="text-[10px] text-red-500 font-medium mt-1 hidden">Required</p>
              </div>
              <!-- Last Name -->
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Last Name <span class="text-brand">*</span></label>
                <div class="relative">
                  <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:account-outline"></span>
                  <input id="lastName" type="text" placeholder="Dela Cruz" required
                    class="input-field w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none" />
                </div>
                <p id="lastNameError" class="text-[10px] text-red-500 font-medium mt-1 hidden">Required</p>
              </div>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Email <span class="text-brand">*</span></label>
              <div class="relative">
                <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:email-outline"></span>
                <input id="email" type="email" placeholder="you@school.edu" required
                  class="input-field w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none" />
              </div>
              <p id="emailError" class="text-[10px] text-red-500 font-medium mt-1 hidden">Please enter a valid email address</p>
            </div>

            <!-- Contact Number -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Contact Number <span class="text-brand">*</span></label>
              <div class="relative">
                <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:phone-outline"></span>
                <input id="contactNumber" type="tel" placeholder="+63 912 345 6789" required
                  class="input-field w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none" />
              </div>
              <p id="contactError" class="text-[10px] text-red-500 font-medium mt-1 hidden">Please enter your contact number</p>
            </div>

            <!-- Password -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Password <span class="text-brand">*</span></label>
              <div class="relative">
                <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:lock-outline"></span>
                <input id="password" type="password" placeholder="Min. 8 characters" required
                  oninput="checkPasswordStrength(this.value); checkPasswordMatch();"
                  class="input-field w-full h-12 pl-10 pr-11 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none" />
                <button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                  <span class="iconify text-lg" data-icon="mdi:eye-off-outline"></span>
                </button>
              </div>
              <!-- Strength meter -->
              <div class="mt-2">
                <div class="flex gap-1.5">
                  <div class="h-1 flex-1 bg-slate-200 rounded-full overflow-hidden"><div id="str1" class="strength-bar h-full w-0 rounded-full"></div></div>
                  <div class="h-1 flex-1 bg-slate-200 rounded-full overflow-hidden"><div id="str2" class="strength-bar h-full w-0 rounded-full"></div></div>
                  <div class="h-1 flex-1 bg-slate-200 rounded-full overflow-hidden"><div id="str3" class="strength-bar h-full w-0 rounded-full"></div></div>
                  <div class="h-1 flex-1 bg-slate-200 rounded-full overflow-hidden"><div id="str4" class="strength-bar h-full w-0 rounded-full"></div></div>
                </div>
                <div class="flex items-center justify-between mt-1.5">
                  <p id="strText" class="text-[10px] font-medium text-slate-400"></p>
                  <div class="flex gap-3">
                    <span id="chkLen" class="text-[10px] text-slate-300 flex items-center gap-0.5"><span class="iconify" data-icon="mdi:close-circle-outline"></span> 8+ chars</span>
                    <span id="chkUpper" class="text-[10px] text-slate-300 flex items-center gap-0.5"><span class="iconify" data-icon="mdi:close-circle-outline"></span> Uppercase</span>
                    <span id="chkNum" class="text-[10px] text-slate-300 flex items-center gap-0.5"><span class="iconify" data-icon="mdi:close-circle-outline"></span> Number</span>
                    <span id="chkSym" class="text-[10px] text-slate-300 flex items-center gap-0.5"><span class="iconify" data-icon="mdi:close-circle-outline"></span> Symbol</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Confirm Password -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Confirm Password <span class="text-brand">*</span></label>
              <div class="relative">
                <span class="iconify absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:lock-check-outline"></span>
                <input id="confirmPassword" type="password" placeholder="Re-enter your password" required
                  oninput="checkPasswordMatch()"
                  class="input-field w-full h-12 pl-10 pr-11 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none" />
                <span id="matchIcon" class="absolute right-3.5 top-1/2 -translate-y-1/2 hidden">
                  <span class="iconify text-lg" data-icon=""></span>
                </span>
              </div>
              <p id="matchText" class="text-[10px] font-medium mt-1.5 hidden"></p>
            </div>

            <!-- Terms -->
            <div class="pt-1">
              <label class="flex items-start gap-2.5 cursor-pointer">
                <input type="checkbox" id="termsCheck" class="w-4 h-4 mt-0.5 rounded border-slate-300 text-brand focus:ring-brand/30" />
                <span class="text-xs text-slate-500 leading-relaxed">I agree to the <a href="#" class="text-brand font-semibold hover:underline">Terms of Service</a> and <a href="#" class="text-brand font-semibold hover:underline">Privacy Policy</a></span>
              </label>
            </div>

            <!-- Submit -->
            <button type="submit" id="signupBtn"
              class="w-full h-12 brand-gradient text-white text-sm font-bold uppercase tracking-wider rounded-xl hover:scale-[1.02] hover:-translate-y-0.5 transition-all duration-150 shadow-lg shadow-brand/20 flex items-center justify-center gap-2 mt-2">
              <span id="signupBtnText">Create Account</span>
              <span id="signupSpinner" class="hidden"><span class="iconify spinner text-lg" data-icon="mdi:loading"></span></span>
            </button>
          </form>
        </div>


        <!-- ============ SUCCESS STATE ============ -->
        <div id="successPanel" class="hidden">
          <div class="text-center py-8">
            <!-- Checkmark -->
            <div class="anim-scale w-24 h-24 mx-auto mb-8 brand-gradient rounded-full flex items-center justify-center shadow-xl shadow-brand/30">
              <span class="iconify text-white text-5xl" data-icon="mdi:check-bold"></span>
            </div>

            <h2 class="anim-fade-1 text-3xl font-bold tracking-tight mb-3">Account Created!</h2>
            <p class="anim-fade-2 text-slate-500 font-light leading-relaxed max-w-sm mx-auto mb-8">
              Welcome to HMS-Learn! We've sent a verification link to your email. Please verify to start managing hotel tasks.
            </p>

            <!-- Summary card -->
            <div class="anim-fade-3 bg-brand-soft rounded-2xl p-6 text-left max-w-sm mx-auto mb-8 border border-brand/10">
              <div class="flex items-center gap-3 mb-4">
                <div id="successAvatar" class="w-12 h-12 brand-gradient rounded-xl flex items-center justify-center text-white text-lg font-bold">J</div>
                <div>
                  <p id="successName" class="font-bold text-slate-800">Juan Dela Cruz</p>
                  <p id="successSchoolId" class="text-xs text-brand font-medium">2025-00123</p>
                </div>
              </div>
              <div class="space-y-2">
                <div class="flex items-center gap-2 text-sm">
                  <span class="iconify text-slate-400" data-icon="mdi:email-outline"></span>
                  <span id="successEmail" class="text-slate-600 font-light">you@school.edu</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                  <span class="iconify text-slate-400" data-icon="mdi:phone-outline"></span>
                  <span id="successContact" class="text-slate-600 font-light">+63 912 345 6789</span>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="anim-fade-4 flex flex-col gap-3 max-w-sm mx-auto">
              <a href="{{ route('login') }}"
                class="h-12 brand-gradient text-white text-sm font-bold uppercase tracking-wider rounded-xl flex items-center justify-center gap-2 hover:scale-[1.02] transition-all shadow-lg shadow-brand/20">
                <span class="iconify text-lg" data-icon="mdi:login"></span>
                Go to Dashboard
              </a>
              <button onclick="resendEmail()"
                class="h-10 text-sm font-semibold text-brand hover:text-brand-dark transition-colors flex items-center justify-center gap-1.5 mx-auto">
                <span class="iconify" data-icon="mdi:email-send-outline"></span>
                Resend verification email
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Footer -->
    <div class="px-6 md:px-12 py-4 border-t border-slate-100">
      <p class="text-[10px] text-slate-400 text-center">&copy; 2025 HMS-Learn. All rights reserved.</p>
    </div>
  </div>


  <!-- ==================== SCRIPTS ==================== -->
  <script>
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
      const hasLength = password.length >= 8;
      const hasUpper = /[a-z]/.test(password) && /[A-Z]/.test(password);
      const hasNum = /\d/.test(password);
      const hasSym = /[^a-zA-Z0-9]/.test(password);

      if (hasLength) score++;
      if (hasUpper) score++;
      if (hasNum) score++;
      if (hasSym) score++;

      const bars = [document.getElementById('str1'), document.getElementById('str2'), document.getElementById('str3'), document.getElementById('str4')];
      const text = document.getElementById('strText');

      const levels = [
        { color: '#E2E8F0', label: '', textColor: '#94A3B8' },
        { color: '#EF4444', label: 'Weak', textColor: '#EF4444' },
        { color: '#F59E0B', label: 'Fair', textColor: '#F59E0B' },
        { color: '#3B82F6', label: 'Good', textColor: '#3B82F6' },
        { color: '#22C55E', label: 'Strong', textColor: '#22C55E' },
      ];

      bars.forEach((bar, i) => {
        if (i < score) {
          bar.style.width = '100%';
          bar.style.background = levels[score].color;
        } else {
          bar.style.width = '0%';
        }
      });

      text.textContent = password.length === 0 ? '' : levels[score].label;
      text.style.color = levels[score].textColor;

      updateChecklist('chkLen', hasLength);
      updateChecklist('chkUpper', hasUpper);
      updateChecklist('chkNum', hasNum);
      updateChecklist('chkSym', hasSym);
    }

    function updateChecklist(id, passed) {
      const el = document.getElementById(id);
      const icon = el.querySelector('.iconify');
      if (passed) {
        el.style.color = '#22C55E';
        icon.setAttribute('data-icon', 'mdi:check-circle');
      } else {
        el.style.color = '#CBD5E1';
        icon.setAttribute('data-icon', 'mdi:close-circle-outline');
      }
    }

    // === Password Match ===
    function checkPasswordMatch() {
      const pass = document.getElementById('password').value;
      const confirm = document.getElementById('confirmPassword').value;
      const icon = document.getElementById('matchIcon');
      const text = document.getElementById('matchText');

      if (confirm.length === 0) {
        icon.classList.add('hidden');
        text.classList.add('hidden');
        document.getElementById('confirmPassword').classList.remove('error', 'success');
        return;
      }

      icon.classList.remove('hidden');
      text.classList.remove('hidden');
      const iconEl = icon.querySelector('.iconify');
      const input = document.getElementById('confirmPassword');

      if (pass === confirm) {
        iconEl.setAttribute('data-icon', 'mdi:check-circle');
        iconEl.style.color = '#22C55E';
        text.textContent = 'Passwords match';
        text.style.color = '#22C55E';
        input.classList.remove('error');
        input.classList.add('success');
      } else {
        iconEl.setAttribute('data-icon', 'mdi:close-circle');
        iconEl.style.color = '#EF4444';
        text.textContent = 'Passwords don\'t match';
        text.style.color = '#EF4444';
        input.classList.remove('success');
        input.classList.add('error');
      }
    }

    // === Toast ===
    function showToast(message, type = 'success') {
      const container = document.getElementById('toastContainer');
      const toast = document.createElement('div');
      const icons = { success: 'mdi:check-circle', error: 'mdi:alert-circle', info: 'mdi:information' };
      const colors = {
        success: { bg: 'bg-green-50', border: 'border-green-200', icon: 'text-green-500', text: 'text-green-700' },
        error: { bg: 'bg-red-50', border: 'border-red-200', icon: 'text-red-500', text: 'text-red-700' },
        info: { bg: 'bg-blue-50', border: 'border-blue-200', icon: 'text-blue-500', text: 'text-blue-700' },
      };
      const c = colors[type];
      toast.className = `toast-in pointer-events-auto flex items-center gap-3 px-5 py-3.5 ${c.bg} border ${c.border} rounded-xl shadow-lg`;
      toast.innerHTML = `<span class="iconify ${c.icon} text-xl" data-icon="${icons[type]}"></span><p class="text-sm font-semibold ${c.text}">${message}</p>`;
      container.appendChild(toast);
      setTimeout(() => {
        toast.classList.remove('toast-in');
        toast.classList.add('toast-out');
        setTimeout(() => toast.remove(), 300);
      }, 4000);
    }

    // === Handle Signup ===
    function handleSignup(e) {
      e.preventDefault();

      // Clear previous errors
      document.querySelectorAll('.input-field').forEach(f => f.classList.remove('error'));
      document.querySelectorAll('[id$="Error"]').forEach(e => e.classList.add('hidden'));

      let hasError = false;

      // Validate required fields
      const fields = [
        { id: 'schoolId', errorId: 'schoolIdError', message: 'School ID is required' },
        { id: 'firstName', errorId: 'firstNameError', message: 'First name is required' },
        { id: 'lastName', errorId: 'lastNameError', message: 'Last name is required' },
        { id: 'email', errorId: 'emailError', message: 'Valid email is required' },
        { id: 'contactNumber', errorId: 'contactError', message: 'Contact number is required' },
      ];

      fields.forEach(f => {
        const input = document.getElementById(f.id);
        const error = document.getElementById(f.errorId);
        if (!input.value.trim() || (f.id === 'email' && !input.value.includes('@'))) {
          input.classList.add('error');
          error.classList.remove('hidden');
          error.textContent = f.message;
          hasError = true;
        }
      });

      const password = document.getElementById('password').value;
      const confirm = document.getElementById('confirmPassword').value;
      const terms = document.getElementById('termsCheck').checked;

      if (password.length < 8) {
        showToast('Password must be at least 8 characters.', 'error');
        document.getElementById('password').classList.add('error');
        hasError = true;
      }
      if (password !== confirm) {
        showToast('Passwords don\'t match.', 'error');
        document.getElementById('confirmPassword').classList.add('error');
        hasError = true;
      }
      if (!terms) {
        showToast('Please agree to the Terms of Service.', 'error');
        hasError = true;
      }

      if (hasError) return;

      // Loading
      const btn = document.getElementById('signupBtn');
      const btnText = document.getElementById('signupBtnText');
      const spinner = document.getElementById('signupSpinner');

      btn.disabled = true;
      btnText.textContent = 'Creating...';
      spinner.classList.remove('hidden');

      setTimeout(() => {
        btn.disabled = false;
        btnText.textContent = 'Create Account';
        spinner.classList.add('hidden');

        // Fill success summary
        const firstName = document.getElementById('firstName').value;
        const lastName = document.getElementById('lastName').value;
        const email = document.getElementById('email').value;
        const schoolId = document.getElementById('schoolId').value;
        const contact = document.getElementById('contactNumber').value;

        document.getElementById('successName').textContent = firstName + ' ' + lastName;
        document.getElementById('successSchoolId').textContent = schoolId;
        document.getElementById('successEmail').textContent = email;
        document.getElementById('successContact').textContent = contact;
        document.getElementById('successAvatar').textContent = firstName.charAt(0).toUpperCase();

        // Show success
        document.getElementById('signupPanel').classList.add('hidden');
        document.getElementById('successPanel').classList.remove('hidden');

        showToast('Account created successfully!', 'success');
      }, 2000);
    }

    // === Resend Email ===
    function resendEmail() {
      showToast('Verification email sent! Check your inbox.', 'info');
    }
  </script>
</body>
</html>