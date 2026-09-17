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
 * Tasks are website customization and nothing else. Running the hotel — check-in,
 * orders, inspections, the maintenance queue — happens in the Simulation area and
 * is not handed out from here. Rows saved under the old simulation titles are
 * still recognised through LEGACY_OPS_TITLES so they can be kept off the Task
 * panel rather than showing up as website work.
 *
 * One numbered task per department, in STEP_ROLES order:
 *
 *   TASK 01  Front Desk Customization        — the hotel concept, then the home
 *                                              page and the chrome every page
 *                                              inherits (logo, name, palette,
 *                                              type, footer), plus the Experience
 *                                              page
 *   TASK 02  Room Management Customization   — the Rooms page
 *   TASK 03  Restaurant Management Customization — the Restaurant page
 *   TASK 04  Housekeeping Customization      — the Amenities page
 *
 * Each entry under a task is one activity: its own row, submitted and reviewed on
 * its own. Maintenance owns no page of the site (HotelTemplateBuilder::
 * ROLE_EDITABLE_PAGES), so it has no task here.
 *
 * The approved hotel concept is the brief for all of it. Nothing but the concept
 * itself can be assigned before faculty approves one of the two proposals —
 * FacultyController::storeTask refuses it, and the tasks.complete route refuses
 * to take a website task in before then.
 *
 * The site logo and the hotel name are single site-wide values — any owning role
 * writes the same key and claimSharedLogo() hands it over — so they are assigned
 * to Front Desk alone rather than repeated per role, which read as three separate
 * logos to change.
 */
class TaskChecklist
{
    /**
     * The department each numbered task belongs to, in task order. Position 0 is
     * TASK 01. Front Desk leads because its first activity is the hotel concept
     * that every other task is built on.
     */
    public const STEP_ROLES = [
        'front_desk',
        'room_management',
        'restaurant_management',
        'housekeeping',
    ];

    /** The name each numbered task is shown under. */
    public const STEP_LABELS = [
        'front_desk' => 'Front Desk Customization',
        'room_management' => 'Room Management Customization',
        'restaurant_management' => 'Restaurant Management Customization',
        'housekeeping' => 'Housekeeping Customization',
    ];

    /**
     * Titles of the simulation tasks this checklist used to hand out, lowercased.
     *
     * They are not assignable any more — that work lives in the Simulation area —
     * but a task row keeps only a copy of the title it was assigned under, so rows
     * made before the change still carry these. Listed so those rows can be told
     * apart from website work and kept off the student's Task panel.
     */
    public const LEGACY_OPS_TITLES = [
        // Front Desk
        'check room availability',
        'register a guest',
        'add add-ons to a reservation',
        'process the reservation payment',
        'mark a guest as arrived',
        'reserve a dine-in table',
        'seat a reserved table',
        'take a room service order',
        'file a guest complaint',
        'follow up on a resolved complaint',
        'check a guest out',
        'add an extra charge to the final bill',
        'settle the final bill',
        'review the revenue reports',
        // Room Management
        'add a room to the inventory',
        "update a room's details",
        'check a guest in',
        'monitor occupancy',
        "update a room's status",
        'release a room after maintenance',
        // Restaurant
        'add dishes to the menu',
        'keep menu stock current',
        'set up your dining tables',
        'take a dine-in order',
        'move an order through the kitchen',
        'fulfil a room service order',
        'cancel a dine-in order',
        'bill and close a dine-in table',
        // Housekeeping
        'stock the add-ons catalogue',
        'watch the room board',
        'start a room inspection',
        'record what you found',
        'report an issue to maintenance',
        're-inspect after a repair',
        'complete an inspection',
        'work a housekeeping complaint',
        // Maintenance
        'receive a maintenance request',
        'prioritise the queue',
        'start a repair',
        'record the repair',
        'close a repair',
        'hand a request to housekeeping',
    ];

