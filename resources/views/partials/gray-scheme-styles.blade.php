{{-- The Student portal's gray scheme for the dean and faculty layouts. The
     sidebar keeps its wine gradient; a link turns white on hover and light gray
     (#dadada) on the current page, both with dark text. Inside .gray-accents
     (the page area) the wine and gold accents become grays: solid fills, bars
     and icons #4A4643, buttons #5F5A55, soft fills #F3F2F1, borders #E4E2E0.
     Red and rose stay, since these pages use them for delete and error. Work
     status colours live in status-badge-styles, whose tripled classes outrank
     every rule here, so they never change. --}}
<style>
    .nav-item.nav-item { color: #fff; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .nav-item.nav-item:hover { background: #fff; color: #181818; padding-left: 1rem; transform: translateX(2px); }
    .nav-item.nav-item.active { background: #dadada; color: #181818; border-left: 0; box-shadow: 0 4px 16px -4px rgba(0, 0, 0, 0.25); }

    .gray-accents :is(.bg-brand, .bg-brand-dark, .bg-plum-accent, .bg-amber-500, .bg-orange-500, .bg-violet-500, .bg-teal-500, .bg-teal-600, .bg-blue-500) { background-color: #4A4643; }
    .gray-accents .brand-gradient { background: #5F5A55; }
    .gray-accents .brand-gradient:is(a, button):hover { background: #4A4643; opacity: 1; }
    .gray-accents :is(.hover\:bg-brand, .hover\:bg-brand-dark):hover { background-color: #4A4643; }
    .gray-accents :is(.bg-brand-soft, .bg-brand-soft\/30, .bg-brand-soft\/40, .bg-brand\/10, .bg-plum-soft, .bg-amber-50, .bg-emerald-50, .bg-green-50, .bg-blue-50, .bg-violet-50, .bg-teal-50, .bg-slate-50, .bg-slate-100) { background-color: #F3F2F1; }
    .gray-accents :is(.hover\:bg-brand-soft, .hover\:bg-brand-soft\/20, .hover\:bg-brand-soft\/50, .hover\:bg-brand\/10, .hover\:bg-amber-50):hover { background-color: #F3F2F1; }
    .gray-accents :is(.border-brand-light, .border-brand\/10, .border-brand\/15, .border-brand\/20, .border-brand\/30, .border-pink-100, .border-amber-100, .border-amber-200, .border-blue-100, .border-blue-200, .border-emerald-100, .border-emerald-200, .border-green-200, .border-slate-100, .border-slate-200) { border-color: #E4E2E0; }
    .gray-accents :is(.border-brand, .border-amber-300, .border-amber-400, .border-emerald-400) { border-color: #4A4643; }
    .gray-accents :is(.hover\:border-brand, .hover\:border-brand\/30, .hover\:border-brand\/40):hover { border-color: #8A817A; }
    .gray-accents .focus\:border-brand:focus { border-color: #8A817A; }
    .gray-accents :is(.focus\:ring-brand\/15, .focus\:ring-brand\/20, .focus\:ring-brand\/30):focus { --tw-ring-color: rgba(138, 129, 122, 0.3); }
    .gray-accents .ring-brand { --tw-ring-color: #4A4643; }
    .gray-accents :is(.shadow-brand, .shadow-brand\/20, .shadow-brand\/25, .shadow-amber-400\/30) { --tw-shadow-color: rgba(24, 24, 24, 0.15); }
    .gray-accents :is(.text-brand, .text-brand-dark, .text-plum-accent) { color: #4A4643; }
    .gray-accents .hover\:text-brand:hover { color: #181818; }
    .gray-accents :is(.text-amber-500, .text-amber-600, .text-emerald-500, .text-emerald-600, .text-blue-500, .text-blue-600, .text-violet-500, .text-teal-500, .text-slate-200, .text-slate-300, .text-slate-400, .text-slate-500, .text-slate-600):is(.iconify, svg) { color: #4A4643; }
    .gray-accents circle[stroke="#7B1730"] { stroke: #4A4643; }
    .gray-accents circle[stroke="#F2E9E7"] { stroke: #E4E2E0; }
</style>
