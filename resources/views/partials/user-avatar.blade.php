{{--
    Renders a single user's avatar as either an uploaded photo or an initials
    chip, depending on whether $user->avatar is set.

    Props (all required unless noted):
        $user         The User model (nullable). When $user->avatar is empty, the
                      initials chip is rendered instead.
        $name         Display name (string). Used for the alt text and as the
                      source of initials. Required because $user may be null.
        $size         Tailwind sizing classes (e.g. "w-9 h-9"). Default "w-9 h-9".
        $rounded      Rounded corner classes. Default "rounded-xl".
        $currentUser  When true and no photo is uploaded, the initials chip uses
                      the brand-gradient "me" style. Default false.
        $extraClasses Extra classes appended to the wrapper. Default "".
        $id           Optional DOM id. Default "".
        $altFallback  Alt text when $name is empty. Default "User".

    Used by:
        - Student sidebar profile button
        - Student My Team / team row avatars
        - Faculty Manage Students table
        - Faculty Results page
        - Dean Recent Students card
        - Dean sidebar
--}}
@php
    $size         = $size         ?? 'w-9 h-9';
    $rounded      = $rounded      ?? 'rounded-xl';
    $currentUser  = $currentUser  ?? false;
    $extraClasses = $extraClasses ?? '';
    $idAttr       = isset($id) && $id !== '' ? 'id="' . $id . '"' : '';
    $altText      = trim((string) ($name ?? '')) !== '' ? $name : ($altFallback ?? 'User');

    $hasPhoto = !empty($user?->avatar);
    $initials = '';
    if (!$hasPhoto) {
        // Single source of truth: prefer the User model's accessor so that any
        // change to first_name / last_name on the User row is reflected wherever
        // this partial is rendered (Student / Faculty / Dean / builders).
        // Fall back to the supplied display name only when no User is bound
        // (e.g. a synthetic group-member row in the dashboard's Team section).
        if ($user) {
            $initials = strtoupper((string) $user->initials);
        } else {
            $rawName = trim((string) ($name ?? ''));
            if ($rawName !== '') {
                $parts = preg_split('/\s+/', $rawName);
                $first = $parts[0][0] ?? '';
                $last  = count($parts) > 1 ? substr(end($parts), 0, 1) : '';
                $initials = strtoupper($first . $last);
            }
        }
        if ($initials === '') {
            $initials = '?';
        }
    }

    if ($hasPhoto) {
        $imgClasses = trim($size . ' ' . $rounded . ' object-cover ' . $extraClasses);
        echo '<img ' . $idAttr . ' src="' . e($user->avatar_url) . '" alt="' . e($altText) . '" class="' . e($imgClasses) . '">';
    } else {
        $chipClasses = trim(
            $size . ' ' . $rounded . ' flex items-center justify-center shrink-0 ' . $extraClasses
        );
        if ($currentUser) {
            $chipClasses .= ' brand-gradient shadow-md shadow-brand/20';
            $textClasses = 'text-white text-xs font-bold';
        } else {
            $chipClasses .= ' bg-slate-100';
            $textClasses = 'text-slate-500 text-xs font-bold';
        }
        echo '<div ' . $idAttr . ' class="' . e($chipClasses) . '">';
        echo '<span class="' . e($textClasses) . '">' . e($initials) . '</span>';
        echo '</div>';
    }
@endphp
