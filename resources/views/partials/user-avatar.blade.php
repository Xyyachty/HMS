{{--
    Renders a single user's avatar: the uploaded photo, or the generic
    blank-person picture (User::avatar_url) when none is uploaded.

    Props (all required unless noted):
        $user         The User model (nullable).
        $name         Display name (string), used for the alt text.
        $size         Tailwind sizing classes (e.g. "w-9 h-9"). Default "w-9 h-9".
        $rounded      Rounded corner classes. Default "rounded-xl".
        $extraClasses Extra classes appended to the image. Default "".
        $id           Optional DOM id. Default "".
        $altFallback  Alt text when $name is empty. Default "User".
--}}
@php
    $size         = $size         ?? 'w-9 h-9';
    $rounded      = $rounded      ?? 'rounded-xl';
    $extraClasses = $extraClasses ?? '';
    $idAttr       = isset($id) && $id !== '' ? 'id="' . $id . '"' : '';
    $altText      = trim((string) ($name ?? '')) !== '' ? $name : ($altFallback ?? 'User');
    $src          = $user?->avatar_url ?? asset('images/default-avatar.svg');

    $imgClasses = trim($size . ' ' . $rounded . ' object-cover shrink-0 ' . $extraClasses);
    echo '<img ' . $idAttr . ' src="' . e($src) . '" alt="' . e($altText) . '" class="' . e($imgClasses) . '">';
@endphp
