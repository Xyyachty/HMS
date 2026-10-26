{{-- No sideways scrolling, on any screen. Included by every page shell.
     The page itself never grows wider than the window; tables that used to
     scroll sideways wrap their text instead; tab strips that used to scroll
     wrap onto a second line. "html" in front of each selector outranks the
     Tailwind utility of the same name, wherever that stylesheet loads.
     DataTables tables keep nowrap: their Responsive extension folds extra
     columns into a child row, and it needs nowrap to measure them. --}}
<style>
    html, body { overflow-x: clip; }
    html .overflow-x-auto { overflow-x: hidden; }
    html .overflow-x-auto > table:not(.dataTable) { min-width: 0 !important; width: 100%; }
    html .overflow-x-auto > table:not(.dataTable) :is(th, td) { white-space: normal !important; }
    html .overflow-x-auto > table:not(.dataTable) td { overflow-wrap: anywhere; }
    /* A modal locks scrolling by putting overflow: hidden on body (inline, or
       SweetAlert's swal2-shown). Because html has overflow-x: clip above, that
       no longer passes to the window: body turns into its own scroll box, the
       sticky sidebar sticks to body, and on a scrolled page it slides up and
       leaves a bare strip below it. So lock html (the window) instead and keep
       body an ordinary box. Bodies that are overflow-hidden by class (the
       h-screen shells) are left alone. */
    html:has(> body:not(.overflow-hidden)[style*="overflow: hidden"]),
    html:has(> body.swal2-shown:not(.overflow-hidden):not(.swal2-no-backdrop):not(.swal2-toast-shown)) { overflow: hidden; }
    html > body:not(.overflow-hidden)[style*="overflow: hidden"],
    html > body.swal2-shown:not(.overflow-hidden) { overflow: visible !important; overflow-x: clip !important; }
    html .overflow-x-auto.flex,
    html .tm-nav,
    html .rs-courses,
    html .rs-col .rs-views { flex-wrap: wrap; overflow-x: visible; }
</style>
