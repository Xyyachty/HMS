{{--
    First-login guided tour for the Dean, Faculty and Student dashboards.

    Included by all three portals; renders only on the role's dashboard. Opens by
    itself until users.tour_completed_at is set (Finish or Skip posts to
    account.tour), and again whenever the dashboard is opened with ?tour=1, which
    is where Restart Guided Tour in the sidebar profile card points.

    Steps point at existing elements by id or data-tour; none of them change the
    page. A step whose element is hidden at that moment (the sidebar on a phone)
    shows as a centred card instead, so every step still reads on small screens.
    Step keys: el (selector), title, text, section (Student dashboard section to
    show first), optional (drop when el is not on the page), mobile (phones only).
--}}
@php
    $tourUser = auth()->user();
    $tourRole = match (true) {
        request()->routeIs('dean.dashboard') => 'dean',
        request()->routeIs('faculty.dashboard') => 'faculty',
        request()->routeIs('students.dashboard') => 'student',
        default => null,
    };
    $tourAuto = $tourUser && $tourRole
        && (request()->boolean('tour') || (!$tourUser->tour_completed_at && !$tourUser->must_change_password));
    $tourSteps = [];

    $tourMenu = ['el' => '[data-tour="menu-toggle"]', 'title' => 'Menu', 'text' => 'Tap here to open the menu. All the pages in this tour are inside it.', 'mobile' => true];
    $tourBell = ['el' => '[data-notify-root]', 'title' => 'Notifications', 'text' => 'The bell shows a number when something new needs your attention. Click it to read your notifications.'];
    $tourHelp = ['el' => '[data-tour="help"]', 'title' => 'My Profile and Help', 'text' => 'My Profile is where you change your photo and password. Click Restart Guided Tour anytime to see this tour again.'];

    if ($tourRole === 'dean') {
        $tourSteps = [
            ['title' => 'Welcome, Dean!', 'text' => 'This short tour shows you the main parts of the Dean Portal. Use Next and Back to move, or Skip to close it.'],
            $tourMenu,
            ['el' => '[data-tour="nav-dashboard"]', 'title' => 'Dashboard', 'text' => 'Your home page. It shows totals for students, faculty and teams, the latest tasks and activity, and how far each team is.'],
            ['el' => '[data-tour="dash-stats"]', 'title' => 'Quick Totals', 'text' => 'Click a card to jump to that list of students, faculty or teams.'],
            ['el' => '[data-tour="nav-users"]', 'title' => 'Manage User', 'text' => 'Add faculty and student accounts, upload many at once from a file, and approve new students.'],
            ['el' => '[data-tour="nav-teams"]', 'title' => 'Teams Overview', 'text' => 'See every faculty member, their student teams, and each team\'s task progress.'],
            ['el' => '[data-tour="nav-reports"]', 'title' => 'Reports', 'text' => 'View and print reports on teams, tasks and results.'],
            $tourBell,
            $tourHelp,
        ];
    } elseif ($tourRole === 'faculty') {
        $tourSteps = [
            ['title' => 'Welcome!', 'text' => 'This short tour shows you how to run the hotel simulation for your class. Use Next and Back to move, or Skip to close it.'],
            $tourMenu,
            ['el' => '[data-tour="nav-dashboard"]', 'title' => 'Dashboard', 'text' => 'Your home page. It shows your students and teams, the latest tasks, and how many tasks each team has had approved.'],
            ['el' => '[data-tour="nav-students"]', 'title' => 'Manage Students', 'text' => 'Start here. Add your students one by one, or upload the whole class at once from an Excel file.'],
            ['el' => '[data-tour="nav-teams"]', 'title' => 'Manage Teams', 'text' => 'Create teams, put students in them, and give each student a hotel role (Front Desk, Rooms, Restaurant, Housekeeping). Here you also set tasks, open submitted work, and approve it or ask for a revision.'],
            ['el' => '[data-tour="nav-reports"]', 'title' => 'Reports', 'text' => 'View and print reports on your teams and their tasks.'],
            $tourBell,
            $tourHelp,
        ];
    } elseif ($tourRole === 'student') {
        $tourRoles = $studentRoles ?? [];
        $tourSeats = \App\Support\SimulationPhase::seatsFor($groupMembership ?? null);
        $customizeText = [
            'front_desk' => 'Front Desk: design the Home and Experience pages, with your hotel\'s name, words and colours.',
            'room_management' => 'Room Management: add your room types, photos and prices on the Rooms page.',
            'restaurant_management' => 'Restaurant: set up the Restaurant page and its menu.',
            'housekeeping' => 'Housekeeping: set up the Amenities page and the add-ons guests can ask for.',
        ];
        $simulationText = [
            'front_desk' => 'Front Desk: book walk-in guests, check guests in, and handle room service, dining tables, amenities, promos and guest complaints.',
            'room_management' => 'Room & Maintenance: keep rooms ready and their status up to date, and fix problems guests report.',
            'housekeeping' => 'Housekeeping: inspect rooms, look after amenities and add-ons, and answer guest complaints.',
            'restaurant_management' => 'Restaurant Services: run the menu, food orders and dining tables.',
        ];
        $tourCustomize = implode(' ', array_values(array_intersect_key($customizeText, array_flip($tourRoles))));
        $tourSimulate = implode(' ', array_values(array_intersect_key($simulationText, array_flip($tourSeats))));
        $tourRoleLabel = $tourRoles
            ? implode(' & ', array_map(fn ($r) => \App\Support\HotelTemplateBuilder::seatLabel($r), $tourRoles))
            : null;

        $tourSteps = [
            ['section' => 'home', 'title' => 'Welcome, ' . ($homeFirstName ?? 'Student') . '!',
                'text' => 'This short tour shows you around your hotel team\'s workspace.'
                    . ($tourRoleLabel ? ' Your role is ' . $tourRoleLabel . '.' : ' Your faculty will give you a role soon.')
                    . ' Use Next and Back to move, or Skip to close it.'],
            $tourMenu,
            ['section' => 'home', 'el' => '#nav-home', 'title' => 'Dashboard', 'text' => 'Your home page. It shows your tasks, your team\'s progress and your latest activity.'],
            ['section' => 'home', 'el' => '#nav-group', 'title' => 'Team Overview', 'text' => 'See your teammates and their roles, your team deadline, and your hotel concept.'
                . (in_array('front_desk', $tourRoles, true) ? ' As Front Desk, you write the hotel concept here and send it to your faculty.' : '')],
            ['section' => 'home', 'el' => '#nav-tasks', 'title' => 'Tasks', 'text' => 'All the work for your role. The project has two phases: Hotel Customization, then Hotel Simulation.'],
            ['section' => 'tasks', 'el' => '[data-tour="phase"]', 'optional' => true, 'title' => 'Phase 1: Hotel Customization', 'text' => 'First, your team builds the hotel\'s website. This box counts how many of your team\'s tasks your faculty has approved.'],
            ['section' => 'tasks', 'el' => '[data-tour="customize"]', 'optional' => true, 'title' => 'Customize Your Pages', 'text' => 'Click Customize to open the website editor. ' . $tourCustomize],
            ['section' => 'tasks', 'el' => '#tasksLiveContainer', 'title' => 'Your Task List',
                'text' => 'Click a task to open it, or the book icon to read its instructions. When you finish, click the check button. Your faculty then approves it or asks you to revise it.'],
            ['section' => 'tasks', 'el' => '[data-tour="start-simulation"]', 'optional' => true, 'title' => 'Phase 2: Hotel Simulation',
                'text' => 'Click Start Simulation to run your hotel like a real one. ' . $tourSimulate],
            ['section' => 'tasks', 'el' => '[data-tour="hotel-site"]', 'optional' => true, 'title' => 'Your Hotel Website', 'text' => 'Open your hotel to guests here. Then share the link so guests can book rooms online.'],
            ['section' => 'home', 'el' => '#nav-activity', 'title' => 'Activity Logs', 'text' => 'A record of what you and your teammates have done.'],
            ['section' => 'home', 'el' => '#nav-reports', 'title' => 'Reports', 'text' => 'See your own work and your team\'s work in numbers.'],
            $tourBell + ['section' => 'home'],
            $tourHelp + ['section' => 'home'],
        ];
    }
