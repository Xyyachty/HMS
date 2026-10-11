{{-- Repaints every [data-team-key] on the page from DeanController::teamProgressLive,
     so a faculty approval shows up without a reload. Children it fills, when present:
     [data-team-pill] the completion indicator, [data-team-bar] / [data-team-percent]
     the bar, [data-team-count] "x/y approved"; [data-team-summary] gets the totals. --}}
<script>
(function () {
    const URL = @json(route('dean.team-progress'));

    function refresh() {
        if (document.hidden) return;
        fetch(URL, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(r => r.ok ? r.json() : Promise.reject(r.status))
            .then(function (data) {
                document.querySelectorAll('[data-team-key]').forEach(function (row) {
                    const t = data.teams[row.getAttribute('data-team-key')];
                    if (!t) return;
                    const pill = row.querySelector('[data-team-pill]');
                    if (pill && pill.innerHTML !== t.pill) pill.innerHTML = t.pill;
                    const bar = row.querySelector('[data-team-bar]');
                    if (bar) bar.style.width = t.percent + '%';
                    const pct = row.querySelector('[data-team-percent]');
                    if (pct) pct.textContent = t.percent + '%';
                    const count = row.querySelector('[data-team-count]');
                    if (count) count.textContent = t.done + '/' + t.total + ' approved';
                });
                document.querySelectorAll('[data-team-summary]').forEach(function (el) {
                    el.textContent = data.summary;
                });
                document.querySelectorAll('[data-team-overall-bar]').forEach(function (el) { el.style.width = data.percent + '%'; });
                document.querySelectorAll('[data-team-overall-percent]').forEach(function (el) { el.textContent = data.percent + '%'; });
            })
            .catch(function () {});
    }

    setInterval(refresh, 30000);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) refresh(); });
})();
</script>
