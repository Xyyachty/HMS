{{--
    Notification bell + dropdown, shared by the dean, faculty and student portals.

    Self-contained on purpose: markup, styles and behaviour ship together so a
    layout only has to @include it next to its header actions. Reads
    /notifications, marks rows read through the same controller.

    Accent colours are applied as inline styles rather than Tailwind classes —
    the class names are built in JS, and inline styles avoid depending on the
    CDN's runtime scan picking them up.
--}}
<div class="hms-notify relative" data-notify-root>
    <button type="button"
            data-notify-toggle
            aria-haspopup="true"
            aria-expanded="false"
            aria-label="Notifications"
            class="relative w-10 h-10 flex items-center justify-center rounded-xl hover:bg-slate-100 transition-colors">
        <span class="iconify text-xl text-slate-500" data-icon="mdi:bell-outline"></span>
        <span data-notify-badge
              class="hidden absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 flex items-center justify-center
                     rounded-full bg-rose-500 text-white text-[10px] font-bold leading-none shadow-sm">0</span>
    </button>

    <div data-notify-panel
         class="hidden absolute right-0 mt-2 w-[22rem] sm:w-96 max-w-[calc(100vw-2rem)] z-50
                bg-white rounded-2xl shadow-2xl ring-1 ring-slate-900/5 overflow-hidden">

        <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-slate-900">Notifications</h3>
                <span data-notify-unread-pill
                      class="hidden px-1.5 py-0.5 rounded-full bg-rose-50 text-rose-600 text-[10px] font-bold">0 new</span>
            </div>
            <button type="button"
                    data-notify-mark-all
                    class="text-xs font-semibold text-slate-500 hover:text-slate-900 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                Mark all as read
            </button>
        </div>

        <div data-notify-list class="max-h-96 overflow-y-auto divide-y divide-slate-100">
            <div class="px-4 py-10 text-center text-xs text-slate-400">Loading…</div>
        </div>
    </div>
</div>

{{-- Inline rather than @push('scripts'): not every layout that needs the bell
     declares a @stack, and the markup above is already in the DOM by here. --}}