    /**
     * Keyed by role, in the order they should appear under each department.
     *
     * @var array<string, list<array{title: string, description: string}>>
     */
    private const TASKS = [
        'front_desk' => [
            [
                // TASK 01's first activity. The row itself is seeded by
                // HotelConceptDesk the moment a student holds the role, so
                // assigning it claims that row rather than writing a second
                // one — see FacultyController::storeTask.
                'title' => HotelConceptDesk::TASK_TITLE,
                'description' => HotelConceptDesk::TASK_DESCRIPTION,
            ],
            [
                'title' => 'Brand Your Hotel',
                'description' => "Give the site your hotel's identity: replace the default logo with your own, and the placeholder name in the header with your team's hotel name. Both are single site-wide values — the header, the footer and every page read them.",
            ],
            [
                'title' => 'Design the Home Page',
                'description' => 'Customise the page a guest lands on: pick the five photographs that rotate across the top, rewrite the headline and the introduction under them so they describe your hotel rather than the sample text, and rename the links in the top menu so they read the way your hotel would label them.',
            ],
            [
                'title' => 'Write Your Hotel\'s Story',
                'description' => "Replace the sample words with your own: the tagline over the headline, the paragraph introducing the hotel, and the contact block every footer prints - address, phone, email and the hours the desk keeps. These are one record for the whole site, so what you write here is what the Rooms page and the Restaurant page say too.",
            ],
            [
                'title' => 'Choose the Site\'s Colours',
                'description' => "Open Background Colours from the toolbar and give the site its own palette. Start with the main website colour - the text, cards and borders follow it automatically - then set the header and the footer if they should stand apart from it. Pick a background your text still reads against.",
            ],
            [
                'title' => 'Set the Site\'s Typography',
                'description' => "Choose the typeface the whole site is set in, the size body text is read at, and the colours for ordinary text and for headings. Type is site-wide: it reaches pages your role cannot otherwise edit, so choose something legible at a paragraph's length, not only in a heading.",
            ],
            [
                'title' => 'Add Your Social Profiles',
                'description' => "Put the hotel's social accounts in the footer. Add only the networks the hotel actually uses - each one shows as its own icon, and an account nobody keeps is worse than a missing one.",
            ],
            [
                'title' => 'Fill In the Promos Section',
                'description' => "Write the offers the hotel is running on the Home page: a picture for each, the name of the offer, what it includes in a sentence or two, the saving it carries, and the condition it comes with. Three cards is what the section is built for.",
            ],
            [
                'title' => 'Build the Partner Brands Strip',
                'description' => "List the businesses the hotel works with - the travel agency, the cafe, the airline. Add a card per brand and upload each one's logo; a brand with no logo yet shows its name instead, so the strip is never half empty. Remove the sample brands you are not using.",
            ],
            [
                'title' => 'Introduce Your Team',
                'description' => "Put four real people in Our Team: a photograph of each, their full name, and the position they hold. This is the section a guest reads to see who runs the hotel, so use the roles your team actually assigned.",
            ],
            [
                'title' => 'Write the Experience Page',
                'description' => 'Write the Experience page so it tells a guest what staying at your hotel is like, and lay it out so it matches the rest of the site.',
            ],
            [
                'title' => 'Colour the Experience Page',
                'description' => "Give the Experience page its own background from Background Colours. It's the page guests read after the rooms, so it should look like the same hotel - close enough to the site's palette to belong to it, different enough to be its own.",
            ],
            [
                'title' => 'Illustrate the Experience Page',
                'description' => "Photograph the Experience page: a picture for each thing you describe, so the page shows the stay rather than only claiming it. Replace every sample image; a page of stock photographs reads as a page nobody wrote.",
            ],
        ],

        'room_management' => [
            [
                // The step DesignTaskChain watches: approving it hands the team
                // "Detail Every Room Category", which is not on this list because
                // it cannot be done before the categories are agreed.
                'title' => 'Create Your Room Categories',
                'description' => 'Decide the room categories your hotel sells and set them up on the Rooms page — add the ones you need with the + tab, rename the starting ones that do not fit, and drop the sample wording. Get the names right first; you describe and price them once your faculty approves this.',
            ],
            [
                'title' => 'Build Your Room Types',
                'description' => 'Replace the sample rooms with the room types your hotel actually offers. Give each one its own name and describe what it includes, so a guest can tell them apart without asking.',
            ],
            [
                'title' => 'Photograph and Price Every Room',
                'description' => 'Give every room type its own picture — none should be left on the placeholder image — set a nightly rate you can explain, and lay the Rooms page out so it matches the rest of the site.',
            ],
            [
                'title' => 'Style the Rooms Page',
                'description' => "Give the Rooms page its own look: the colour behind the room cards, the colour of the cards themselves, and the colour of the booking popup that opens when a guest picks one. The card colour is shared with the preview on the Home page, so it sets the tone in both places at once.",
            ],
            [
                'title' => 'List What Each Room Includes',
                'description' => "Set the amenity chips under every room - the bed, the view, the bath, the wifi - so a guest can compare two rooms without opening either. Use the icons that match what is actually in the room rather than leaving the sample set.",
            ],
            [
                'title' => 'Write the Rooms Page Introduction',
                'description' => "Write the words above the room cards: the small line over the heading, the heading itself, and the paragraph under it. They are what a guest reads before any price, so say what kind of rooms these are rather than repeating the word Rooms.",
            ],
            [
                'title' => 'Name the Category Tabs',
                'description' => "The tabs across the Rooms page are how a guest narrows the list. Name them the way your hotel sells rooms, put them in the order you want them read, and make sure the tab a room sits under is the one a guest would look for it in.",
            ],
            [
                'title' => 'Set the Room Booking Popup',
                'description' => "The popup that opens from a room card is where a guest decides. Check what it shows - the photograph, the price, what the room includes, the availability calendar - and set its colour so it reads as part of your site rather than a dialog on top of it.",
            ],
            [
                'title' => 'Check the Rooms Page on a Phone',
                'description' => "Most guests will read this page on a phone. Narrow the window until the cards stack and look for what breaks: a name that wraps badly, a price that leaves the card, a photograph cropped to nothing.",
            ],
        ],

        'restaurant_management' => [
            [
                'title' => 'Build Your Menu',
                'description' => 'Replace the sample dishes with your own menu, grouped so a guest can find what they want.',
            ],
            [
                'title' => 'Photograph and Price the Menu',
                'description' => 'Add a picture to every dish, set a price for every dish, and lay the Restaurant page out so it matches the rest of the site.',
            ],
            [
                'title' => 'Organise the Menu into Categories',
                'description' => "Sort the menu into the sections a diner reads it by - starters, mains, desserts, drinks - and name them the way your restaurant would. The tabs on the Restaurant page and the preview on the Home page both follow these, so a dish in the wrong section is in the wrong section twice.",
            ],
            [
                'title' => 'Style the Menu Cards',
                'description' => "Set the colour of the dish cards and the background of the Restaurant section behind them. The card colour carries to the dining preview on the Home page, so check both before you call it done.",
            ],
            [
                'title' => 'Write the Restaurant Page Introduction',
                'description' => "Write the words above the menu: the small line over the heading, the heading, and the paragraph under it. Say what kind of kitchen this is - the food, the room, the hours a guest can eat - rather than repeating the word Menu.",
            ],
            [
                'title' => 'Write Every Dish Description',
                'description' => "A price and a photograph are not enough to choose by. Give each dish a line that says what it actually is - what is in it, how it is cooked, how big it is - in the voice the rest of the site is written in.",
            ],
            [
                'title' => 'Make the Dish Photographs Consistent',
                'description' => "A menu photographed six different ways reads as six different restaurants. Frame the dishes the same way - the same distance, the same light, the same plate if you can - and replace the ones that do not match.",
            ],
            [
                'title' => 'Check the Restaurant Page on a Phone',
                'description' => "Narrow the window until the dish cards stack and read the page as a guest with a phone would: names that wrap, prices that leave the card, tabs that no longer fit on one line.",
            ],
            [
                'title' => 'Review the Restaurant Page Against the Site',
                'description' => "Open the Home page and the Restaurant page one after the other. They should look like the same hotel: the same typeface, the same kind of photograph, prices written the same way, headings in the same voice.",
            ],
        ],

        'housekeeping' => [
            [
                // The gate on the facilities list: nobody may add, edit,
                // photograph or remove an amenity until this lands on them. See
                // AmenityTaskDesk, which reads a task row back by this title.
                'title' => 'Customize Hotel Amenities',
                'description' => "Add and customize the hotel's amenities displayed in the default template. Give each one its name, where it is, the hours it keeps, whether it is open, and a description a guest can read, then photograph it: the card cycles through every picture you upload and View Details opens them in a carousel.",
            ],
            [
                'title' => 'Build the Amenities Page',
                'description' => 'Fill in the Amenities page with what your hotel actually offers, so it matches the add-ons you lend out.',
            ],
            [
                'title' => 'Colour the Amenities Page',
                'description' => "Give the Amenities page its own background from Background Colours. It's the page guests read after the rooms, so it should look like the same hotel - close enough to the site's palette to belong to it, different enough to be its own.",
            ],
            [
                'title' => 'Write the Amenities Page Introduction',
                'description' => "Write the words above the facility cards: the small line over the heading, the heading, and the paragraph under it. Say what a guest can actually use and how they get at it, rather than repeating the word Amenities.",
            ],
            [
                'title' => 'Check the Amenities Page on a Phone',
                'description' => "Narrow the window until the cards stack and read the page as a guest with a phone would: headings that wrap badly, photographs cropped to nothing, a carousel that no longer fits.",
            ],
            [
                'title' => 'Review the Amenities Page Against the Site',
                'description' => "Open the Home page, then Amenities. They should look like the same hotel: the same typeface, the same kind of photograph, headings written in the same voice.",
            ],
        ],
    ];

