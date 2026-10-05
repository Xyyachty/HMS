{{-- Black text for everything inside .ink-all (Dashboard, Team Overview, Tasks,
     Activity Logs and Reports for dean, faculty and students). It beats every
     text-* colour utility, hover ones included, and the pages' own tab, chip and
     filter colours. Icons keep their colours, and white text stays white on filled
     buttons and active tabs, along with everything inside them. --}}
<style>
    .ink-all,
    .ink-all.ink-all :where([class*="text-"]:not(.text-white):not([class*="text-white/"]):not(.iconify):not(svg)),
    .ink-all.ink-all :where([class*="text-"]:not(.text-white):not([class*="text-white/"]):not(.iconify):not(svg)):hover,
    .ink-all .main-tab-btn:not(.active),
    .ink-all #teamsTable .member-chip,
    .ink-all #teamsTable .role-mini,
    .ink-all .rp-tab:not(.active),
    .ink-all .srp-tab:not(.active),
    .ink-all .rp-filter.rp-filter,
    .ink-all .rp-filter-clear.rp-filter-clear { color: #111; }

    .ink-all.ink-all.ink-all :is(.btn-sendback, .btn-revise, .btn-approve, .tab-btn.active-tab, .main-tab-btn.active, .rp-tab.active, .srp-tab.active),
    .ink-all.ink-all.ink-all :is(.btn-sendback, .btn-revise, .btn-approve, .tab-btn.active-tab, .main-tab-btn.active, .rp-tab.active, .srp-tab.active):hover,
    .ink-all.ink-all.ink-all [class*="hover:text-white"]:hover { color: #fff; }

    .ink-all.ink-all.ink-all :is(.text-white, .btn-sendback, .btn-revise, .btn-approve, .tab-btn.active-tab, .main-tab-btn.active, .rp-tab.active, .srp-tab.active) :where([class*="text-"]:not(.iconify):not(svg)),
    .ink-all.ink-all.ink-all :is(.text-white, .btn-sendback, .btn-revise, .btn-approve, .tab-btn.active-tab, .main-tab-btn.active, .rp-tab.active, .srp-tab.active) :where([class*="text-"]:not(.iconify):not(svg)):hover { color: inherit; }
</style>
