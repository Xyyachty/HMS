<?php

namespace App\Support;

use App\Models\Task;

/**
 * Which part of the hotel site each task opens for editing.
 *
 * Editing used to be gated on the page alone: hold Front Desk and the whole Home
 * page was yours from the moment you joined a team. That let a student redesign
 * work nobody had asked them for, and left faculty with no way to say "do this
 * bit first" — every page was open from day one.
 *
 * So the site is cut into named sections, each section belongs to exactly one
 * task, and a section is editable only while that task is on the student's
 * plate. Assign nothing and nothing is editable; assign "Brand Your Hotel" and
 * the logo and hotel name unlock, and only those.
 *
 * The join is TaskChecklist's stable key, carried on the task row as
 * tasks.checklist_key — not the title, which is prose and gets rewritten.
 *
 * Sections are marked in the templates as data-hms-section="<key>". The editor
 * (hms-template-editor.js) refuses any element whose nearest data-hms-section
 * ancestor is not in the unlocked set, and hms-site-content.js gates the
 * structured editors — nav links, hero slides, room cards, menu cards — on the
 * same set. Both read window.__HMS_EDITABLE_SECTIONS__, which editor-bridge
 * writes from unlockedFor() below.
 */
class TemplateSectionMap
{
    /**
     * Every editable section of the site.
     *
     * 'page' is the template page the section appears on, matching
     * HotelTemplateBuilder::ROLE_EDITABLE_PAGES. 'all' means it is site
     * furniture that renders on every page — the header and the footer.
     *
     * @var array<string, array{label: string, page: string, role: string}>
     */
    public const SECTIONS = [
        // Site-wide furniture. The logo and the hotel name are one pair of
        // site-wide values (HotelTemplateBuilder::LOGO_IMAGE_ID), which is why
        // they are one section rather than one per page they appear on.
        'brand' => ['label' => 'Logo and hotel name', 'page' => 'all', 'role' => 'front_desk'],
        'nav' => ['label' => 'Navigation menu', 'page' => 'all', 'role' => 'front_desk'],
        'footer' => ['label' => 'Footer', 'page' => 'all', 'role' => 'front_desk'],

        // Home.
        // The headline and the introduction sit inside the hero in both
        // templates, so they are the same section rather than one of their own.
        'hero' => ['label' => 'Hero banner and welcome text', 'page' => 'home', 'role' => 'front_desk'],

        // Rooms. The Home page carries an "Available Rooms" strip drawn from the
        // same room list, so it is part of the rooms section rather than a
        // Front Desk one — otherwise branding would let you edit room cards.
        'rooms' => ['label' => 'Rooms', 'page' => 'rooms', 'role' => 'room_management'],

        // Restaurant, likewise: the Home page's dining strip reads the menu.
        'dining' => ['label' => 'Restaurant and menu', 'page' => 'restaurant', 'role' => 'restaurant_management'],

        // Housekeeping's two pages.
        'amenities' => ['label' => 'Amenities', 'page' => 'amenities', 'role' => 'housekeeping'],
        'experience' => ['label' => 'Experience', 'page' => 'experience', 'role' => 'housekeeping'],
    ];

    /**
     * TaskChecklist key => the sections it unlocks.
     *
     * Only the website tasks appear. A staff-tool task changes no page, so it
     * unlocks nothing here and a student holding only ops work sees a read-only
     * site — which is the intent: the hotel is built first, then run.
     *
     * @var array<string, list<string>>
     */
    public const TASK_SECTIONS = [
        // Task 1
        'fd_brand' => ['brand'],
        'rm_room_types' => ['rooms'],
        'rst_menu' => ['dining'],
        'hk_amenities_page' => ['amenities'],

        // Task 2. Front Desk's second task is the whole of the Home page it has
        // not already got through branding, the navigation menu included.
        'fd_home_design' => ['hero', 'nav', 'footer'],
        'rm_room_media' => ['rooms'],
        'rst_menu_media' => ['dining'],
        'hk_experience_page' => ['experience'],
    ];

    /** @return array<string, array{label: string, page: string, role: string}> */
    public static function all(): array
    {
        return self::SECTIONS;
    }

    public static function exists(string $section): bool
    {
        return array_key_exists($section, self::SECTIONS);
    }

    public static function label(string $section): string
    {
        return self::SECTIONS[$section]['label'] ?? $section;
    }

    /**
     * The sections a single checklist key opens.
     *
     * @return list<string>
     */
    public static function forKey(?string $key): array
    {
        if ($key === null || $key === '') {
            return [];
        }

        return self::TASK_SECTIONS[$key] ?? [];
    }

    /**
     * The sections a student may edit right now, given their task rows.
     *
     * Only rows still on their plate count. A task goes archived the moment it
     * is submitted, so submitting freezes the section it opened and what the
     * faculty review compares is what was handed in; a "Revise" verdict puts
     * the row back to active and the section opens again.
     *
     * Rows handed out before checklist_key existed fall back to the title, so a
     * class already running does not lose its edit rights mid-course.
     *
     * @param  iterable<Task>  $tasks
     * @return list<string>
     */
    public static function unlockedFor(iterable $tasks): array
    {
        $titles = null;
        $sections = [];

        foreach ($tasks as $task) {
            if (($task->status ?? null) !== 'active') {
                continue;
            }

            $key = $task->checklist_key;

            if (!$key) {
                $titles ??= TaskChecklist::keysByTitle();
                $key = $titles[$task->title] ?? null;
            }

            foreach (self::forKey($key) as $section) {
                $sections[$section] = true;
            }
        }

        return array_keys($sections);
    }

    /**
     * The same answer for one team member in one department, read fresh.
     *
     * DepartmentTemplatePage already has the rows in hand and should pass them
     * to unlockedFor() instead; this is for the standalone template routes,
     * which render the site without booting a department page.
     *
     * @return list<string>
     */
    public static function unlockedForMember(?int $facultyId, ?string $groupName, string $role): array
    {
        if (!$facultyId || !$groupName) {
            return [];
        }

        $tasks = Task::where('faculty_id', $facultyId)
            ->forTeam($groupName)
            ->where('role', $role)
            ->where('status', 'active')
            ->get();

        return self::unlockedFor($tasks);
    }

    /**
     * Sections grouped by the page they live on, for the builder's own UI.
     *
     * @param  list<string>  $unlocked
     * @return array<string, list<array{key: string, label: string}>>
     */
    public static function describe(array $unlocked): array
    {
        $out = [];

        foreach ($unlocked as $key) {
            if (!self::exists($key)) {
                continue;
            }

            $out[self::SECTIONS[$key]['page']][] = [
                'key' => $key,
                'label' => self::SECTIONS[$key]['label'],
            ];
        }

        return $out;
    }

    /**
     * Section keys owned by a role, whether or not they are unlocked.
     *
     * @return list<string>
     */
    public static function forRole(string $role): array
    {
        $out = [];

        foreach (self::SECTIONS as $key => $section) {
            if ($section['role'] === $role) {
                $out[] = $key;
            }
        }

        return $out;
    }
}
