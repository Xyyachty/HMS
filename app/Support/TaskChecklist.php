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
 *   TASK 01  Front Desk Customization        — the hotel concept, then FD TASK
 *                                              1-8: branding, the hero, promos,
 *                                              partner brands, hotel experiences,
 *                                              our team, the footer and the
 *                                              Highlights page
 *   TASK 02  Room Management Customization   — the Rooms page: RM TASK 1-5,
 *                                              header, categories, details,
 *                                              slider photos and rooms
 *   TASK 03  Restaurant Management Customization — the Restaurant page
 *   TASK 04  Housekeeping Customization      — the Amenities page: HK TASK
 *                                              1-5, header, images, information,
 *                                              a new amenity and a review
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
                'title' => 'Customize Your Hotel Branding',
                'description' => 'Make the website your own by changing the hotel logo, hotel name, and navigation names.',
            ],
            [
                'title' => 'Customize the Home Page',
                'description' => 'Customize the Home page by changing the 5 slider images and editing the text in the Hero section to match your hotel.',
            ],
            [
                'title' => 'Customize Promos and Packages',
                'description' => 'Customize the Promos and Packages section by updating the images, titles, descriptions, and other promo details to match your hotel.',
            ],
            [
                'title' => 'Customize Partner Brands',
                'description' => 'Customize the Partner Brands section by adding your hotel’s partner brands and updating their names and images.',
            ],
            [
                'title' => 'Customize Hotel Experiences',
                'description' => 'Customize the Hotel Experiences section on the Home page so it introduces the best experiences your hotel offers.',
            ],
            [
                'title' => 'Customize Our Team',
                'description' => 'Customize the Our Team section by updating the photos, names, and positions of the hotel team members.',
            ],
            [
                'title' => 'Customize the Footer',
                'description' => 'Customize the Footer by updating the hotel information, links, contact details, and other text to match the hotel.',
            ],
            [
                'title' => 'Customize Hotel Highlights',
                'description' => 'Customize the Hotel Highlights section to showcase the best features, attractions, and special things your hotel offers.',
            ],
        ],

        'room_management' => [
            // RM TASK 1-5, in order, all on the Rooms page: the header in
            // Design mode; the categories with the tab bar's + and pencils;
            // their details and photos from each category's own tools; the
            // rooms with Add Room.
            [
                'title' => 'Customize Rooms Header',
                'description' => 'Customize the main Rooms section so it matches your hotel.',
            ],
            [
                'title' => 'Manage Room Categories',
                'description' => 'Create and organize the room categories offered by your hotel. Examples are Classic, Superior, Deluxe, Premium, Family, Executive, and Presidential.',
            ],
            [
                'title' => 'Customize Room Details',
                'description' => 'Update the information for each room category so guests can clearly understand the room.',
            ],
            [
                'title' => 'Update Room Slider Images',
                'description' => 'Add or replace the photos displayed in each room\'s image slider.',
            ],
            [
                'title' => 'Add and Manage Rooms',
                'description' => 'Add the actual hotel rooms under the correct room category and make sure their information is correct.',
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
                'title' => 'Design the Restaurant Hero',
                'description' => "The hero is the first thing a guest sees on the Restaurant page: the big two-line heading, the paragraph under it and the three short facts below the buttons. Replace the sample words with your restaurant's own. The plate photograph beside them is your first photographed dish, so make sure that dish is one worth leading with.",
            ],
            [
                'title' => 'Edit the Best Seller Section',
                'description' => "Best Seller sits under the hero and shows the first six dishes on your menu - the same six the Home page previews. Rename the section in your restaurant's voice, and give every one of the six a photograph, a price and a line saying what it is.",
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
            // HK TASK 1-5, in order. All five are done on the Amenities
            // page: its header in Design mode, the facilities with the card
            // tools or the Housekeeping Amenities screen - one team list.
            [
                'title' => 'Customize Amenities Header',
                'description' => 'Customize the main Amenities section so it matches the hotel.',
            ],
            [
                'title' => 'Update Amenity Images',
                'description' => 'Update the images of the amenities so they properly represent each hotel facility or service.',
            ],
            [
                'title' => 'Edit Amenity Information',
                'description' => 'Update the information of each amenity so guests can clearly understand what it offers.',
            ],
            [
                'title' => 'Add a New Amenity',
                'description' => 'Add a new hotel facility or service to the Amenities section.',
            ],
            [
                'title' => 'Review Hotel Amenities',
                'description' => 'Review the complete Amenities section and make sure all information and images are correct before submitting.',
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
        'Customize Rooms Header' => 'The label, the title and the description above the rooms are your own words.',
        'Manage Room Categories' => 'The category tabs are the ones your hotel offers, named the way it sells them.',
        'Customize Room Details' => 'Every room category has its own name, price per night and description.',
        'Update Room Slider Images' => 'Every room category\'s slider shows uploaded photos of that room.',
        'Add and Manage Rooms' => 'The hotel\'s rooms are listed under the right categories with correct details.',
        'Customize Amenities Header' => 'The label, the title and the description above the amenities are your own words.',
        'Update Amenity Images' => 'Every amenity shows slider images of the real facility or service.',
        'Edit Amenity Information' => 'Every amenity has its own name, location, available hours and description.',
        'Add a New Amenity' => 'A new amenity of your hotel appears in the Amenities section with its details and images.',
        'Review Hotel Amenities' => 'Every amenity is one the hotel really has, with correct details and images.',
        'Customize Your Hotel Branding' => 'Your own logo, hotel name and navigation names appear correctly on View Live.',
        'Customize the Home Page' => 'The 5 slider images, the small heading, the main heading and the description are all your own.',
        'Customize Promos and Packages' => 'Every promo carries your own image, title, offer label, description and details.',
        'Customize Hotel Experiences' => 'The section\'s headings and button are your own words, and its three cards show your hotel\'s own experiences.',
        'Customize Partner Brands' => 'Every card is one of your hotel\'s real partners, with its own name and image, and no sample brands remain.',
        'Customize Our Team' => 'Every team member shows their own photo, their correct name and the position they hold.',
        'Customize the Footer' => 'The footer\'s description, links and contact details are all correct for your hotel.',
        'Customize Hotel Highlights' => 'Every highlight is one your hotel really offers, with its own photo and title, and no sample highlights remain.',
        'Build Your Menu' => 'Every dish the restaurant serves is on the page and no sample dish remains.',
        'Photograph and Price the Menu' => 'Every dish carries its own photograph and its price.',
        'Organise the Menu into Categories' => 'Every dish sits in a named section, and the tabs read as your restaurant\'s.',
        'Style the Menu Cards' => 'The dish cards and the section behind them sit in your palette.',
        'Design the Restaurant Hero' => 'The heading, the paragraph and the three facts are your own words, and the plate shows one of your dishes.',
        'Edit the Best Seller Section' => 'The section is named in your own words and its six dishes each carry a photograph, a price and a description.',
        'Write the Restaurant Page Introduction' => 'The heading and its introduction are your own words, with no sample copy left on the page.',
        'Write Every Dish Description' => 'Every dish on the menu has its own description and none of the sample lines remain.',
        'Make the Dish Photographs Consistent' => 'The menu reads as one set of photographs rather than a collection.',
        'Check the Restaurant Page on a Phone' => 'The page reads cleanly in one column with the tabs and every card intact.',
        'Review the Restaurant Page Against the Site' => 'The two pages read as one hotel, with any remaining difference a deliberate one.',
    ];

    /**
     * The steps one activity is worked through as, keyed by its title.
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
        'Customize Rooms Header' => [
            'Edit the small “ACCOMMODATIONS” label.',
            'Edit the “Our Rooms & Suites” title.',
            'Edit the description below the title.',
        ],
        'Manage Room Categories' => [
            'Review the existing room categories.',
            'Edit a room category name if needed.',
            'Add a new room category.',
            'Make sure the categories are organized and displayed correctly.',
        ],
        'Customize Room Details' => [
            'Edit the room/category name.',
            'Edit the room price per night.',
            'Edit the room description.',
            'Review and save the updated room information.',
        ],
        'Update Room Slider Images' => [
            'Select the room category you want to update.',
            'Open “Change Photos.”',
            'Upload or replace the room slider images.',
            'Review the slider and make sure all images display correctly.',
        ],
        'Add and Manage Rooms' => [
            'Select the correct room category.',
            'Add a new room.',
            'Enter the room number and required room details.',
            'Make sure the room appears under the correct category.',
            'Review the room and save the changes.',
        ],
        'Customize Amenities Header' => [
            'Edit the small label above the title.',
            'Edit the "Hotel Amenities" title.',
            'Edit the description below the title.',
        ],
        'Update Amenity Images' => [
            'Select an amenity to update.',
            'Replace or upload its slider images.',
            'Review the images and make sure they match the amenity.',
        ],
        'Edit Amenity Information' => [
            'Edit the amenity name.',
            'Edit the amenity location.',
            'Edit the available hours.',
            'Edit the amenity description.',
        ],
        'Add a New Amenity' => [
            'Click "Add an Amenity".',
            'Enter the amenity name and required details.',
            'Upload the slider images for the new amenity.',
            'Save the amenity and confirm that it appears correctly in the Amenities section.',
        ],
        'Review Hotel Amenities' => [
            'Check that every amenity has the correct name, location, available hours, and description.',
            'Check that all amenity slider images are correct and properly displayed.',
            'Update or remove any amenity that does not match the hotel.',
        ],
        'Customize Your Hotel Branding' => [
            'Change the Logo: upload your team\'s hotel logo.',
            'Change the Hotel Name: replace the default hotel name with your team\'s hotel name.',
            'Rename the Navigation: change the names of the main navigation items, such as Home, Rooms, Restaurant, Amenities, and Highlights.',
            'Check Your Changes: click View Live and make sure your logo, hotel name, and navigation names appear correctly.',
        ],
        'Customize the Home Page' => [
            'Change the 5 Slider Images: replace all 5 images in the Home page slider with images that represent your hotel.',
            'Edit the Small Heading: change the small text above the main heading, such as "COMFORT BY THE SEA."',
            'Edit the Main Heading: change the main Hero text, such as "Where Elegance Meets Comfort."',
            'Edit the Description: change the short description below the main heading to describe your hotel.',
        ],
        'Customize Promos and Packages' => [
            'Change the images of the featured promo and the promo cards.',
            'Edit the promo titles and labels, such as the discount or offer name.',
            'Edit the descriptions and other details of each promo or package.',
            'Review the Promos and Packages section and make sure all information and images match your hotel.',
        ],
        'Customize Partner Brands' => [
            'Change the images of the existing partner brands.',
            'Edit the brand names to match your hotel’s actual partners.',
            'Add new partner brands using the Add Brand button.',
            'Remove unnecessary brands and review the section to make sure all partner information is correct.',
        ],
        'Customize Hotel Experiences' => [
            'Edit the small heading above the section, such as "WORTH THE STAY."',
            'Edit the section title, such as "Selected Highlights."',
            'Edit the label of the "View all highlights" button.',
            'Check that the three experience cards show your hotel\'s own photos and names.',
        ],
        'Customize Our Team' => [
            'Change the photos of each team member.',
            'Edit the names to show the correct team members.',
            'Edit their positions or roles to match their responsibilities in the hotel.',
        ],
        'Customize the Footer' => [
            'Edit the hotel description with information about the hotel.',
            'Update the footer links and their names.',
            'Update the contact details, including the address, phone number, and email.',
            'Review the footer and make sure all information is correct and matches the hotel.',
        ],
        'Customize Hotel Highlights' => [
            'Change the images of the hotel highlights.',
            'Edit the highlight names or titles to match your hotel.',
            'Add new highlights that showcase the best features of your hotel.',
            'Remove unnecessary highlights and review the section to make sure everything matches your hotel.',
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
        'Design the Restaurant Hero' => [
            'Rewrite both lines of the hero heading so they name your restaurant\'s food.',
            'Rewrite the paragraph under it in two or three sentences.',
            'Rewrite the three facts under the buttons to say what your kitchen offers.',
            'Check the plate photograph is a dish you want guests to see first.',
        ],
        'Edit the Best Seller Section' => [
            'Rewrite the small line and the Best Seller heading in your own words.',
            'Read the six dishes it shows as a guest would, looking for sample content.',
            'Give each of the six a photograph, a price and a one-line description.',
            'Open the Home page and confirm its Best Seller preview matches.',
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
    ];

    /**
     * A task with its steps written into the description, as "Steps:" and its
     * numbered lines. Every reader of the checklist goes through here, so the
     * Create Task tab, the row it saves and the student's copy all carry the same
     * steps.
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
        // rendering the steps as their own list wants the paragraph on its own,
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
        'customize your hotel branding' => ['page' => 'home', 'section' => 'header'],
        // Rows assigned before the rename still carry the old title.
        'brand your hotel' => ['page' => 'home', 'section' => 'header'],
        'design the home page' => ['page' => 'home', 'section' => 'hero'],
        'customize promos and packages' => ['page' => 'home', 'section' => 'promos'],
        // Rows assigned before the rename still carry the old title.
        'fill in the promos section' => ['page' => 'home', 'section' => 'promos'],
        'customize partner brands' => ['page' => 'home', 'section' => 'partners'],
        // Rows assigned before the rename still carry the old title.
        'build the partner brands strip' => ['page' => 'home', 'section' => 'partners'],
        'customize our team' => ['page' => 'home', 'section' => 'team'],
        // Rows assigned before the rename still carry the old title.
        'introduce your team' => ['page' => 'home', 'section' => 'team'],
        'customize hotel experiences' => ['page' => 'home', 'section' => 'highlights'],
        'customize the footer' => ['page' => 'home', 'section' => 'footer'],
        // The highlights are added, photographed and renamed on the Highlights
        // page; the Home page only previews the first three.
        'customize hotel highlights' => ['page' => 'experience', 'section' => null],
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

    /**
     * Titles (lowercased) whose review compares against the stock template
     * rather than a snapshot. Branding is the team's first change to the site,
     * so "what did the student change" means "what differs from the template
     * they were given": the logo, the hotel name, the link labels. A snapshot
     * taken later — the student's own save, which is what a row with no
     * assignment snapshot fell back to — already holds that work, and the
     * review then outlined nothing.
     *
     * The Home Page task has the same problem: the slides and the hero text are
     * usually changed long before the task is handed in. The value names which
     * page check the After preview runs (see hms-review-highlight.js).
     */
    private const REVIEW_AGAINST_STOCK = [
        'customize your hotel branding' => 'branding',
        // Rows assigned before the rename still carry the old title.
        'brand your hotel' => 'branding',
        'customize the home page' => 'home',
        'design the home page' => 'home',
        'customize promos and packages' => 'promos',
        'fill in the promos section' => 'promos',
        'customize partner brands' => 'partners',
        'build the partner brands strip' => 'partners',
        'customize our team' => 'team',
        'introduce your team' => 'team',
        'customize the footer' => 'footer',
        'customize hotel experiences' => 'highlights',
        'customize hotel highlights' => 'highlights',
        'customize amenities header' => 'amenities-header',
        'update amenity images' => 'amenities',
        'edit amenity information' => 'amenities',
        'add a new amenity' => 'amenities',
        'review hotel amenities' => 'amenities-all',
        'customize rooms header' => 'rooms-header',
        'manage room categories' => 'room-categories',
        'customize room details' => 'room-details',
        'update room slider images' => 'room-photos',
        'add and manage rooms' => 'rooms-added',
    ];

    public static function reviewsAgainstStock(string $title): bool
    {
        return self::stockReviewFor($title) !== null;
    }

    /** Which page check a task's After preview runs: 'branding', 'home', 'promos', 'partners', 'team', 'footer', 'highlights', or one of the amenities checks, or null. */
    public static function stockReviewFor(string $title): ?string
    {
        return self::REVIEW_AGAINST_STOCK[mb_strtolower(trim($title))] ?? null;
    }

    /**
     * The number a task is shown under within its role - 'FD TASK 3' - or null
     * for the hotel concept and for anything not on the checklist.
     *
     * Read from the checklist, not from where a card happens to sit: the cards
     * are re-sorted as work is handed in, and a number taken from the position
     * moved every time one was.
     */
    public static function taskNumber(string $title, string $role): ?int
    {
        $number = 0;
        foreach (self::TASKS[$role] ?? [] as $task) {
            if (self::isConceptTitle($task['title'])) {
                continue;
            }
            $number++;
            if (strcasecmp($task['title'], trim($title)) === 0) {
                return $number;
            }
        }

        return null;
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
