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
 * The two kinds are not interleaved. A team customises its site before it runs
 * the hotel, so the leading positions are website work for every role and the ops
 * work starts at the same number for all of them at once — past the longest site
 * list any one role has, never earlier than SITE_STEPS. allByStep() works that
 * out rather than trusting these arrays to be counted out by hand, so adding
 * design work to one role moves the whole simulation back a step instead of
 * leaving that role building its page while another is already running the hotel.
 *
 * Roles do not have the same amount of design work, because they do not own the
 * same amount of the site: Front Desk holds the home page and the chrome every
 * other page inherits — the palette, the type, the footer — while Maintenance owns
 * no page at all (ROLE_EDITABLE_PAGES) and so has no website work to be given.
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

    /** How many leading task numbers are reserved for website work. */
    public const SITE_STEPS = 2;

    /**
     * Keyed by role, in the order they should appear under each department.
     * The two website tasks first, then the ops work that follows from them.
     *
     * @var array<string, list<array{title: string, description: string, priority: string, scope: string}>>
     */
    private const TASKS = [
        'front_desk' => [
            [
                'title' => 'Brand Your Hotel',
                'description' => "Give the site your hotel's identity: replace the default logo with your own, and the placeholder name in the header with your team's hotel name. Both are single site-wide values — the header, the footer and every page read them.",
                'priority' => 'high',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Design the Home Page',
                'description' => 'Customise the page a guest lands on: pick the five photographs that rotate across the top, rewrite the headline and the introduction under them so they describe your hotel rather than the sample text, and rename the links in the top menu so they read the way your hotel would label them.',
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],

            [
                'title' => 'Write Your Hotel\'s Story',
                'description' => "Replace the sample words with your own: the tagline over the headline, the paragraph introducing the hotel, and the contact block every footer prints - address, phone, email and the hours the desk keeps. These are one record for the whole site, so what you write here is what the Rooms page and the Restaurant page say too.",
                'priority' => 'high',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Choose the Site\'s Colours',
                'description' => "Open Background Colours from the toolbar and give the site its own palette. Start with the main website colour - the text, cards and borders follow it automatically - then set the header and the footer if they should stand apart from it. Pick a background your text still reads against.",
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Set the Site\'s Typography',
                'description' => "Choose the typeface the whole site is set in, the size body text is read at, and the colours for ordinary text and for headings. Type is site-wide: it reaches pages your role cannot otherwise edit, so choose something legible at a paragraph's length, not only in a heading.",
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Add Your Social Profiles',
                'description' => "Put the hotel's social accounts in the footer. Add only the networks the hotel actually uses - each one shows as its own icon, and an account nobody keeps is worse than a missing one.",
                'priority' => 'low',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Fill In the Promos Section',
                'description' => "Write the offers the hotel is running on the Home page: a picture for each, the name of the offer, what it includes in a sentence or two, the saving it carries, and the condition it comes with. Three cards is what the section is built for.",
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Build the Partner Brands Strip',
                'description' => "List the businesses the hotel works with - the travel agency, the cafe, the airline. Add a card per brand and upload each one's logo; a brand with no logo yet shows its name instead, so the strip is never half empty. Remove the sample brands you are not using.",
                'priority' => 'low',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Introduce Your Team',
                'description' => "Put four real people in Our Team: a photograph of each, their full name, and the position they hold. This is the section a guest reads to see who runs the hotel, so use the roles your team actually assigned.",
                'priority' => 'medium',
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
                // The step DesignTaskChain watches: approving it hands the team
                // "Detail Every Room Category", which is not on this list because
                // it cannot be done before the categories are agreed.
                'title' => 'Create Your Room Categories',
                'description' => 'Decide the room categories your hotel sells and set them up on the Rooms page — add the ones you need with the + tab, rename the starting ones that do not fit, and drop the sample wording. Get the names right first; you describe and price them once your faculty approves this.',
                'priority' => 'high',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Build Your Room Types',
                'description' => 'Replace the sample rooms with the room types your hotel actually offers. Give each one its own name and describe what it includes, so a guest can tell them apart without asking.',
                'priority' => 'high',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Photograph and Price Every Room',
                'description' => 'Give every room type its own picture — none should be left on the placeholder image — set a nightly rate you can explain, and lay the Rooms page out so it matches the rest of the site.',
                'priority' => 'high',
                'scope' => self::SCOPE_SITE,
            ],

            // Build the inventory first, then work the rooms that are sold.
            [
                'title' => 'Style the Rooms Page',
                'description' => "Give the Rooms page its own look: the colour behind the room cards, the colour of the cards themselves, and the colour of the booking popup that opens when a guest picks one. The card colour is shared with the preview on the Home page, so it sets the tone in both places at once.",
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'List What Each Room Includes',
                'description' => "Set the amenity chips under every room - the bed, the view, the bath, the wifi - so a guest can compare two rooms without opening either. Use the icons that match what is actually in the room rather than leaving the sample set.",
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],
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
                'title' => 'Photograph and Price the Menu',
                'description' => 'Add a picture to every dish, set a price for every dish, and lay the Restaurant page out so it matches the rest of the site.',
                'priority' => 'high',
                'scope' => self::SCOPE_SITE,
            ],

            // The kitchen: stock it, lay out the room, then run the orders.
            [
                'title' => 'Organise the Menu into Categories',
                'description' => "Sort the menu into the sections a diner reads it by - starters, mains, desserts, drinks - and name them the way your restaurant would. The tabs on the Restaurant page and the preview on the Home page both follow these, so a dish in the wrong section is in the wrong section twice.",
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Style the Menu Cards',
                'description' => "Set the colour of the dish cards and the background of the Restaurant section behind them. The card colour carries to the dining preview on the Home page, so check both before you call it done.",
                'priority' => 'low',
                'scope' => self::SCOPE_SITE,
            ],
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
                // The gate on the facilities list: nobody may add, edit,
                // photograph or remove an amenity until this lands on them. See
                // AmenityTaskDesk, which reads a task row back by this title.
                'title' => 'Customize Hotel Amenities',
                'description' => "Add and customize the hotel's amenities displayed in the default template. Give each one its name, where it is, the hours it keeps, whether it is open, and a description a guest can read, then photograph it: the card cycles through every picture you upload and View Details opens them in a carousel.",
                'priority' => 'high',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Build the Amenities Page',
                'description' => 'Fill in the Amenities page with what your hotel actually offers, so it matches the add-ons you lend out.',
                'priority' => 'high',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Write the Experience Page',
                'description' => 'Write the Experience page so it tells a guest what staying at your hotel is like, and lay it out so it matches the rest of the site.',
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],

            [
                'title' => 'Colour the Amenities and Experience Pages',
                'description' => "Give your two pages their own backgrounds from Background Colours. They are the pages guests read after the rooms, so they should look like the same hotel - close enough to the site's palette to belong to it, different enough to be their own.",
                'priority' => 'low',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Illustrate the Experience Page',
                'description' => "Photograph the Experience page: a picture for each thing you describe, so the page shows the stay rather than only claiming it. Replace every sample image; a page of stock photographs reads as a page nobody wrote.",
                'priority' => 'medium',
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

    /**
     * The same checklist pivoted: step index => [role => task].
     *
     * Position N is the same stage for every role, so the Create Task tab can
     * hand out "Task 1" to a whole team in one tick. The first SITE_STEPS
     * positions are the website build and hold nothing else: a role's site
     * tasks fill them in order, and its ops tasks start after them, so Task 3
     * is where the simulation begins for everybody at once. Maintenance owns no
     * page of the site, so it simply has no card in Tasks 1 and 2 and its queue
     * opens at Task 3 with the rest.
     *
     * A step is only as wide as the roles that still have work at that position,
     * so the later ones hold fewer entries as the shorter lists run out.
     *
     * Role order inside a step follows all(), which follows
     * HotelTemplateBuilder::ROLES.
     *
     * @return array<int, array<string, array{title: string, description: string, priority: string, scope: string}>>
     */
    public static function allByStep(): array
    {
        $byStep = [];

        // Where the simulation starts, for everybody at once: past the longest
        // list of website work any one role has. Measured across all roles rather
        // than per role, or a role with more design work would still be building
        // its page in the same numbered step another was already running the
        // hotel in, and a step would stop meaning one stage. Never below
        // SITE_STEPS, so the two reserved website positions stand even if every
        // role's site list were shorter than that.
        $opsStart = max(self::SITE_STEPS, ...array_map(
            fn ($tasks) => count(array_filter(
                $tasks,
                fn ($task) => ($task['scope'] ?? self::SCOPE_SITE) === self::SCOPE_SITE
            )),
            array_values(self::all())
        ));

        foreach (self::all() as $role => $tasks) {
            $siteStep = 0;
            $opsStep = $opsStart;

            foreach ($tasks as $task) {
                $isSite = ($task['scope'] ?? self::SCOPE_SITE) === self::SCOPE_SITE;
                $byStep[$isSite ? $siteStep++ : $opsStep++][$role] = $task;
            }
        }

        ksort($byStep);

        return $byStep;
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
