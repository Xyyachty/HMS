<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The starting photographs used to be picsum.photos seeds, and a picsum seed is a
 * random photo: the Swimming Pool card showed a mountain, the Executive Chef a
 * road. The defaults in code now point at photographs of the thing they name, but
 * every team seeded before this still has the old seed URLs in its rows and saved
 * template content.
 *
 * Only an exact old default URL is swapped. A team's own upload is a storage path
 * or a data URL, and any picsum seed the starting content never used is left as
 * it is, so nothing a student chose is touched.
 */
return new class extends Migration
{
    /** Old seed => Unsplash photo id. Same pairs the templates and seeders now use. */
    private const SEEDS = [
        'hotelroom3' => '1631049307264-da0ec9d70304',
        'twinroom' => '1595576508898-0ad5c879a061',
        'hotelroom2' => '1590490360182-c33d57733427',
        'hotelroom1' => '1566665797739-1674de7a421a',
        'juniorsuite' => '1560185007-cde436f6a4d0',
        'penthouse1' => '1578683010236-d716f9a3f461',
        'exp-pool' => '1584132967334-10e028bd69f7',
        'exp-spa' => '1544161515-4ab6ce6db874',
        'exp-gym' => '1534438327276-14e5300c3a48',
        'exp-dining' => '1414235077428-338989a2e8c0',
        'exp-beach' => '1519046904884-53103b34b206',
        'exp-transport' => '1449965408869-eaa3f722e40d',
        'exp-lounge' => '1572116469696-31de0f17cc34',
        'exp-events' => '1431540015161-0bf868a2d407',
        'exp-tours' => '1506929562872-bb421503ef21',
        'exp-yoga' => '1544367567-0f2fcb009e0b',
        'exp-kids' => '1596461404969-9ae70f2830c1',
        'exp-garden' => '1585320806297-9794b3e4eeae',
        'hotelweekend' => '1424847651672-bf20a4b0982b',
        'hoteldinner' => '1550966871-3ed3cdb5ed0c',
        'hotelearlybird' => '1540541338287-41700207dee6',
        'hotelluxurypackage' => '1582610116397-edb318620f90',
        'hotelteam1' => '1573496359142-b8d87734a5a2',
        'hotelteam2' => '1507003211169-0a1dd7228f2d',
        'hotelteam3' => '1595257841889-eca2678454e2',
        'hotelteam4' => '1556157382-97eda2d62296',
        'luxuryhotel' => '1542314831-068cd1dbfeeb',
        'hotellobby' => '1625244724120-1fd1d34d00f6',
        'hotelpool' => '1563911302283-d2bc129e7570',
        'luxurysuite' => '1578683010236-d716f9a3f461',
        'hoteldining' => '1517248135467-4c7edcad34c4',
        'swimmingpool' => '1571896349842-33c89424de2d',
        'functionroom' => '1519167758481-83f550bb49b3',
        'hotelgym' => '1571902943202-507ec2618e8f',
        'hotelplayground' => '1575783970733-1aaedde1db74',
        'hotelspa' => '1600334089648-b0d9d3028eb2',
        'foldingbed' => '1595576508898-0ad5c879a061',
        'extratowel' => '1540555700478-4be289fbecef',
        'cateringstandard' => '1555244162-803834f70033',
        'cateringpremium' => '1530062845289-9109b2c9c868',
        'cateringplated' => '1464366400600-7168b8af9bc3',
        'scalloptartare' => '1625944525533-473f1a3d54e7',
        'wagyucarpaccio' => '1608877907149-a206d75ba011',
        'tomatobisque' => '1476718406336-bb5a9690ee2a',
        'seafoodchowder' => '1594756202469-9ff9799b2e4e',
        'doversole' => '1519708227418-c8fd9a32b7a2',
        'angusribeye' => '1600891964092-4316c288032e',
        'lambrack' => '1615937657715-bc7b4b7962c1',
        'rhubarbsouffle' => '1488477181946-6428a0291777',
        'chocfondant' => '1624353365286-3f8d62daad51',
        'oldfashioned' => '1470337458703-46ad1756a187',
        'goldnegroni' => '1551751299-1b51cab2694c',
        'calamansitea' => '1556679343-c7306c1976bc',
    ];

    /** Seeds the code built on the fly: a new room or dish got "room<timestamp>" and the like. */
    private const PATTERNS = [
        '/^(room-.+|room\d+|newroom\d+)$/' => '1611892440504-42a792e24d32',
        '/^menuitem\d+$/' => '1547592180-85f173990554',
        '/^amenity-.+$/' => '1621293954908-907159247fc8',
        '/^addon-.+$/' => '1540555700478-4be289fbecef',
    ];

    /** Template 2's Presidential Suite shared Template 1 Classic's seed. */
    private const PRESIDENTIAL = '1590381105924-c72589b9ef3f';

    /** table => [primary key, image columns] */
    private const COLUMNS = [
        'hotel_rooms' => ['hotel_room_id', ['image', 'gallery']],
        'hotel_room_categories' => ['hotel_room_category_id', ['image_path', 'gallery']],
        'hotel_amenities' => ['hotel_amenity_id', ['image', 'gallery']],
        'hotel_addons' => ['hotel_addon_id', ['image']],
        'hotel_catering_packages' => ['hotel_catering_package_id', ['image']],
        'hotel_promos' => ['hotel_promo_id', ['image']],
        'hotel_menu_items' => ['hotel_menu_item_id', ['image']],
        'template_images' => ['template_image_id', ['image_path']],
        'template_elements' => ['template_element_id', ['image_src', 'image_background']],
        'template_content_fields' => ['template_content_field_id', ['field_value']],
    ];

    public function up(): void
    {
        $text = DB::getDriverName() === 'mysql' ? 'CHAR' : 'TEXT';

        foreach (self::COLUMNS as $table => [$key, $columns]) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }
                $query = DB::table($table)->whereRaw("CAST($column AS $text) LIKE ?", ['%picsum.photos%']);
                if ($table === 'template_content_fields') {
                    // The item tells a saved Presidential Suite from a saved Classic room.
                    $query->leftJoin('template_content_items', 'template_content_items.template_content_item_id', '=', 'template_content_fields.template_content_item_id')
                        ->select('template_content_fields.template_content_field_id', "template_content_fields.$column", 'template_content_items.item_ref');
                } else {
                    $query->select($key, $column);
                }

                foreach ($query->get() as $row) {
                    $old = (string) $row->$column;
                    $new = $this->swap($old, ($row->item_ref ?? null) === 'presidential');
                    if ($new !== $old) {
                        DB::table($table)->where($key, $row->$key)->update([$column => $new]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // The old seeds were random photographs; there is nothing worth putting back.
    }

    /** Every old default URL in $value, plain or JSON-escaped (\/), swapped for its photograph. */
    private function swap(string $value, bool $presidential): string
    {
        return preg_replace_callback(
            '#https?:(\\\\?/)\1picsum\.photos\1seed\1([A-Za-z0-9_%-]+)\1(\d+)\1(\d+)(?:\.jpg)?#',
            function ($m) use ($presidential) {
                [$all, $slash, $seed, $w, $h] = $m;
                $id = $this->photoFor($seed, $presidential);
                if ($id === null) {
                    return $all;
                }
                $faces = str_starts_with($seed, 'hotelteam') ? '&crop=faces' : '';
                $url = "https://images.unsplash.com/photo-$id?auto=format&fit=crop$faces&w=$w&h=$h&q=80";

                return str_replace('/', $slash, $url);
            },
            $value
        );
    }

    private function photoFor(string $seed, bool $presidential): ?string
    {
        if ($seed === 'hotelroom3' && $presidential) {
            return self::PRESIDENTIAL;
        }
        if (isset(self::SEEDS[$seed])) {
            return self::SEEDS[$seed];
        }
        foreach (self::PATTERNS as $pattern => $id) {
            if (preg_match($pattern, $seed)) {
                return $id;
            }
        }

        return null;
    }
};