    /**
     * What "finished" means for an activity, in one sentence, keyed by title.
     *
     * This is what a faculty review opens the site to check. An activity with no
     * requirement listed is shown without one rather than with an empty heading.
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
        'Colour the Experience Page' => 'The page carries a background of its own that still belongs to the site.',
        'Create Your Room Categories' => 'The categories are the ones your hotel sells, each with an opening rate.',
        'Build Your Room Types' => 'Every room type the hotel has is on the page, under the right category.',
        'Photograph and Price Every Room' => 'Every room card carries its own photograph, its price and its own description.',
        'Style the Rooms Page' => 'The section, the cards and the booking popup all sit in your palette.',
        'List What Each Room Includes' => 'Every room card names what is in the room, with icons that match.',
        'Write the Rooms Page Introduction' => 'The heading and its introduction are your own words, with no sample copy left on the page.',
        'Name the Category Tabs' => 'Every tab is named in your own words and every room is filed under the right one.',
        'Set the Room Booking Popup' => 'The popup shows your own content and its colour belongs to your palette.',
        'Check the Rooms Page on a Phone' => 'The page reads cleanly in one column with nothing overflowing or cropped away.',
        'Build Your Menu' => 'Every dish the restaurant serves is on the page and no sample dish remains.',
        'Photograph and Price the Menu' => 'Every dish carries its own photograph and its price.',
        'Organise the Menu into Categories' => 'Every dish sits in a named section, and the tabs read as your restaurant\'s.',
        'Style the Menu Cards' => 'The dish cards and the section behind them sit in your palette.',
        'Write the Restaurant Page Introduction' => 'The heading and its introduction are your own words, with no sample copy left on the page.',
        'Write Every Dish Description' => 'Every dish on the menu has its own description and none of the sample lines remain.',
        'Make the Dish Photographs Consistent' => 'The menu reads as one set of photographs rather than a collection.',
        'Check the Restaurant Page on a Phone' => 'The page reads cleanly in one column with the tabs and every card intact.',
        'Review the Restaurant Page Against the Site' => 'The two pages read as one hotel, with any remaining difference a deliberate one.',
        'Customize Hotel Amenities' => 'Every facility the hotel has is listed, described, and photographed more than once.',
        'Build the Amenities Page' => 'The page lists every facility a guest can use, in your own words.',
        'Write the Experience Page' => 'The page describes your hotel\'s stay, with no sample copy left on it.',
        'Colour the Amenities Page' => 'The page carries a background of its own that still belongs to the site.',
        'Illustrate the Experience Page' => 'Every section of the page carries a picture, and none of them are the samples.',
        'Write the Amenities Page Introduction' => 'The heading and its introduction are your own words, with no sample copy left on the page.',
        'Check the Amenities Page on a Phone' => 'The page reads cleanly in one column with the carousel and every card intact.',
        'Review the Amenities Page Against the Site' => 'The page sits with the rest of the site rather than beside it.',
    ];

    /**
     * The four steps one activity is worked through as, keyed by its title.
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
        'Colour the Experience Page' => [
            'Open Background Colours in Design mode.',
            'Set the background of the Experience page.',
            'Pick a background your text still reads against.',
            'Check it still looks like the same hotel as the Home page.',
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
        'Colour the Amenities Page' => [
            'Open Background Colours in Design mode.',
            'Set the background of the Amenities page.',
            'Pick a background your text still reads against.',
            'Check it still looks like the same hotel as the Home page.',
        ],
        'Illustrate the Experience Page' => [
            'List the things the page describes.',
            'Find or take a photograph for each of them.',
            'Replace every sample image on the page.',
            'Read the page through and cut any picture that adds nothing.',
        ],
        'Write the Amenities Page Introduction' => [
            'Rewrite the eyebrow line over the heading.',
            'Rewrite the heading in your hotel\'s own words.',
            'Write the paragraph under it in two or three sentences.',
            'Read it against the Home page so the two sound like one hotel.',
        ],
        'Check the Amenities Page on a Phone' => [
            'Narrow the browser until the amenity cards stack in one column.',
            'Read every card for text that wraps or overflows.',
            'Open View Details at that width and check the carousel fits.',
            'Fix what breaks and check again.',
        ],
        'Review the Amenities Page Against the Site' => [
            'Open the Home page, then the Amenities page, and compare them.',
            'Note every difference that is not deliberate.',
            'Fix the ones that make the pages look unrelated.',
            'Read both headings aloud and check they sound like one writer.',
        ],
    ];

    /**
     * A task with its steps written into the description, as "Steps:" and four
     * numbered lines. Every reader of the checklist goes through here, so the
     * Create Task tab, the row it saves and the student's copy all carry the same
     * four steps.
     *
     * A task with no steps listed is returned untouched rather than given an
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
        // The description as written, before the steps were appended: a screen
        // rendering the four as their own list wants the paragraph on its own,
        // not the paragraph with the list printed under it twice.
        $task['summary'] = rtrim($task['description'] ?? '');
        $task['description'] = rtrim($task['description'] ?? '')
            . "\n\nSteps:\n"
            . implode("\n", $lines);

        return $task;
    }

    /**
     * Whether a saved task row is website work.
     *
     * Asked of a row, which keeps no scope of its own - it is a copy of a
     * checklist entry made at assignment time. Everything is website work except
     * the simulation tasks this checklist no longer hands out.
     */
    public static function isSiteTitle(string $title): bool
    {
        return !self::isSimulationTitle($title);
    }

