{{--
    Sidebar footer card: avatar, name, role pill, then My Profile and Sign Out rows.
    Shared by the Dean, Faculty and Student sidebars. Styled with its own CSS
    because the Faculty layout runs on a frozen Tailwind build.

    Props:
        $user            The User model (nullable).
        $name            Display name.
        $role            Role label for the pill (e.g. "Dean Admin").
        $profileHref     Link for My Profile.
        $profileOnclick  Optional onclick for My Profile (Student dashboard sections).

    The Sign Out form keeps id="logoutForm" so each layout's confirm dialog still binds.
--}}
<style>
    .spc { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.09); border-radius: 18px; box-shadow: 0 10px 30px -12px rgba(0,0,0,0.45); overflow: hidden; }
    .spc-head { display: flex; align-items: center; gap: 12px; padding: 14px; border-bottom: 1px solid rgba(255,255,255,0.08); }
    .spc-avatar { width: 46px; height: 46px; flex-shrink: 0; border-radius: 9999px; padding: 2px; background: linear-gradient(135deg, rgba(236,175,190,0.7), rgba(123,23,48,0.6)); }
    .spc-avatar > * { width: 100% !important; height: 100% !important; border-radius: 9999px !important; object-fit: cover; }
    .spc-name { margin: 0 0 6px; font-size: 14px; font-weight: 800; color: #fff; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .spc-role { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 8px; background: rgba(158,27,60,0.6); color: #F6D5DC; font-size: 11px; font-weight: 600; }
    .spc-role .iconify { color: #fff; font-size: 13px; }
    .spc-rows { padding: 6px; }
    .spc-row { box-sizing: border-box; width: 100%; display: flex; align-items: center; gap: 12px; padding: 8px; border: 0; border-radius: 12px; background: transparent; color: #fff; font: inherit; font-size: 14px; font-weight: 600; text-align: left; text-decoration: none; cursor: pointer; transition: background .2s ease; }
    .spc-row:hover, .spc-row:focus-visible { background: rgba(255,255,255,0.06); outline: none; }
    .spc-tile { width: 36px; height: 36px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: rgba(255,255,255,0.08); font-size: 18px; color: #fff; }
    .spc-tile-out { background: rgba(196,66,94,0.45); color: #F6D5DC; }
    .spc-label { flex: 1; min-width: 0; }
    .spc-chev { color: rgba(255,255,255,0.55); font-size: 18px; transition: transform .2s ease; }
    .spc-row:hover .spc-chev { transform: translateX(2px); }
</style>
<div class="spc">
    <div class="spc-head">
        <div class="spc-avatar">
            @include('partials.user-avatar', ['user' => $user, 'name' => $name, 'size' => '', 'rounded' => '', 'currentUser' => true])
        </div>
        <div style="min-width:0">
            <p class="spc-name" title="{{ $name }}">{{ $name }}</p>
            <span class="spc-role"><span class="iconify" data-icon="mdi:shield"></span>{{ $role }}</span>
        </div>
    </div>
    <div class="spc-rows">
        <a href="{{ $profileHref }}" class="spc-row" @isset($profileOnclick) onclick="{{ $profileOnclick }}" @endisset>
            <span class="spc-tile"><span class="iconify" data-icon="lucide:user-round"></span></span>
            <span class="spc-label">My Profile</span>
            <span class="iconify spc-chev" data-icon="lucide:chevron-right"></span>
        </a>
        <form method="POST" action="{{ route('logout') }}" id="logoutForm">
            @csrf
            <button type="submit" class="spc-row">
                <span class="spc-tile spc-tile-out"><span class="iconify" data-icon="lucide:log-out"></span></span>
                <span class="spc-label">Sign Out</span>
                <span class="iconify spc-chev" data-icon="lucide:chevron-right"></span>
            </button>
        </form>
    </div>
</div>
