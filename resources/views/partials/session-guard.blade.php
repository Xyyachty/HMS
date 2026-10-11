{{-- Page side of App\Http\Middleware\EnforceSessionRules, on every signed-in page.

     - Counts clicks, typing, scrolling and pointer movement as activity. Activity
       is shared across this browser's tabs through localStorage, so working in
       one tab keeps the others signed in too.
     - Tells the server about it with /session/ping?active=1, at most every 15
       seconds plus once after the last burst. The server then never sees less
       recent activity than this page does, so it never ends the session first.
     - At five idle minutes it saves any open builder draft (never submits it),
       then goes to /session-ended, which signs out and says why.
     - Asks once a minute, and on coming back to the tab, whether the session is
       still the account's live one, so a sign-in on another device shows here
       without waiting for a click. --}}
@auth
<script>
(function () {
    if (window.__hmsSessionGuard) return;
    window.__hmsSessionGuard = true;

    var IDLE_MS = {{ \App\Http\Middleware\EnforceSessionRules::IDLE_SECONDS * 1000 }};
    var SAVE_LEAD_MS = 8000;
    var PING_EVERY_MS = 15000;
    var PING_URL = @json(route('session.ping', [], false));
    var ENDED_URL = @json(route('session.ended', [], false));
    var KEY = 'hms.lastActivity';

    var last = Date.now();
    var sentAt = 0;
    var trailing = null;
    var leaving = false;

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

    var saving = false;
    function saveThenLeave() {
        if (saving) return;
        saving = true;
        var builder = window.hmsBuilder;
        var save = builder && typeof builder.autosave === 'function'
            ? Promise.resolve().then(function () { return builder.autosave(); }).catch(function () {})
            : Promise.resolve();
        var cap = new Promise(function (resolve) { setTimeout(resolve, SAVE_LEAD_MS - 1000); });
        Promise.race([save, cap]).then(function () {
            var left = IDLE_MS - (Date.now() - lastShared());
            if (left > SAVE_LEAD_MS) { saving = false; return; } // another tab saw activity meanwhile
            setTimeout(function () {
                if (Date.now() - lastShared() >= IDLE_MS - 500) leave('idle');
                else saving = false;
            }, Math.max(0, left));
        });
    }

    setInterval(function () {
        if (leaving) return;
        if (Date.now() - lastShared() >= IDLE_MS - SAVE_LEAD_MS) saveThenLeave();
    }, 2000);

    setInterval(function () {
        if (!document.hidden && !leaving) ping(false);
    }, 60000);

    document.addEventListener('visibilitychange', function () {
        if (document.hidden || leaving) return;
        if (Date.now() - lastShared() >= IDLE_MS) { leave('idle'); return; }
        ping(false);
    });
})();
</script>
@endauth
