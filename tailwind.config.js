/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Manrope', 'sans-serif'],
      },
      colors: {
        brand: '#DB2777',
        'brand-light': '#F472B6',
        'brand-dark': '#9D174D',
        'brand-soft': '#FDF2F8',
        'rose-accent': '#FB7185',
        'plum-accent': '#A855F7',
        'plum-soft': '#FAF5FF',
        'amber-soft': '#FFFBEB',
        sidebar: '#DB2777',
        'sidebar-light': '#500724',
      },
      animation: {
        'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
        'fade-in-up-2': 'fadeInUp 0.8s ease-out 0.2s forwards',
        'fade-in-up-4': 'fadeInUp 0.8s ease-out 0.4s forwards',
        'fade-in-up-6': 'fadeInUp 0.8s ease-out 0.6s forwards',
        float: 'float 6s ease-in-out infinite',
        'float-delay': 'float 6s ease-in-out 2s infinite',
        'pulse-soft': 'pulseSoft 3s ease-in-out infinite',
        'glow-pulse': 'glowPulse 2.5s ease-in-out infinite',
      },
    },
  },
  plugins: [],
};