    /** Whether a saved row carries one of the retired simulation task titles. */
    public static function isSimulationTitle(string $title): bool
    {
        return in_array(mb_strtolower(trim($title)), self::LEGACY_OPS_TITLES, true);
    }

    /** What finishing one task means, or null when none is written for it. */
    public static function completionFor(string $title): ?string
    {
        return self::COMPLETION[$title] ?? null;
    }

    /** The steps for one task title, or an empty list when it has none. */
    public static function activitiesFor(string $title): array
    {
        return self::ACTIVITIES[$title] ?? [];
    }

    /**
     * The whole checklist, keyed by role.
     *
     * Ordered by HotelTemplateBuilder::ROLES so a role added there cannot be
     * silently missed here — it appears with an empty list instead.
     *
     * @return array<string, list<array{title: string, description: string}>>
     */
    public static function all(): array
    {
        $out = [];

        foreach (array_keys(HotelTemplateBuilder::ROLES) as $role) {
            $out[$role] = self::forRole($role);
        }

        return $out;
    }

    /**
     * The checklist as numbered tasks: step index => one department's work.
     *
     * Step 0 is TASK 01. Each step carries its role, its label, and that role's
     * activities keyed by their position in forRole() — the position is what the
     * Create Task form posts, so it has to be the same key the controller reads
     * the title back by.
     *
     * @return array<int, array{role: string, label: string, tasks: array<int, array{title: string, description: string}>}>
     */
    public static function allByStep(): array
    {
        $byStep = [];

        foreach (self::STEP_ROLES as $step => $role) {
            $byStep[$step] = [
                'role' => $role,
                'label' => self::STEP_LABELS[$role],
                'tasks' => self::forRole($role),
            ];
        }

        return $byStep;
    }

