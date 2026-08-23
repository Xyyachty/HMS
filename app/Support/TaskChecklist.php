<?php

namespace App\Support;

/**
 * The assignable task checklist faculty tick from on the Create Task tab.
 *
 * This lived inline in pagerole.blade.php, where its variable shadowed the
 * controller's $tasksByRole (the real Task rows) for the rest of the template.
 * It is data, every role needs it, and it is worth reading without opening a
 * 3,000-line view — so it lives here.
 *
 * Two kinds of work, marked so the wording can say which is which:
 *
 *  - SCOPE_SITE  builds the team's hotel website. The faculty review's
 *                Before/After comparison is built from template snapshots, so
 *                these are the tasks it can actually show a diff for.
 *  - SCOPE_OPS   runs the hotel through the department's staff tools. These
 *                touch bookings, orders, inspections and complaints rather
 *                than the site, so their review shows no site changes and is
 *                judged by opening the department page instead.
 *
 * Maintenance owns no editable page (HotelTemplateBuilder::ROLE_EDITABLE_PAGES),
 * so everything it can be asked to do is ops work.
 *
 * The site logo and the hotel name are single site-wide values — any owning role
 * writes the same key and claimSharedLogo() hands it over — so they are assigned
 * to Front Desk alone rather than repeated per role, which read as three separate
 * logos to change.
 */
class TaskChecklist
{
    public const SCOPE_SITE = 'site';
    public const SCOPE_OPS = 'ops';

    /**
     * Keyed by role, in the order they should appear under each department.
     * Site work first, then the ops work that follows from it.
     *
     * @var array<string, list<array{title: string, description: string, priority: string, scope: string}>>
     */
    private const TASKS = [
        'front_desk' => [
            [
                'title' => 'Change Logo',
                'description' => 'Replace the default logo with your own. It is one image for the whole site — the header, the footer and every page read it.',
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Name Your Hotel',
                'description' => "Replace the placeholder name in the header with your team's hotel name.",
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Choose Your Hero Images',
                'description' => 'Pick the five photographs that rotate across the top of the Home page. They are the first thing a guest sees.',
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Write the Welcome Section',
                'description' => 'Rewrite the headline and the introduction under the hero so they describe your hotel rather than the sample text.',
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Set Up the Navigation Menu',
                'description' => 'Check every link in the top menu points at the right page and is labelled the way your hotel would label it.',
                'priority' => 'low',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Check In a Guest',
                'description' => 'Take a booking through Guest Information: confirm who the guest is, assign the room and complete the check-in.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Take a Room Service Order',
                'description' => 'Handle a room service request end to end in Room Service and charge it to the room.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Seat a Walk-In Guest',
                'description' => 'Use Dine-in Tables to seat a guest who arrives without a reservation, and free the table again when they leave.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Resolve a Guest Complaint',
                'description' => 'Answer a complaint in Complaints, pass it to the department that can fix it, and tell the guest what was done.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Review the Revenue Reports',
                'description' => 'Read the Reports page and say where your hotel earns most — rooms, dine-in or room service.',
                'priority' => 'low',
                'scope' => self::SCOPE_OPS,
            ],
        ],

        'room_management' => [
            [
                'title' => 'Add Your Room Types',
                'description' => 'Replace the sample rooms with the room types your hotel offers, each with its own name.',
                'priority' => 'high',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Photograph Every Room',
                'description' => 'Give every room type its own picture on the Rooms page. No room should be left on the placeholder image.',
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Price Your Rooms',
                'description' => 'Set a nightly rate for each room type and be ready to explain why the more expensive ones cost more.',
                'priority' => 'high',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Write Room Descriptions',
                'description' => 'Describe each room type and list what it includes, so a guest can tell them apart without asking.',
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Style the Rooms Page',
                'description' => 'Lay out the Rooms page so it matches the rest of the site — colours, fonts and spacing.',
                'priority' => 'low',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Assign a Guest to a Room',
                'description' => 'Use Manage Room to put a booked guest in a room and move that room through its statuses.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Keep Room Status Current',
                'description' => 'Work through Manage Room and make every room read the right status — available, occupied, cleaning or maintenance.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],
        ],

        'restaurant_management' => [
            [
                'title' => 'Build Your Menu',
                'description' => 'Replace the sample dishes with your own menu, grouped so a guest can find what they want.',
                'priority' => 'high',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Photograph Your Dishes',
                'description' => 'Add a picture to every dish on the Restaurant page.',
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Price the Menu',
                'description' => 'Set a price for every dish and check the menu reads consistently.',
                'priority' => 'high',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Style the Restaurant Page',
                'description' => 'Lay out the Restaurant page so it matches the rest of the site.',
                'priority' => 'low',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Set Up Your Dining Tables',
                'description' => 'Use Manage Tables to lay out the dining room — how many tables, and how many people each one seats.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Take a Dine-In Order',
                'description' => 'Put an order through Orders for a seated table and close the bill when they are finished.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
        ],

        'housekeeping' => [
            [
                'title' => 'Build the Amenities Page',
                'description' => 'Fill in the Amenities page with what your hotel actually offers, so it matches the add-ons you lend out.',
                'priority' => 'high',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Write the Experience Page',
                'description' => 'Write the Experience page so it tells a guest what staying at your hotel is like.',
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Style Your Pages',
                'description' => 'Lay out the Experience and Amenities pages so they match the rest of the site.',
                'priority' => 'low',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Stock the Add-ons Catalogue',
                'description' => 'Fill the Add-ons list with what guests can ask for, and set how many of each you hold.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Complete a Room Inspection',
                'description' => 'Inspect a room in Room Inspections and take it through to completed.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Report a Room Issue',
                'description' => 'Log a fault you find during an inspection so Maintenance picks it up, and follow it until the room is ready again.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Handle a Housekeeping Complaint',
                'description' => 'Take a complaint that belongs to housekeeping, fix what caused it and record what you did.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],
        ],

        // Maintenance owns no page of the site, so all of its work is ops work.
        'maintenance' => [
            [
                'title' => 'Accept a Maintenance Concern',
                'description' => 'Pick up a concern raised against your department and confirm you are dealing with it.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Repair a Reported Fault',
                'description' => 'Fix a fault reported from a room inspection and record what the repair involved.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Return a Room to Service',
                'description' => 'Close out a repair so the room leaves maintenance and can be sold again.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Keep a Repair Log',
                'description' => 'Write up what has been repaired so far and which faults keep coming back.',
                'priority' => 'low',
                'scope' => self::SCOPE_OPS,
            ],
        ],
    ];

    /**
     * The whole checklist, keyed by role.
     *
     * Ordered by HotelTemplateBuilder::ROLES so a role added there cannot be
     * silently missed here — it appears with an empty list instead.
     *
     * @return array<string, list<array{title: string, description: string, priority: string, scope: string}>>
     */
    public static function all(): array
    {
        $out = [];

        foreach (array_keys(HotelTemplateBuilder::ROLES) as $role) {
            $out[$role] = self::TASKS[$role] ?? [];
        }

        return $out;
    }

    /** @return list<array{title: string, description: string, priority: string, scope: string}> */
    public static function forRole(string $role): array
    {
        return self::TASKS[$role] ?? [];
    }

    /** Short label for the scope badge on each checklist card. */
    public static function scopeLabel(string $scope): string
    {
        return $scope === self::SCOPE_OPS ? 'Staff tools' : 'Website';
    }
}