@once
<script>
(function () {
    // Layouts may include the partial twice (desktop + mobile header); bind once per root.
    const ENDPOINTS = {
        index:   @json(route('notifications.index')),
        readAll: @json(route('notifications.read-all')),
        read:    id => @json(url('/notifications')) + '/' + id + '/read',
    };

    const ACCENTS = {
        sky:     { bg: '#e0f2fe', fg: '#0284c7' },
        violet:  { bg: '#ede9fe', fg: '#7c3aed' },
        indigo:  { bg: '#e0e7ff', fg: '#4f46e5' },
        amber:   { bg: '#fef3c7', fg: '#d97706' },
        emerald: { bg: '#d1fae5', fg: '#059669' },
        rose:    { bg: '#ffe4e6', fg: '#e11d48' },
        slate:   { bg: '#f1f5f9', fg: '#475569' },
    };

    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));

    function postJson(url) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        }).then(r => r.ok ? r.json() : Promise.reject(r.status));
    }

    function initBell(root) {
        if (root.dataset.notifyReady === '1') return;
        root.dataset.notifyReady = '1';

        const toggle    = root.querySelector('[data-notify-toggle]');
        const panel     = root.querySelector('[data-notify-panel]');
        const badge     = root.querySelector('[data-notify-badge]');
        const pill      = root.querySelector('[data-notify-unread-pill]');
        const list      = root.querySelector('[data-notify-list]');
        const markAll   = root.querySelector('[data-notify-mark-all]');

        let loaded = false;

        function setUnread(count) {
            const n = Number(count) || 0;
            badge.textContent = n > 99 ? '99+' : String(n);
            badge.classList.toggle('hidden', n === 0);
            pill.textContent = n + ' new';
            pill.classList.toggle('hidden', n === 0);
            markAll.disabled = n === 0;
        }

        function render(items) {
            if (!items.length) {
                list.innerHTML =
                    '<div class="px-4 py-12 text-center">' +
                    '<span class="iconify text-3xl text-slate-300" data-icon="mdi:bell-sleep-outline"></span>' +
                    '<p class="mt-2 text-xs font-medium text-slate-400">No notifications yet</p>' +
                    '</div>';
                return;
            }

            list.innerHTML = items.map((n) => {
                const accent = ACCENTS[n.accent] || ACCENTS.slate;
                const unreadClasses = n.read ? 'bg-white' : 'bg-sky-50/60';

                return (
                    '<button type="button" data-notify-item data-id="' + n.id + '"' +
                        ' data-read="' + (n.read ? '1' : '0') + '"' +
                        ' data-url="' + escapeHtml(n.url || '') + '"' +
                        ' class="w-full text-left px-4 py-3 flex gap-3 hover:bg-slate-50 transition-colors ' + unreadClasses + '">' +
                        '<span class="shrink-0 w-9 h-9 rounded-xl flex items-center justify-center"' +
                            ' style="background:' + accent.bg + ';color:' + accent.fg + '">' +
                            '<span class="iconify text-lg" data-icon="' + escapeHtml(n.icon) + '"></span>' +
                        '</span>' +
                        '<span class="min-w-0 flex-1">' +
                            '<span class="block text-[13px] font-semibold text-slate-900 leading-snug">' +
                                escapeHtml(n.title) +
                            '</span>' +
                            (n.body
                                ? '<span class="block mt-0.5 text-xs text-slate-500 leading-snug"' +
                                    ' style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">' +
                                    escapeHtml(n.body) + '</span>'
                                : '') +
                            '<span class="block mt-1 text-[11px] text-slate-400">' +
                                escapeHtml(n.created_at_human) +
                            '</span>' +
                        '</span>' +
                        (n.read
                            ? ''
                            : '<span class="shrink-0 mt-1.5 w-2 h-2 rounded-full bg-sky-500"></span>') +
                    '</button>'
                );
            }).join('');
        }

        function load() {
            return fetch(ENDPOINTS.index, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin',
            })
                .then(r => r.ok ? r.json() : Promise.reject(r.status))
                .then((data) => {
                    loaded = true;
                    render(data.notifications || []);
                    setUnread(data.unread_count);
                })
                .catch(() => {
                    list.innerHTML =
                        '<div class="px-4 py-10 text-center text-xs text-slate-400">' +
                        'Could not load notifications.</div>';
                });
        }

        function open() {
            panel.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
            // Always refresh on open — the feed is cheap and staleness is worse.
            load();
        }

        function close() {
            panel.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
        }

        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            panel.classList.contains('hidden') ? open() : close();
        });

        document.addEventListener('click', (e) => {
            if (!root.contains(e.target)) close();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') close();
        });

        markAll.addEventListener('click', (e) => {
            e.stopPropagation();
            postJson(ENDPOINTS.readAll)
                .then(() => load())
                .catch(() => {});
        });

        list.addEventListener('click', (e) => {
            const item = e.target.closest('[data-notify-item]');
            if (!item) return;

            const id = item.dataset.id;
            const url = item.dataset.url;
            const wasUnread = item.dataset.read === '0';

            const done = wasUnread
                ? postJson(ENDPOINTS.read(id)).catch(() => {})
                : Promise.resolve();

            done.then(() => {
                if (url) {
                    window.location.href = url;
                } else {
                    load();
                }
            });
        });

        // Badge-only poll keeps the bell honest without opening the dropdown.
        fetch(@json(route('notifications.unread-count')), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
            .then(r => r.ok ? r.json() : Promise.reject(r.status))
            .then(d => setUnread(d.unread_count))
            .catch(() => {});

        setInterval(() => {
            if (!panel.classList.contains('hidden')) return; // open panel refreshes itself
            fetch(@json(route('notifications.unread-count')), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin',
            })
                .then(r => r.ok ? r.json() : Promise.reject(r.status))
                .then(d => setUnread(d.unread_count))
                .catch(() => {});
        }, 60000);
    }

    function initAll() {
        document.querySelectorAll('[data-notify-root]').forEach(initBell);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
</script>
@endonce