    /**
     * Where in the Default Template each activity is done: the page to open and
     * the section on it to bring into view, keyed by lowercased title.
     *
     * Section names are the template's own data-hms-section values, plus
     * 'header' for the nav bar, which carries none. A null section opens the
     * page at the top — the whole page is the work. A section a template does
     * not have (Template 2 has no promos strip) falls back to the page top in
     * the editor rather than failing.
     *
     * @var array<string, array{page: string, section: ?string}>
     */
    private const AREAS = [
        'brand your hotel' => ['page' => 'home', 'section' => 'header'],
        'design the home page' => ['page' => 'home', 'section' => 'hero'],
        "write your hotel's story" => ['page' => 'home', 'section' => 'hero'],
        "choose the site's colours" => ['page' => 'home', 'section' => null],
        "set the site's typography" => ['page' => 'home', 'section' => null],
        'add your social profiles' => ['page' => 'home', 'section' => 'footer'],
        'fill in the promos section' => ['page' => 'home', 'section' => 'promos'],
        'build the partner brands strip' => ['page' => 'home', 'section' => 'partners'],
        'introduce your team' => ['page' => 'home', 'section' => 'team'],
        'write the experience page' => ['page' => 'experience', 'section' => null],
        'colour the experience page' => ['page' => 'experience', 'section' => null],
        'illustrate the experience page' => ['page' => 'experience', 'section' => null],
    ];

