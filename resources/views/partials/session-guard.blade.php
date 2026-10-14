{{-- Page side of App\Http\Middleware\EnforceSessionRules, on every signed-in page.

     - Counts clicks, typing, scrolling and pointer movement as activity. Activity
       is shared across this browser's tabs through localStorage, so working in
       one tab keeps the others signed in too.
     - Tells the server about it with /session/ping?active=1, at most every 15
       seconds plus once after the last burst. The server then never sees less
       recent activity than this page does, so it never ends the session first.
     - At fifteen idle minutes it saves any open builder draft (never submits
       it) and shows a ten-second "Stay Logged In" warning. Only that button
       keeps the session; when the countdown ends it goes to /session-ended,
       which signs out and says why. Every open tab shows the same countdown,
       and pressing the button in one closes it in all of them.
     - Asks once a minute, and on coming back to the tab, whether the session is
       still the account's live one, so a sign-in on another device shows here
       without waiting for a click. --}}
@auth
<script>
(function () {
    if (window.__hmsSessionGuard) return;
    window.__hmsSessionGuard = true;

    var IDLE_MS = {{ \App\Http\Middleware\EnforceSessionRules::IDLE_SECONDS * 1000 }};
    var WARN_MS = {{ \App\Http\Middleware\EnforceSessionRules::WARNING_SECONDS * 1000 }};
    var PING_EVERY_MS = 15000;
    var PING_URL = @json(route('session.ping', [], false));
    var ENDED_URL = @json(route('session.ended', [], false));
    var KEY = 'hms.lastActivity';

    var last = Date.now();
    var sentAt = 0;
    var trailing = null;
    var leaving = false;
    var warning = false;

    function store(t) { try { localStorage.setItem(KEY, String(t)); } catch (e) {} }
    function lastShared() {
        var v = 0;
        try { v = Number(localStorage.getItem(KEY)) || 0; } catch (e) {}
        return Math.max(v, last);
    }

    function leave(reason) {
        if (leaving) return;
        leaving = true;
        window.location.href = ENDED_URL + '?reason=' + (reason === 'replaced' ? 'replaced' : 'idle');
    }

    function ping(active) {
        return fetch(PING_URL + (active ? '?active=1' : ''), {
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        }).then(function (r) {
            if (r.status !== 401) return;
            return r.json().then(function (d) { leave(d && d.session_ended); }, function () { leave('idle'); });
        }).catch(function () {});
    }

    function onActivity() {
        if (warning) return; // only the Stay Logged In button counts now
        var now = Date.now();
        if (now - last < 1000 && sentAt) return; // pointer moves fire constantly
        last = now;
        store(now);

        var wait = sentAt + PING_EVERY_MS - now;
        if (wait <= 0) {
            sentAt = now;
            ping(true);
        } else if (!trailing) {
            trailing = setTimeout(function () {
                trailing = null;
                sentAt = Date.now();
                ping(true);
            }, wait);
        }
    }

    ['pointerdown', 'pointermove', 'keydown', 'input', 'wheel', 'touchstart', 'scroll'].forEach(function (type) {
        window.addEventListener(type, onActivity, { passive: true, capture: true });
    });

    // Opening the page was activity; the server already counted it.
    store(last);
    sentAt = last;

    var saved = null;
    function saveDraft() {
        var builder = window.hmsBuilder;
        return builder && typeof builder.autosave === 'function'
            ? Promise.resolve().then(function () { return builder.autosave(); }).catch(function () {})
            : Promise.resolve();
    }

    function secondsLeft() {
        return Math.max(0, Math.ceil((lastShared() + IDLE_MS + WARN_MS - Date.now()) / 1000));
    }

    function countdownText(n) {
        return 'Your session is about to expire due to inactivity. You will be logged out in '
            + n + (n === 1 ? ' second.' : ' seconds.');
    }

    function stay() {
        warning = false;
        saved = null;
        last = sentAt = Date.now();
        store(last);
        clearTimeout(trailing);
        trailing = null;
        ping(true);
    }

    function warn() {
        warning = true;
        saved = saveDraft();
        if (!window.Swal) return;
        Swal.fire({
            icon: 'warning',
            title: 'Session Expiring',
            text: countdownText(secondsLeft()),
            confirmButtonText: 'Stay Logged In',
            allowOutsideClick: false,
            allowEscapeKey: false,
            heightAuto: false,
        }).then(function (r) {
            if (r.isConfirmed && warning && !leaving) stay();
        });
    }

    function closeWarning() {
        warning = false;
        saved = null;
        if (window.Swal && Swal.isVisible()) Swal.close();
    }

    var ending = false;
    function tick() {
        if (leaving || ending) return;
        var idle = Date.now() - lastShared();
        if (idle >= IDLE_MS + WARN_MS) {
            // Give a draft save already on its way a moment to land.
            ending = true;
            Promise.race([saved || saveDraft(), new Promise(function (r) { setTimeout(r, 1500); })])
                .then(function () { leave('idle'); });
            return;
        }
        if (idle >= IDLE_MS) {
            if (!warning) warn();
            var box = window.Swal && Swal.getHtmlContainer();
            if (box) box.textContent = countdownText(secondsLeft());
        } else if (warning) {
            closeWarning(); // Stay Logged In was pressed in another tab
        }
    }

    setInterval(tick, 1000);

    setInterval(function () {
        if (!document.hidden && !leaving) ping(false);
    }, 60000);

    document.addEventListener('visibilitychange', function () {
        if (document.hidden || leaving) return;
        tick();
        if (!leaving) ping(false);
    });
})();
</script>
@endauth
