{{--
    Live Philippine date + time, shared by the dean, faculty and student headers.
    Sits to the left of the notification bell.

    Rendered in the browser rather than with now() on purpose. The app runs on
    UTC (config/app.php), so a server-rendered stamp would show the wrong hour
    and then sit frozen at whatever time the page happened to load. Formatting
    client-side against a fixed Asia/Manila zone keeps it correct and ticking no
    matter where the server or the viewer is.

    Styled with its own CSS rather than Tailwind utilities, the same way the
    notification bell ships its own behaviour. The dean and student pages load
    Tailwind from the CDN, but the faculty layout loads a prebuilt css/app.css
    that carries only the utilities used when it was generated - it has no
    `md:flex`, so a utility-styled clock would stay hidden there forever.
--}}
<div class="hms-clock" data-hms-clock>
    <span class="iconify hms-clock__icon" data-icon="mdi:clock-outline"></span>
    <span class="hms-clock__time" data-hms-clock-text>&nbsp;</span>
    <span class="hms-clock__zone">PHT</span>
</div>

@once
<style>
    /* Hidden on small screens: the header is already tight there. */
    .hms-clock {
        display: none;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        background: #F8FAFC;
        border-radius: 0.75rem;
    }
    @media (min-width: 768px) {
        .hms-clock { display: inline-flex; }
    }
    .hms-clock__icon { font-size: 0.875rem; color: #94A3B8; }
    .hms-clock__time {
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748B;
        white-space: nowrap;
        /* Stops the row jittering as the digits change width. */
        font-variant-numeric: tabular-nums;
    }
    .hms-clock__zone {
        font-size: 0.625rem;
        font-weight: 700;
        color: #CBD5E1;
        letter-spacing: 0.05em;
    }
</style>
<script>
(function () {
    var TIME_ZONE = 'Asia/Manila';

    // Intl carries the offset rules, so no manual hour arithmetic.
    var formatter = new Intl.DateTimeFormat('en-PH', {
        timeZone: TIME_ZONE,
        month: 'short', day: '2-digit', year: 'numeric',
        hour: 'numeric', minute: '2-digit', second: '2-digit',
        hour12: true,
    });

    function paint() {
        var text = formatter.format(new Date());
        var nodes = document.querySelectorAll('[data-hms-clock-text]');
        for (var i = 0; i < nodes.length; i++) {
            nodes[i].textContent = text;
        }
    }

    paint();

    // Align the first tick to the next whole second so the display does not sit
    // a fraction behind the wall clock, then tick once a second.
    var timer = null;
    setTimeout(function () {
        paint();
        timer = setInterval(paint, 1000);
    }, 1000 - (Date.now() % 1000));

    // A backgrounded tab throttles timers; repaint on return so the time is
    // never stale.
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) paint();
    });

    window.addEventListener('pagehide', function () {
        if (timer) clearInterval(timer);
    });
})();
</script>
@endonce