@endphp
@if ($tourRole)
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css">
<style>
    .driver-popover.hms-tour, .driver-popover.hms-tour * { font-family: 'Manrope', sans-serif; }
    .driver-popover.hms-tour { max-width: min(22rem, calc(100vw - 2rem)); border-radius: 1rem; padding: 1.125rem; color: #2A1118; }
    .hms-tour .driver-popover-title { font-size: 1rem; font-weight: 800; color: #7B1730; }
    .hms-tour .driver-popover-description { font-size: 0.875rem; line-height: 1.5; color: #47262D; }
    .hms-tour .driver-popover-progress-text { font-size: 0.75rem; font-weight: 700; color: #6B4A54; }
    .hms-tour .driver-popover-footer { flex-wrap: wrap; gap: 0.5rem; }
    .hms-tour .driver-popover-footer button { font: inherit; font-size: 0.8125rem; font-weight: 700; text-shadow: none; padding: 0.375rem 0.875rem; border-radius: 0.625rem; border: 1px solid #E4D3CF; background: #fff; color: #47262D; }
    .hms-tour .driver-popover-footer .driver-popover-next-btn { background: #7B1730; border-color: #7B1730; color: #fff; }
    .hms-tour .driver-popover-footer .hms-tour-skip { border-color: transparent; color: #6B4A54; text-decoration: underline; }
    .hms-tour .driver-popover-navigation-btns { flex: 1; justify-content: flex-end; gap: 0.375rem; }
</style>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const defs = @json($tourSteps);
    const saveUrl = @json(route('account.tour'));
    let saved = @json((bool) $tourUser?->tour_completed_at);
    let tour = null;

    function shown(el) {
        const r = el.getBoundingClientRect();
        return r.width > 0 && r.height > 0 && r.right > 0 && r.left < window.innerWidth;
    }

    // Shows the step's dashboard section, then points the step at its element,
    // or centres it when the element is hidden (the sidebar on a phone).
    function prepare(step) {
        if (step.section && typeof showSection === 'function') showSection(step.section);
        const el = step.el && document.querySelector(step.el);
        if (el && shown(el)) step.element = step.el; else delete step.element;
    }

    function save() {
        if (saved) return;
        saved = true;
        fetch(saveUrl, {
            method: 'POST',
            keepalive: true,
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        }).catch(function () {});
    }

    window.startGuidedTour = function () {
        if (!window.driver || tour) return false;
        if (typeof closeMobileSidebar === 'function') closeMobileSidebar();

        const mobile = window.innerWidth < 1024;
        const steps = defs
            .filter(function (s) { return !(s.mobile && !mobile) && !(s.optional && !document.querySelector(s.el)); })
            .map(function (s) { return { el: s.el, section: s.section, popover: { title: s.title, description: s.text } }; });

        function go(i) {
            if (i >= steps.length) { tour.destroy(); return; }
            prepare(steps[i]);
            tour.moveTo(i);
        }

        tour = window.driver.js.driver({
            steps: steps,
            popoverClass: 'hms-tour',
            showProgress: true,
            progressText: 'Step {' + '{current}} of {' + '{total}}',
            showButtons: ['next', 'previous'],
            nextBtnText: 'Next',
            prevBtnText: 'Back',
            doneBtnText: 'Finish',
            allowClose: false,
            disableActiveInteraction: true,
            stagePadding: 6,
            stageRadius: 12,
            onNextClick: function () { go(tour.getActiveIndex() + 1); },
            onPrevClick: function () { go(tour.getActiveIndex() - 1); },
            onPopoverRender: function (popover, opts) {
                if (opts.state.activeIndex === steps.length - 1) return;
                const skip = document.createElement('button');
                skip.type = 'button';
                skip.className = 'hms-tour-skip';
                skip.textContent = 'Skip';
                skip.addEventListener('click', function () { tour.destroy(); });
                popover.footerButtons.prepend(skip);
            },
            onDestroyed: function () {
                save();
                tour = null;
                if (steps[0].section && typeof showSection === 'function') showSection('home');
            },
        });

        prepare(steps[0]);
        tour.drive();
        return false;
    };

    @if ($tourAuto)
    window.addEventListener('load', function () {
        try {
            const url = new URL(window.location.href);
            url.searchParams.delete('tour');
            history.replaceState(null, '', url);
        } catch (e) { /* ignore */ }
        // Waits out the sign-in welcome alert so the two do not stack.
        (function start() {
            if (window.Swal && Swal.isVisible()) { setTimeout(start, 300); return; }
            window.startGuidedTour();
        })();
    });
    @endif
})();
</script>
@endif
