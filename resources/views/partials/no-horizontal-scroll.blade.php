{{-- No sideways scrolling, on any screen. Included by every page shell.
     The page itself never grows wider than the window. Tables wrap their text
     between words; on a phone each row turns into a small card, one labelled
     line per column, so nothing is cut off or crushed until "Completed" stacks
     one letter per line. Tab strips that used to scroll wrap onto a second
     line. "html" in front of each selector outranks the Tailwind utility of the
     same name, wherever that stylesheet loads.
     DataTables tables keep nowrap: their Responsive extension folds extra
     columns into a child row, and it needs nowrap to measure them. --}}
<style>
    html, body { overflow-x: clip; }
    html .overflow-x-auto > table:not(.dataTable) :is(th, td) { white-space: normal !important; }
    html .overflow-x-auto > table:not(.dataTable) td { overflow-wrap: break-word; }

    /* Phone: a row per card. The labels come from the table's own header,
       copied onto each cell by the script below (data-label). */
    @media (max-width: 640px) {
        html .overflow-x-auto > table.rt-stack:not(.dataTable) { display: block; min-width: 0 !important; width: 100% !important; table-layout: auto !important; }
        html .overflow-x-auto > table.rt-stack:not(.dataTable) > thead { display: none; }
        html .overflow-x-auto > table.rt-stack:not(.dataTable) > tbody { display: block; }
        html .overflow-x-auto > table.rt-stack:not(.dataTable) > tbody > tr { display: block; padding: .65rem .9rem; }
        html .overflow-x-auto > table.rt-stack:not(.dataTable) > tbody > tr > td {
            display: flex; align-items: center; justify-content: space-between; gap: .75rem;
            width: 100% !important; padding: .3rem 0 !important; text-align: right; border: 0 !important;
        }
        html .overflow-x-auto > table.rt-stack:not(.dataTable) > tbody > tr > td::before {
            content: attr(data-label); flex: 0 0 auto; max-width: 45%; text-align: left;
            font-size: .625rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #8A817A;
        }
        html .overflow-x-auto > table.rt-stack:not(.dataTable) > tbody > tr > td[data-label=""] { justify-content: flex-start; text-align: left; }
        html .overflow-x-auto > table.rt-stack:not(.dataTable) > tbody > tr > td[data-label=""]::before { display: none; }
        html .overflow-x-auto > table.rt-stack:not(.dataTable) > tbody > tr > td > * { min-width: 0; }
        html .overflow-x-auto > table.rt-stack:not(.dataTable) .truncate { white-space: normal; overflow: visible; }
        html .overflow-x-auto > table.rt-stack:not(.dataTable) [class*="hidden"][class*=":table-cell"] { display: flex; }
    }

    /* Portal header on a phone: one line of title, one of subtitle, cut with
       an ellipsis rather than wrapping out of the 5rem bar. */
    @media (max-width: 640px) {
        html .glass-header { padding-left: 1rem; padding-right: 1rem; gap: .5rem; }
        html .glass-header > div:first-child { min-width: 0; flex: 1 1 auto; gap: .5rem; }
        html .glass-header > div:first-child > div { min-width: 0; }
        html .glass-header h2 { font-size: 1.05rem; line-height: 1.3; }
        html .glass-header :is(h2, h2 + p) { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    }

    /* Dashboard panel rows (dean and faculty): three a row only where each has
       room for a full task or team name; two on a laptop with the last one
       full width below; one on a phone. */
    .dash-panels { display: grid; gap: .75rem; grid-template-columns: minmax(0, 1fr); }
    @media (min-width: 1024px) { .dash-panels { grid-template-columns: repeat(2, minmax(0, 1fr)); } .dash-panels > :last-child { grid-column: 1 / -1; } }
    @media (min-width: 1536px) { .dash-panels { grid-template-columns: repeat(3, minmax(0, 1fr)); } .dash-panels > :last-child { grid-column: auto; } }

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
<script>
    // Label every cell with its column's header, so the phone layout above can
    // print "STATUS  Active" on each line. Rows added later (live refreshes,
    // pagination) are labelled as they arrive. A cell spanning several columns
    // is an empty-state or note row and gets no label.
    (function () {
        function label(table) {
            var heads = Array.prototype.map.call(table.querySelectorAll(':scope > thead > tr:last-child > th'), function (th) {
                return th.textContent.replace(/\s+/g, ' ').trim();
            });
            if (!heads.length) return;
            table.classList.add('rt-stack');
            table.querySelectorAll(':scope > tbody > tr').forEach(function (tr) {
                var col = 0;
                Array.prototype.forEach.call(tr.children, function (td) {
                    var span = td.colSpan || 1;
                    if (!td.hasAttribute('data-label')) td.setAttribute('data-label', span > 1 ? '' : (heads[col] || ''));
                    col += span;
                });
            });
        }
        var queued = false;
        function run() {
            queued = false;
            document.querySelectorAll('.overflow-x-auto > table:not(.dataTable)').forEach(label);
        }
        function queue() { if (!queued) { queued = true; requestAnimationFrame(run); } }
        document.addEventListener('DOMContentLoaded', run);
        new MutationObserver(queue).observe(document.documentElement, { childList: true, subtree: true });
    })();
</script>
