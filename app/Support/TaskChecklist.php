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
 * The ops list is the simulation itself, in the order the server enforces it —
 * a stay runs Booked → Arrived → Checked In → Checked Out, and checking out
 * opens the housekeeping inspection that ends with the room Available again.
 * Every status enum in the app is forward-only, so each department's ops tasks
 * are listed in the only order they can be done in, and each one sits with the
 * role that actually holds the button: Front Desk reserves and settles the
 * bill, Room Management checks the guest in, Restaurant runs the kitchen,
 * Housekeeping inspects, Maintenance closes the repair. See PROCEDURES.md.
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

            // A stay in the order the desk works it: find a free room, take the
            // guest's details, take their money, then greet them on arrival.
            [
                'title' => 'Check Room Availability',
                'description' => 'Open the Rooms page and answer a guest asking what is free. Each room card has an availability calendar showing the dates already booked.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Register a Guest',
                'description' => 'Press Reserve Now on a free room and fill in Register Guest: full name, contact number, email, the government or passport ID you verified, and the check-in and check-out dates and times.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Add Add-ons to a Reservation',
                'description' => "Open the Add-ons expander while registering and lend the guest something extra from Housekeeping's catalogue. Anything showing Out of stock cannot be added.",
                'priority' => 'low',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Process the Reservation Payment',
                'description' => 'Take the payment on the Process Payment step: full or partial, the amount, the method, the payer name and a reference. Complete Reservation is what actually books the room.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Mark a Guest as Arrived',
                'description' => 'When the guest reaches the desk, find their booking in Guest Information and press Arrive. Room Management checks them into the room after that.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],

            // Restaurant work the desk owns: it holds tables and places room
            // service, but the kitchen runs the orders.
            [
                'title' => 'Reserve a Dine-in Table',
                'description' => 'Hold a table in Dine-in Tables for a customer who has rung ahead: their name, contact number, the date and time, and a party size the table can seat.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Seat a Reserved Table',
                'description' => 'Press Customer Arrived on a reserved table when the customer turns up. The restaurant cannot take an order until the table is seated.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Take a Room Service Order',
                'description' => 'Order from the Restaurant page, review the order, then charge it to a checked-in guest. Only a guest who is already checked in can be billed for room service.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'File a Guest Complaint',
                'description' => 'Record what a guest reported in Complaints — the room, the guest, the category — and send it to the department that can fix it.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Follow Up on a Resolved Complaint',
                'description' => 'The badge on Complaints counts the ones the department has closed. Read the resolution note and tell the guest what was done.',
                'priority' => 'low',
                'scope' => self::SCOPE_OPS,
            ],

            // Departure, and the money that has to be settled before it.
            [
                'title' => 'Check a Guest Out',
                'description' => 'Press Check Out in Guest Information and read the final bill with the guest: room charges, room service, add-ons and what they have already paid.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Add an Extra Charge to the Final Bill',
                'description' => 'Put anything the guest used that is not on the bill yet — a minibar item, a late fee — onto the final bill before you settle it.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Settle the Final Bill',
                'description' => 'Take the closing payment with a method, an amount and a reference, then check the guest out. The system refuses to check anyone out while money is still outstanding.',
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

            // Build the inventory first, then work the rooms that are sold.
            [
                'title' => 'Add a Room to the Inventory',
                'description' => 'Use Add Room in Manage Room to put a new room in the hotel: its category, price, description and photo. The room number comes from the category sequence — you do not type it.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => "Update a Room's Details",
                'description' => 'Press Update on a room and correct what is wrong — its name, category, price, description or photo. The same panel shows the dates it is already booked.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Check a Guest In',
                'description' => 'Open Guest Details, find a guest Front Desk has marked as arrived and press Check In. Nothing can be ordered or reported against that room until you do.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Monitor Occupancy',
                'description' => 'Read Guest Details and say how the hotel stands: which rooms hold a guest, who is still waiting to be checked in, and how long each stay has left.',
                'priority' => 'low',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => "Update a Room's Status",
                'description' => 'Set a room to Available, Cleaning or Maintenance from Update Status on the Rooms page. Changing the status of a room with a guest in it closes their stay, so check before you do.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Release a Room After Maintenance',
                'description' => 'Once Maintenance has closed the repair, put the room back to Available so it can be sold again.',
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

            // The kitchen: stock it, lay out the room, then run the orders.
            [
                'title' => 'Add Dishes to the Menu',
                'description' => 'Use Manage Menu to add a dish the kitchen can actually serve: name, category, price, how many you hold, a short description and a photo.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Keep Menu Stock Current',
                'description' => 'Every order placed takes portions off the dish. Work through Manage Menu, restock what is running down and check nothing is left sold out that you can still serve.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Set Up Your Dining Tables',
                'description' => 'Use Manage Tables to lay out the dining room — how many tables, and how many people each one seats.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Take a Dine-In Order',
                'description' => 'Open Orders and take a New Dine-In Order. Only a seated table can be picked — if the list is empty, seat a reserved table in Manage Tables first.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Move an Order Through the Kitchen',
                'description' => 'Take an order from Preparing to Ready, then Delivering, then Completed. A status only ever moves forward, so do not skip a step.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Fulfil a Room Service Order',
                'description' => 'Work the Room Service tab in Orders and run a room order through to Completed so it lands on the guest\'s bill. A room-service order cannot be cancelled.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Cancel a Dine-In Order',
                'description' => 'Cancel a dine-in order the customer changed their mind about. The portions go back into stock — this is the only order type that can be cancelled.',
                'priority' => 'low',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Bill and Close a Dine-In Table',
                'description' => 'Press Bill on an occupied table, take the payment with a method, amount and reference, then Mark Paid and Close Table. You cannot bill a table with food still with the kitchen.',
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

            // One inspection, start to finish. It opens by itself when Front
            // Desk checks a guest out, and ends with the room sellable again.
            [
                'title' => 'Stock the Add-ons Catalogue',
                'description' => 'Fill the Add-ons list with what guests can ask for, and set how many of each you hold. Front Desk lends from this list while registering a guest.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Watch the Room Board',
                'description' => 'Read the room strip across the top of Room Inspections and say which rooms are ready, which are waiting on cleaning and which are out for maintenance.',
                'priority' => 'low',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Start a Room Inspection',
                'description' => 'A room raises an inspection by itself the moment Front Desk checks its guest out. Pick one up with Start inspection.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Record What You Found',
                'description' => 'Say what the room needs — cleaning only, damaged equipment, needs repair or missing items — and write a note describing it.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Report an Issue to Maintenance',
                'description' => 'Send a fault you found to Maintenance with a category and a description. The room goes out to maintenance and the inspection waits until the repair is closed.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Re-inspect After a Repair',
                'description' => 'When Maintenance closes the repair the room comes back for a final pass. Walk it again and either report another issue or finish it.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Complete an Inspection',
                'description' => 'Clean the room, replace the linens, towels and amenities, then mark the inspection completed — that is what puts the room back to Available. It is refused while any issue is still open.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Work a Housekeeping Complaint',
                'description' => 'Take a complaint that belongs to housekeeping from Open to In Progress to Resolved, write what you did in the note, or hand it to Maintenance if it turns out to be a repair.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],
        ],

        // Maintenance owns no page of the site, so all of its work is ops work.
        // Housekeeping's inspection is parked until this queue closes the issue.
        'maintenance' => [
            [
                'title' => 'Receive a Maintenance Request',
                'description' => 'Open Complaints and Concerns and read the queue. Requests reach you from Front Desk, when a guest reports something, and from Housekeeping, when an inspection finds a fault.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Prioritise the Queue',
                'description' => 'Sort what is waiting and say which you would do first. A room with an open issue is out of service, so the rooms nobody can sell come before anything else.',
                'priority' => 'low',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Start a Repair',
                'description' => 'Move a request to In Progress when you begin work on it, so the rest of the hotel can see it is being dealt with.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Record the Repair',
                'description' => 'Write what was wrong and what you did in the note. That note is what Front Desk reads back to the guest.',
                'priority' => 'medium',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Close a Repair',
                'description' => 'Inspect your own work, then move the request to Resolved. Closing the last issue on a room sends it back to Housekeeping for its final pass.',
                'priority' => 'high',
                'scope' => self::SCOPE_OPS,
            ],
            [
                'title' => 'Hand a Request to Housekeeping',
                'description' => 'Send a concern to Housekeeping when it turns out to be cleaning rather than a repair. A request already closed cannot be handed over.',
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
