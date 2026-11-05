{{-- The guest pages' header: the hotel's name and logo, and the way back to its website. --}}
<header class="top">
  <div class="wrap">
    <a class="brand" href="{{ $siteUrl }}">
      <img class="brand-logo" src="{{ $theme['logo'] ?: $theme['defaultLogo'] }}" alt="" referrerpolicy="no-referrer"
        data-fallback="{{ $theme['defaultLogo'] }}" onerror="if (!this.dataset.failed) { this.dataset.failed = '1'; this.src = this.dataset.fallback; } else { this.style.display = 'none'; }">
      <span>{{ $brandName }}</span>
    </a>
    <a class="back" href="{{ $siteUrl }}"><i class="fa-solid fa-arrow-left"></i> Hotel website</a>
  </div>
</header>