    /**
     * The template page and section an activity is worked on, or null for the
     * hotel concept — that is written on the dashboard, not in the template.
     *
     * Anything not listed in AREAS opens the page its role owns, which is right
     * for every Rooms, Restaurant and Amenities activity: each of those is the
     * whole page.
     *
     * @return array{page: string, section: ?string}|null
     */
    public static function areaFor(string $title, string $role): ?array
    {
        if (self::isConceptTitle($title)) {
            return null;
        }

        return self::AREAS[mb_strtolower(trim($title))] ?? [
            'page' => HotelTemplateBuilder::preferredPageForRole($role),
            'section' => null,
        ];
    }

    /** The name a numbered task is shown under, or null past the last one. */
    public static function stepLabel(int $step): ?string
    {
        $role = self::STEP_ROLES[$step] ?? null;

        return $role === null ? null : self::STEP_LABELS[$role];
    }

    /** Whether a title is the hotel concept, TASK 01's first activity. */
    public static function isConceptTitle(string $title): bool
    {
        return strcasecmp($title, HotelConceptDesk::TASK_TITLE) === 0;
    }

    /**
     * Task title (lowercased) => its zero-based step. Built once per request:
     * the checklist does not change inside one.
     *
     * The follow-up DesignTaskChain hands out on approval is not on the checklist
     * but is its role's work all the same, so it is filed under that role's task
     * rather than shown with no number.
     *
     * @return array<string, int>
     */
    public static function stepByTitle(): array
    {
        static $map = null;

        if ($map === null) {
            $map = [];
            foreach (self::allByStep() as $step => $stepData) {
                foreach ($stepData['tasks'] as $task) {
                    $map[mb_strtolower($task['title'])] = $step;
                }

                $followUp = DesignTaskChain::NEXT[$stepData['role']]['title'] ?? null;
                if ($followUp !== null) {
                    $map[mb_strtolower($followUp)] = $step;
                }
            }
        }

        return $map;
    }

    /**
     * Which numbered task a saved row belongs to, or null when its title is not
     * on the checklist — a one-off a faculty wrote by hand, or a retired
     * simulation task. Callers print nothing for those rather than inventing a
     * number.
     *
     * A task row keeps only a copy of the title it was assigned under, so this
     * is the one place a row's step is decided. Reading it here rather than
     * counting rows on screen is what keeps the student's list and the faculty's
     * Set Task screen saying the same number for the same work.
     */
    public static function stepForTitle(string $title): ?int
    {
        return self::stepByTitle()[mb_strtolower($title)] ?? null;
    }

    /** @return array<int, array{title: string, description: string}> */
    public static function forRole(string $role): array
    {
        return array_map(
            fn (array $task) => self::withActivities($task),
            self::TASKS[$role] ?? []
        );
    }
}
