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
                // Task 01's Front Desk activity. The row itself is seeded by
                // HotelConceptDesk the moment a student holds the role, so
                // assigning this step claims that row rather than writing a
                // second one — see FacultyController::storeTask.
                'title' => HotelConceptDesk::TASK_TITLE,
                'description' => HotelConceptDesk::TASK_DESCRIPTION,
                'priority' => 'high',
                'scope' => self::SCOPE_SITE,
            ],
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
                'title' => 'Write the Rooms Page Introduction',
                'description' => "Write the words above the room cards: the small line over the heading, the heading itself, and the paragraph under it. They are what a guest reads before any price, so say what kind of rooms these are rather than repeating the word Rooms.",
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Name the Category Tabs',
                'description' => "The tabs across the Rooms page are how a guest narrows the list. Name them the way your hotel sells rooms, put them in the order you want them read, and make sure the tab a room sits under is the one a guest would look for it in.",
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Set the Room Booking Popup',
                'description' => "The popup that opens from a room card is where a guest decides. Check what it shows - the photograph, the price, what the room includes, the availability calendar - and set its colour so it reads as part of your site rather than a dialog on top of it.",
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Check the Rooms Page on a Phone',
                'description' => "Most guests will read this page on a phone. Narrow the window until the cards stack and look for what breaks: a name that wraps badly, a price that leaves the card, a photograph cropped to nothing.",
                'priority' => 'low',
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
                'title' => 'Write the Restaurant Page Introduction',
                'description' => "Write the words above the menu: the small line over the heading, the heading, and the paragraph under it. Say what kind of kitchen this is - the food, the room, the hours a guest can eat - rather than repeating the word Menu.",
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Write Every Dish Description',
                'description' => "A price and a photograph are not enough to choose by. Give each dish a line that says what it actually is - what is in it, how it is cooked, how big it is - in the voice the rest of the site is written in.",
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Make the Dish Photographs Consistent',
                'description' => "A menu photographed six different ways reads as six different restaurants. Frame the dishes the same way - the same distance, the same light, the same plate if you can - and replace the ones that do not match.",
                'priority' => 'low',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Check the Restaurant Page on a Phone',
                'description' => "Narrow the window until the dish cards stack and read the page as a guest with a phone would: names that wrap, prices that leave the card, tabs that no longer fit on one line.",
                'priority' => 'low',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Review the Restaurant Page Against the Site',
                'description' => "Open the Home page and the Restaurant page one after the other. They should look like the same hotel: the same typeface, the same kind of photograph, prices written the same way, headings in the same voice.",
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
                'title' => 'Write the Amenities Page Introduction',
                'description' => "Write the words above the facility cards: the small line over the heading, the heading, and the paragraph under it. Say what a guest can actually use and how they get at it, rather than repeating the word Amenities.",
                'priority' => 'medium',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Set the Amenity Photo Pace',
                'description' => "Each amenity card cycles through its photographs on its own. Set how long a card holds each one - three, four or five seconds - from the control beside the cards in Design mode, and check the result on a card with several pictures.",
                'priority' => 'low',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Check Your Pages on a Phone',
                'description' => "Narrow the window until the cards stack and read both your pages as a guest with a phone would: headings that wrap badly, photographs cropped to nothing, a carousel that no longer fits.",
                'priority' => 'low',
                'scope' => self::SCOPE_SITE,
            ],
            [
                'title' => 'Review Your Pages Against the Site',
                'description' => "Open the Home page, then Amenities, then Experience. All three should look like the same hotel: the same typeface, the same kind of photograph, headings written in the same voice.",
                'priority' => 'low',
                'scope' => self::SCOPE_SITE,
            ],
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


    /**
     * What "finished" means for a website task, in one sentence, keyed by title.
     *
     * Written for the design work because that is what a faculty review opens the
     * site to check; the staff-tool tasks are judged by the rows they leave in the
     * department's screens and carry none. A task with no requirement listed is
     * shown without one rather than with an empty heading.
     *
     * @var array<string, string>
     */
    private const COMPLETION = [
        HotelConceptDesk::TASK_TITLE => HotelConceptDesk::TASK_COMPLETION,
        'Brand Your Hotel' => 'Your own logo and hotel name appear in the header, the footer and the mobile menu on every page.',
        'Design the Home Page' => 'The photographs, the headline, the introduction and the menu links are all your own.',
        'Write Your Hotel\'s Story' => 'The tagline, the introduction and the whole contact block are filled in and correct.',
        'Choose the Site\'s Colours' => 'The site carries your palette and every page\'s text still reads against it.',
        'Set the Site\'s Typography' => 'One typeface, one body size and your own text and heading colours across the site.',
        'Add Your Social Profiles' => 'Every icon in the footer belongs to an account the hotel actually keeps.',
        'Fill In the Promos Section' => 'Three offers, each with its picture, its name, what it includes and its condition.',
        'Build the Partner Brands Strip' => 'Every card is a real partner, with a logo or its name, and no sample brands remain.',
        'Introduce Your Team' => 'Four people, each with a photograph, a full name and the position they hold.',
        'Create Your Room Categories' => 'The categories are the ones your hotel sells, each with an opening rate.',
        'Build Your Room Types' => 'Every room type the hotel has is on the page, under the right category.',
        'Photograph and Price Every Room' => 'Every room card carries its own photograph, its price and its own description.',
        'Style the Rooms Page' => 'The section, the cards and the booking popup all sit in your palette.',
        'List What Each Room Includes' => 'Every room card names what is in the room, with icons that match.',
        'Build Your Menu' => 'Every dish the restaurant serves is on the page and no sample dish remains.',
        'Photograph and Price the Menu' => 'Every dish carries its own photograph and its price.',
        'Organise the Menu into Categories' => 'Every dish sits in a named section, and the tabs read as your restaurant\'s.',
        'Style the Menu Cards' => 'The dish cards and the section behind them sit in your palette.',
        'Customize Hotel Amenities' => 'Every facility the hotel has is listed, described, and photographed more than once.',
        'Build the Amenities Page' => 'The page lists every facility a guest can use, in your own words.',
        'Write the Experience Page' => 'The page describes your hotel\'s stay, with no sample copy left on it.',
        'Colour the Amenities and Experience Pages' => 'Both pages carry a background of their own that still belongs to the site.',
        'Illustrate the Experience Page' => 'Every section of the page carries a picture, and none of them are the samples.',
        'Write the Rooms Page Introduction' => 'The heading and its introduction are your own words, with no sample copy left on the page.',
        'Name the Category Tabs' => 'Every tab is named in your own words and every room is filed under the right one.',
        'Set the Room Booking Popup' => 'The popup shows your own content and its colour belongs to your palette.',
        'Check the Rooms Page on a Phone' => 'The page reads cleanly in one column with nothing overflowing or cropped away.',
        'Write the Restaurant Page Introduction' => 'The heading and its introduction are your own words, with no sample copy left on the page.',
        'Write Every Dish Description' => 'Every dish on the menu has its own description and none of the sample lines remain.',
        'Make the Dish Photographs Consistent' => 'The menu reads as one set of photographs rather than a collection.',
        'Check the Restaurant Page on a Phone' => 'The page reads cleanly in one column with the tabs and every card intact.',
        'Review the Restaurant Page Against the Site' => 'The two pages read as one hotel, with any remaining difference a deliberate one.',
        'Write the Amenities Page Introduction' => 'The heading and its introduction are your own words, with no sample copy left on the page.',
        'Set the Amenity Photo Pace' => 'The pace is set for the team and a card with several photographs reads comfortably at it.',
        'Check Your Pages on a Phone' => 'Both pages read cleanly in one column with the carousel and every card intact.',
        'Review Your Pages Against the Site' => 'Your two pages sit with the rest of the site rather than beside it.',
    ];

    /**
     * The four steps one activity is worked through as, keyed by its title.
     *
     * An activity is one role's share of a task — Task 01 holds four of them, one
     * per role that owns part of the site — and these are the steps the student
     * holding it works through. Four steps of one activity, never four activities
     * for one role.
     *
     * Kept beside the tasks rather than inside them so the entries above stay
     * readable as a list of work, and appended to the description on the way out
     * (see withActivities) rather than stored twice: a task row is a copy of a
     * description made at assignment time, so anything a student has to read
     * while doing the work has to be in that text.
     *
     * @var array<string, list<string>>
     */
    private const ACTIVITIES = [
        HotelConceptDesk::TASK_TITLE => HotelConceptDesk::TASK_ACTIVITIES,
        'Write the Rooms Page Introduction' => [
            'Rewrite the eyebrow line over the heading.',
            'Rewrite the heading in your hotel\'s own words.',
            'Write the paragraph under it in two or three sentences.',
            'Read it back against the Home page so the two sound like one hotel.',
        ],
        'Name the Category Tabs' => [
            'Read the tabs as a guest would and note any that do not fit.',
            'Rename each tab to the wording your hotel uses.',
            'Check every room sits under the tab a guest would look in.',
            'Confirm the first tab is the one you want opened by default.',
        ],
        'Set the Room Booking Popup' => [
            'Open a room card and read the popup as a guest would.',
            'Fix anything in it that still reads as sample content.',
            'Set the popup\'s colour from Background Colours.',
            'Check it against a second room before you finish.',
        ],
        'Check the Rooms Page on a Phone' => [
            'Narrow the browser until the room cards stack in one column.',
            'Read every card for text that wraps or overflows.',
            'Open the booking popup at that width and check it fits.',
            'Fix what breaks and check again.',
        ],
        'Write the Restaurant Page Introduction' => [
            'Rewrite the eyebrow line over the heading.',
            'Rewrite the heading in your restaurant\'s own words.',
            'Write the paragraph under it in two or three sentences.',
            'Read it against the Home page so the two sound like one hotel.',
        ],
        'Write Every Dish Description' => [
            'Read every dish card for a description still carrying sample text.',
            'Write what each dish is, in one line.',
            'Keep the lines a similar length so the cards sit evenly.',
            'Read three of them together and cut any word doing no work.',
        ],
        'Make the Dish Photographs Consistent' => [
            'Look at the menu as a grid and pick out the photographs that do not fit.',
            'Replace them with pictures framed like the rest.',
            'Check every card fills its picture area without stretching.',
            'Look at the dining preview on the Home page as well.',
        ],
        'Check the Restaurant Page on a Phone' => [
            'Narrow the browser until the dish cards stack in one column.',
            'Read every card for text that wraps or overflows.',
            'Check the category tabs still work at that width.',
            'Fix what breaks and check again.',
        ],
        'Review the Restaurant Page Against the Site' => [
            'Open the Home page, then the Restaurant page, and compare them.',
            'Note every difference that is not deliberate.',
            'Fix the ones that make the pages look unrelated.',
            'Check the dining section on Home matches the page it previews.',
        ],
        'Write the Amenities Page Introduction' => [
            'Rewrite the eyebrow line over the heading.',
            'Rewrite the heading in your hotel\'s own words.',
            'Write the paragraph under it in two or three sentences.',
            'Read it against the Home page so the two sound like one hotel.',
        ],
        'Set the Amenity Photo Pace' => [
            'Open the Amenities page in Design mode.',
            'Set the pace from the control above the cards.',
            'Watch a card with several photographs cycle right through.',
            'Open View Details and check the carousel reads at that pace too.',
        ],
        'Check Your Pages on a Phone' => [
            'Narrow the browser until the amenity cards stack in one column.',
            'Read every card for text that wraps or overflows.',
            'Open View Details at that width and check the carousel fits.',
            'Do the same for the Experience page.',
        ],
        'Review Your Pages Against the Site' => [
            'Open the Home page and then each of your two pages in turn.',
            'Note every difference that is not deliberate.',
            'Fix the ones that make the pages look unrelated.',
            'Read all three headings aloud and check they sound like one writer.',
        ],
        'Brand Your Hotel' => [
            'Upload your own logo in place of the default mark in the header.',
            'Rename the hotel: replace the placeholder name with your team\'s.',
            'Check the footer and the mobile menu carry the same logo and name.',
            'Open View Live and confirm the branding is right on every page.',
        ],
        'Design the Home Page' => [
            'Replace the five photographs that rotate across the top of the page.',
            'Rewrite the headline and the line above it so they name your hotel.',
            'Rewrite the introduction under the headline in your own words.',
            'Rename the links in the top menu to the wording your hotel would use.',
        ],
        'Write Your Hotel\'s Story' => [
            'Write the tagline that sits above the headline on the landing page.',
            'Write the paragraph introducing the hotel, in two or three sentences.',
            'Fill in the contact block: address, phone number, email and hours.',
            'Check the footer on another page shows the same details.',
        ],
        'Choose the Site\'s Colours' => [
            'Open Background Colours from the toolbar in Design mode.',
            'Set the main website colour and watch the cards and text follow it.',
            'Set the header and the footer if they should stand apart from it.',
            'Read a paragraph on each page to confirm the text still has contrast.',
        ],
        'Set the Site\'s Typography' => [
            'Choose the typeface the whole site is set in.',
            'Set the size body text is read at.',
            'Set the colour of ordinary text and the colour of headings.',
            'Check a long paragraph and a heading on two different pages.',
        ],
        'Add Your Social Profiles' => [
            'List the networks the hotel actually keeps an account on.',
            'Add each one with its address in the footer editor.',
            'Leave out the networks you do not use rather than adding dead icons.',
            'Open the footer and click each icon to confirm it goes where it should.',
        ],
        'Fill In the Promos Section' => [
            'Upload a photograph for each of the three promo cards.',
            'Name each offer and write what it includes in a sentence or two.',
            'Set the saving or benefit each one carries.',
            'Write the condition under each offer - the dates or the minimum stay.',
        ],
        'Build the Partner Brands Strip' => [
            'Decide which businesses the hotel works with.',
            'Add a card per brand with Add Brand and name it.',
            'Upload each brand\'s logo so it fills its card.',
            'Remove the sample brands your hotel is not partnered with.',
        ],
        'Introduce Your Team' => [
            'Agree which four people the section will show.',
            'Upload a photograph for each of them.',
            'Write each name in full.',
            'Give each the position they actually hold on your team.',
        ],
        'Check Room Availability' => [
            'Open the Rooms page as a guest would see it.',
            'Read the availability calendar on a room card.',
            'Pick out the dates that room is already booked.',
            'Name a room that is free for the dates the guest asked about.',
        ],
        'Register a Guest' => [
            'Press Reserve Now on a room that is free for those dates.',
            'Fill in the guest\'s full name, contact number and email.',
            'Record the government or passport ID you verified.',
            'Set the check-in and check-out dates and times.',
        ],
        'Add Add-ons to a Reservation' => [
            'Open the Add-ons expander while registering the guest.',
            'Read what Housekeeping\'s catalogue has in stock.',
            'Add what the guest asked for and set how many.',
            'Confirm the total on the reservation includes them.',
        ],
        'Process the Reservation Payment' => [
            'Open the Process Payment step of the reservation.',
            'Choose full or partial payment and enter the amount.',
            'Record the method, the payer name and a reference.',
            'Press Complete Reservation to actually book the room.',
        ],
        'Mark a Guest as Arrived' => [
            'Find the booking in Guest Information.',
            'Check the dates and the ID against the guest in front of you.',
            'Press Arrive.',
            'Tell Room Management the guest is ready to be checked in.',
        ],
        'Reserve a Dine-in Table' => [
            'Open Dine-in Tables and find one that seats the party.',
            'Enter the customer\'s name and contact number.',
            'Set the date and time they are coming.',
            'Save the hold and confirm the table now reads as reserved.',
        ],
        'Seat a Reserved Table' => [
            'Find the reserved table when the customer arrives.',
            'Check the booking name against who is standing there.',
            'Press Customer Arrived.',
            'Confirm the table is seated so the kitchen can take an order.',
        ],
        'Take a Room Service Order' => [
            'Find the checked-in room the order is for.',
            'Add the dishes from the menu with their quantities.',
            'Write any note the kitchen needs about the order.',
            'Place the order and confirm it reaches the restaurant queue.',
        ],
        'File a Guest Complaint' => [
            'Take down what the guest is unhappy about, in their words.',
            'Choose the category that routes it to the right department.',
            'Attach the room or facility it concerns.',
            'File it and tell the guest who is picking it up.',
        ],
        'Follow Up on a Resolved Complaint' => [
            'Open a complaint the department has marked resolved.',
            'Read what they recorded as the fix.',
            'Check with the guest that it is actually settled.',
            'Close it, or send it back with what is still wrong.',
        ],
        'Check a Guest Out' => [
            'Find the stay in Guest Information.',
            'Confirm the room and the dates against the booking.',
            'Press Check Out.',
            'Confirm the room has gone to Housekeeping for inspection.',
        ],
        'Add an Extra Charge to the Final Bill' => [
            'Open the bill for the stay you are settling.',
            'Add the charge with a description of what it is for.',
            'Enter the amount and check it against what was used.',
            'Confirm the new total is what you will ask the guest for.',
        ],
        'Settle the Final Bill' => [
            'Read the bill through with the guest.',
            'Take the payment and record the method and reference.',
            'Mark the bill settled.',
            'Confirm the stay now reads as closed.',
        ],
        'Review the Revenue Reports' => [
            'Open Reports and set the period you are looking at.',
            'Read what rooms took against what the restaurant took.',
            'Pick out the busiest day in the period.',
            'Say in a sentence what the numbers tell you about the week.',
        ],
        'Create Your Room Categories' => [
            'Decide the categories your hotel sells rooms in.',
            'Add each one with the name a guest would recognise.',
            'Set the opening rate a new room of that category starts from.',
            'Remove any sample category your hotel does not offer.',
        ],
        'Build Your Room Types' => [
            'Add a room card for each type the hotel has.',
            'Put every card under the category it belongs to.',
            'Name each one the way it appears on the door.',
            'Check the Rooms page reads as your hotel rather than the sample.',
        ],
        'Photograph and Price Every Room' => [
            'Upload a photograph of each room type.',
            'Set the price for the stay length the site quotes.',
            'Write a description of the room in your own words.',
            'Compare two cards side by side and make them consistent.',
        ],
        'Style the Rooms Page' => [
            'Set the background colour behind the room cards.',
            'Set the colour of the cards themselves.',
            'Set the colour of the booking popup that opens from a card.',
            'Check the room preview on the Home page still looks right.',
        ],
        'List What Each Room Includes' => [
            'Decide the four things worth naming on each room.',
            'Set the amenity chips on every card to match them.',
            'Choose icons that fit what is actually in the room.',
            'Read two cards together and confirm a guest could choose between them.',
        ],
        'Add a Room to the Inventory' => [
            'Open Manage Rooms in the department tools.',
            'Add the room with its number and its category.',
            'Set its floor and its starting status.',
            'Confirm it appears on the room board.',
        ],
        'Update a Room\'s Details' => [
            'Find the room on the board.',
            'Change what is wrong - the category, the floor, the rate.',
            'Save and read the row back.',
            'Check the site\'s Rooms page shows the change.',
        ],
        'Check a Guest In' => [
            'Find the guest Front Desk has marked arrived.',
            'Confirm the room is clean and available.',
            'Check them in against that room.',
            'Confirm the room now reads as occupied.',
        ],
        'Monitor Occupancy' => [
            'Open the room board and read the whole strip.',
            'Count what is occupied against what is free.',
            'Pick out the rooms held by a reservation that has not arrived.',
            'Say which rooms you could still sell today.',
        ],
        'Update a Room\'s Status' => [
            'Find the room whose state has changed.',
            'Set the status that describes it now.',
            'Write the reason where the screen asks for one.',
            'Confirm the desk can see the new state.',
        ],
        'Release a Room After Maintenance' => [
            'Find the room Maintenance has finished with.',
            'Read what they recorded as the repair.',
            'Check the room is fit to sell.',
            'Put it back to available.',
        ],
        'Build Your Menu' => [
            'Decide what your restaurant serves.',
            'Add a card for each dish with its name.',
            'Write a short line under each saying what it is.',
            'Remove the sample dishes you are not serving.',
        ],
        'Photograph and Price the Menu' => [
            'Upload a photograph for each dish.',
            'Set the price of every one.',
            'Rewrite any description that no longer matches the picture.',
            'Check the dining preview on the Home page reads well.',
        ],
        'Organise the Menu into Categories' => [
            'Decide the sections a diner reads your menu by.',
            'Name each section the way your restaurant would.',
            'Move every dish into the section it belongs to.',
            'Check the tabs and the Home page preview both follow them.',
        ],
        'Style the Menu Cards' => [
            'Set the colour of the dish cards.',
            'Set the background of the Restaurant section behind them.',
            'Check the cards still read against their new background.',
            'Look at the dining preview on the Home page before you finish.',
        ],
        'Add Dishes to the Menu' => [
            'Open the menu in the department tools.',
            'Add the dish with its name, price and category.',
            'Say how many portions the kitchen is holding.',
            'Confirm it appears on the site\'s Restaurant page.',
        ],
        'Keep Menu Stock Current' => [
            'Read the menu for anything the kitchen has run out of.',
            'Set what is finished to unavailable.',
            'Put back what has been restocked.',
            'Check the site is not offering a dish you cannot cook.',
        ],
        'Set Up Your Dining Tables' => [
            'Add each table the restaurant has.',
            'Set how many the table seats.',
            'Number them the way the floor is laid out.',
            'Confirm Front Desk can hold one of them.',
        ],
        'Take a Dine-In Order' => [
            'Open a table that has been seated.',
            'Add what the customers ordered, with quantities.',
            'Note anything the kitchen needs to know.',
            'Send the order and confirm it lands in the queue.',
        ],
        'Move an Order Through the Kitchen' => [
            'Pick up an order the kitchen has received.',
            'Mark it preparing when the kitchen starts.',
            'Mark it ready when it goes on the pass.',
            'Mark it served once it reaches the table.',
        ],
        'Fulfil a Room Service Order' => [
            'Find the room service order Front Desk placed.',
            'Cook it in the same queue as the dine-in work.',
            'Mark it ready for delivery.',
            'Close it once it has gone to the room.',
        ],
        'Cancel a Dine-In Order' => [
            'Find the order that has to be cancelled.',
            'Record why it is being cancelled.',
            'Cancel it before the kitchen commits to it.',
            'Confirm the bill for that table no longer carries it.',
        ],
        'Bill and Close a Dine-In Table' => [
            'Read the order back to the customers at the table.',
            'Produce the bill for what they had.',
            'Take the payment and record it.',
            'Close the table so it can be seated again.',
        ],
        'Customize Hotel Amenities' => [
            'Add the facilities your hotel actually has, and remove the ones it does not.',
            'Give each its location, opening hours, availability and description.',
            'Upload several photographs of every facility.',
            'Open View Details on a card and check the carousel reads well.',
        ],
        'Build the Amenities Page' => [
            'Read the page against the add-ons you lend out.',
            'Write the heading and the introduction in your own words.',
            'Make sure every facility a guest can use is listed.',
            'Check the page against the rest of the site before you finish.',
        ],
        'Write the Experience Page' => [
            'Decide what staying at your hotel is actually like.',
            'Write each section of the page in your own words.',
            'Cut the sample copy that does not describe your hotel.',
            'Lay the page out so it matches the rest of the site.',
        ],
        'Colour the Amenities and Experience Pages' => [
            'Open Background Colours in Design mode.',
            'Set the background of the Amenities page.',
            'Set the background of the Experience page.',
            'Check both still look like the same hotel as the Home page.',
        ],
        'Illustrate the Experience Page' => [
            'List the things the page describes.',
            'Find or take a photograph for each of them.',
            'Replace every sample image on the page.',
            'Read the page through and cut any picture that adds nothing.',
        ],
        'Stock the Add-ons Catalogue' => [
            'List what a guest can ask to borrow.',
            'Add each item with a short description.',
            'Set how many of each you hold.',
            'Confirm Front Desk can lend one while registering a guest.',
        ],
        'Watch the Room Board' => [
            'Open Room Inspections and read the strip across the top.',
            'Say which rooms are ready to sell.',
            'Say which are waiting on cleaning.',
            'Say which are out with Maintenance.',
        ],
        'Start a Room Inspection' => [
            'Find a room raised for inspection after a checkout.',
            'Press Start inspection to pick it up.',
            'Walk the room.',
            'Confirm it now reads as being inspected.',
        ],
        'Record What You Found' => [
            'Choose what the room needs from the list.',
            'Write a note describing what you saw.',
            'Be specific about anything damaged or missing.',
            'Save it so the next person reads the same thing.',
        ],
        'Report an Issue to Maintenance' => [
            'Choose the category the fault belongs to.',
            'Describe the fault in enough detail to act on.',
            'Send it, and watch the room go out to maintenance.',
            'Confirm the inspection is waiting on the repair.',
        ],
        'Re-inspect After a Repair' => [
            'Pick the room back up when Maintenance closes the repair.',
            'Check the work they recorded was actually done.',
            'Walk the rest of the room again.',
            'Either report another issue or carry on to finish it.',
        ],
        'Complete an Inspection' => [
            'Clean the room.',
            'Replace the linens, the towels and the amenities.',
            'Mark the inspection completed.',
            'Confirm the room is back to available.',
        ],
        'Work a Housekeeping Complaint' => [
            'Take a complaint that belongs to housekeeping.',
            'Move it to In Progress when you start.',
            'Write what you did in the note.',
            'Resolve it, or hand it to Maintenance if it is a repair.',
        ],
        'Receive a Maintenance Request' => [
            'Open the maintenance queue.',
            'Read the request and what the reporter described.',
            'Check which room or facility it concerns.',
            'Acknowledge it so the reporter knows it was seen.',
        ],
        'Prioritise the Queue' => [
            'Read every open request.',
            'Judge which ones stop a room being sold.',
            'Order the queue by what has to be fixed first.',
            'Say why the top one is at the top.',
        ],
        'Start a Repair' => [
            'Pick up the request at the top of the queue.',
            'Mark it In Progress so nobody duplicates the work.',
            'Confirm the room or facility is out of use while you work.',
            'Do the repair.',
        ],
        'Record the Repair' => [
            'Write what was actually wrong.',
            'Write what you did about it.',
            'Note anything that will need doing again.',
            'Save it against the request.',
        ],
        'Close a Repair' => [
            'Check the work is finished.',
            'Close the request.',
            'Confirm the room comes back for a final inspection.',
            'Confirm the reporter can see it is done.',
        ],
        'Hand a Request to Housekeeping' => [
            'Read a request that turns out not to be a repair.',
            'Say why it belongs to housekeeping.',
            'Hand it over.',
            'Confirm it has left your queue and reached theirs.',
        ],
    ];

    /**
     * A task with its activities written into the description, as
     * "Activities:" and four numbered lines. Every reader of the checklist goes
     * through here, so the Create Task tab, the row it saves and the student's
     * copy all carry the same four steps.
     *
     * A task with no activities listed is returned untouched rather than given an
     * empty heading.
     */
    private static function withActivities(array $task): array
    {
        $steps = self::ACTIVITIES[$task['title'] ?? ''] ?? [];

        if ($steps === []) {
            $task['activities'] = [];
            $task['completion'] = self::COMPLETION[$task['title'] ?? ''] ?? null;
            $task['summary'] = rtrim($task['description'] ?? '');

            return $task;
        }

        $lines = [];
        foreach ($steps as $i => $step) {
            $lines[] = ($i + 1) . '. ' . $step;
        }

        // Also handed back as a list, for any screen that would rather render the
        // steps than print them.
        $task['activities'] = $steps;
        $task['completion'] = self::COMPLETION[$task['title'] ?? ''] ?? null;
        // The description as written, before the activities were appended: a
        // screen rendering the four as their own list wants the paragraph on its
        // own, not the paragraph with the list printed under it twice.
        $task['summary'] = rtrim($task['description'] ?? '');
        $task['description'] = rtrim($task['description'] ?? '')
            . "\n\nSteps:\n"
            . implode("\n", $lines);

        return $task;
    }

    /**
     * Whether a task by this title is website work.
     *
     * Asked of a saved task row, which keeps no scope of its own - it is a copy of
     * a checklist entry made at assignment time. Anything not on the checklist
     * (the hotel concept, the chained follow-up) answers site, which is what both
     * of those are.
     */
    public static function isSiteTitle(string $title): bool
    {
        foreach (self::TASKS as $tasks) {
            foreach ($tasks as $task) {
                if (strcasecmp($task['title'], $title) === 0) {
                    return ($task['scope'] ?? self::SCOPE_SITE) === self::SCOPE_SITE;
                }
            }
        }

        return true;
    }

    /** What finishing one task means, or null when none is written for it. */
    public static function completionFor(string $title): ?string
    {
        return self::COMPLETION[$title] ?? null;
    }

    /** The activities for one task title, or an empty list when it has none. */
    public static function activitiesFor(string $title): array
    {
        return self::ACTIVITIES[$title] ?? [];
    }

    public static function all(): array
    {
        $out = [];

        foreach (array_keys(HotelTemplateBuilder::ROLES) as $role) {
            $out[$role] = array_map(
                fn (array $task) => self::withActivities($task),
                self::TASKS[$role] ?? []
            );
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
        return array_map(
            fn (array $task) => self::withActivities($task),
            self::TASKS[$role] ?? []
        );
    }

    /** Short label for the scope badge on each checklist card. */
    public static function scopeLabel(string $scope): string
    {
        return $scope === self::SCOPE_OPS ? 'Staff tools' : 'Website';
    }
}
