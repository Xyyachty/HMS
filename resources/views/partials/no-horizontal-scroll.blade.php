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
    html .overflow-x-auto.flex,
    html .tm-nav,
    html .rs-courses,
    html .rs-col .rs-views { flex-wrap: wrap; overflow-x: visible; }
</style>
