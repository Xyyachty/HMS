{{--
    One Staff Tools nav badge. $key names the count in $navBadges, which
    HotelNavBadges::forRole() built for the role this sidebar was rendered for.

    Rendered from the server so the number is right on first paint, and hidden rather
    than omitted when the count is zero — syncNavBadges() in builder/ops-shell needs
    the element to still be there when work arrives.
--}}
@php $navBadgeCount = (int) ($navBadges[$key] ?? 0); @endphp
<span data-nav-badge="{{ $key }}"
    class="{{ $navBadgeCount > 0 ? '' : 'hidden' }} ml-auto min-w-[20px] h-[18px] px-1.5 flex items-center justify-center rounded-full bg-amber-400 text-zinc-950 text-[10px] font-bold leading-none">{{ $navBadgeCount > 99 ? '99+' : $navBadgeCount }}</span>
