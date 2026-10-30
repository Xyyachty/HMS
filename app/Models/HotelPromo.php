<?php

namespace App\Models;

use App\Support\HotelComplaintAccess;
use App\Support\HotelImageStore;
use App\Support\HotelTemplateBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * A promo or package the Front Desk runs during the Simulation.
 *
 * The team's site shows the active ones that have not passed their end date.
 * Once a team has any promo at all, those replace the Promos and Packages
 * section's starting cards; until then the section keeps the cards the team
 * customized in the editor. The editor itself always shows those cards, so
 * this never touches a Customization task.
 *
 * The four starting promos stay tied to those cards by template_key: a title,
 * description or picture changed on a card in the builder is copied onto its
 * promo here (syncFromTemplate), so the Simulation runs what the team designed.
 */
class HotelPromo extends Model
{
    protected $primaryKey = 'hotel_promo_id';

    /** Simulation seats that may write promos. */
    public const MANAGE_ROLES = ['front_desk', 'administrator'];

    /**
     * The four promos every team starts with: the same offers both templates' Home
     * pages draw as their starting cards (DEFAULT_PROMOS), priced off the default
     * room rates in HotelRoomDefaults. Front Desk edits or removes them like any other.
     */
    public const DEFAULTS = [
        [
            'name'           => 'Weekend Escape',
            'description'    => 'Two nights in a Deluxe Room with breakfast for two and a late checkout at 2 PM.',
            'image'          => 'https://images.unsplash.com/photo-1424847651672-bf20a4b0982b?auto=format&fit=crop&w=800&h=600&q=80',
            'original_price' => 14000,
            'promo_price'    => 11200,
        ],
        [
            'name'           => 'Dine and Stay',
            'description'    => 'Any suite booked for three nights comes with a chef-curated dinner for two at the restaurant.',
            'image'          => 'https://images.unsplash.com/photo-1550966871-3ed3cdb5ed0c?auto=format&fit=crop&w=800&h=600&q=80',
            'original_price' => 32500,
            'promo_price'    => 30000,
        ],
        [
            'name'           => 'Early Bird Rate',
            'description'    => 'Reserve at least thirty days ahead and keep the low season rate whichever month you arrive.',
            'image'          => 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=800&h=600&q=80',
            'original_price' => 5000,
            'promo_price'    => 4250,
        ],
        [
            'name'           => 'Luxury Package',
            'description'    => 'A suite, a spa afternoon for two and dinner at the restaurant, booked as one stay.',
            'image'          => 'https://images.unsplash.com/photo-1582610116397-edb318620f90?auto=format&fit=crop&w=800&h=600&q=80',
            'original_price' => 26000,
            'promo_price'    => 22000,
        ],
    ];

    /** How long the starting promos run from the day a team is handed them. */
    public const DEFAULT_DAYS = 90;

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'name',
        'description',
        'image',
        'original_price',
        'promo_price',
        'start_date',
        'end_date',
        'is_active',
        'template_key',
    ];

    protected $casts = [
        'original_price' => 'integer',
        'promo_price'    => 'integer',
        'start_date'     => 'date',
        'end_date'       => 'date',
        'is_active'      => 'boolean',
    ];

    /**
     * Front Desk in this phase, or signed into the hotel site as Front Desk
     * staff. HotelComplaintAccess::roles() already reads both.
     */
    public static function canManage(StudentGroup $membership): bool
    {
        return count(array_intersect(HotelComplaintAccess::roles($membership), self::MANAGE_ROLES)) > 0;
    }

    /**
     * Hands a team the four starting promos, once. The team's groups row records
     * that it happened, so deleting all four does not bring them back. A team that
     * already wrote promos of its own is only marked, never given more.
     */
    public static function ensureDefaultsFor(StudentGroup $membership): void
    {
        $group = Group::where('group_name', $membership->group_name)
            ->where('faculty_id', $membership->faculty_id)
            ->first();

        if (!$group || $group->promos_seeded_at) {
            return;
        }

        DB::transaction(function () use ($group, $membership) {
            // Two teammates loading at once must not seed eight.
            $group = Group::whereKey($group->group_id)->lockForUpdate()->first();
            if ($group->promos_seeded_at) {
                return;
            }

            if (!self::forTeam($membership->group_name, $membership->faculty_id)->exists()) {
                $start = now('Asia/Manila')->toDateString();
                $end = now('Asia/Manila')->addDays(self::DEFAULT_DAYS)->toDateString();

                foreach (self::DEFAULTS as $i => $promo) {
                    self::create($promo + [
                        'template_key' => 'promo-' . ($i + 1),
                        'group_name' => $membership->group_name,
                        'faculty_id' => $membership->faculty_id,
                        'group_id'   => $group->group_id,
                        'start_date' => $start,
                        'end_date'   => $end,
                        'is_active'  => true,
                    ]);
                }
            }

            $group->forceFill(['promos_seeded_at' => now()])->save();
        });

        // A team that customized the cards before its promos existed starts with
        // its own words and pictures, not the stock ones.
        self::adoptTemplateContent($membership->group_name, (int) $membership->faculty_id);
    }

    /**
     * The builder's promo cards as promo fields: ['promo-1' => ['name' => ...,
     * 'description' => ..., 'image' => ...]]. Text is read off the card's title and
     * description elements, the picture off the card-image map. The offer label and
     * the terms line are left out: on a promo those are drawn from its price and
     * dates, which Front Desk sets.
     *
     * @return array<string, array<string, string>>
     */
    public static function templateContent(array $customizations): array
    {
        $out = [];

        foreach ($customizations as $key => $entry) {
            if (!is_string($key) || !is_array($entry) || !isset($entry['text'])) {
                continue;
            }
            if (preg_match('/\[data-hms-category="(promo-\d+)-(title|desc)"\]$/', $key, $m)) {
                $text = trim(preg_replace('/\s+/', ' ', (string) $entry['text']));
                if ($text !== '') {
                    $out[$m[1]][$m[2] === 'title' ? 'name' : 'description'] = $m[2] === 'title'
                        ? mb_substr($text, 0, 120)
                        : mb_substr($text, 0, 600);
                }
            }
        }

        $map = $customizations[HotelTemplateBuilder::CARD_IMAGES_KEY]['map'] ?? [];
        foreach (is_array($map) ? $map : [] as $ref => $url) {
            if (preg_match('/^promo:(promo-\d+)$/', (string) $ref, $m) && is_string($url) && trim($url) !== '') {
                $out[$m[1]]['image'] = HotelImageStore::relativize($url);
            }
        }

        return $out;
    }

    /**
     * Copies the team's customized cards onto its starting promos, field by field,
     * only where the promo still holds the stock value. What Front Desk already
     * changed in the Simulation is left alone.
     */
    public static function adoptTemplateContent(string $groupName, int $facultyId): void
    {
        $content = self::templateContent(HotelTemplateBuilder::mergeTeamCustomizations($groupName, $facultyId));
        if ($content === []) {
            return;
        }

        foreach (self::forTeam($groupName, $facultyId)->whereNotNull('template_key')->get() as $promo) {
            $stock = self::DEFAULTS[(int) substr($promo->template_key, 6) - 1] ?? null;
            foreach ($stock ? ($content[$promo->template_key] ?? []) : [] as $field => $value) {
                if ($promo->$field === $stock[$field]) {
                    $promo->$field = $value;
                }
            }
            $promo->save();
        }
    }

    /**
     * Called on every builder save. Copies onto the starting promos only the card
     * fields this save changed, so an unrelated edit elsewhere on the site never
     * writes stale card text over a promo Front Desk has since edited.
     *
     * $previousText is this role's stored promo card text before the save
     * (storedTemplateText), $previousImages its stored card-image map, $mine the
     * customizations just written, $storedImages the card-image map after the write,
     * where a newly uploaded picture has become a storage path.
     */
    public static function syncFromTemplate(
        string $groupName,
        int $facultyId,
        array $previousText,
        array $previousImages,
        array $mine,
        callable $storedImages
    ): void {
        $before = self::templateContent(
            collect($previousText)->map(fn ($text) => ['text' => $text])->all()
            + [HotelTemplateBuilder::CARD_IMAGES_KEY => ['map' => $previousImages]]
        );

        $changes = [];
        foreach (self::templateContent($mine) as $key => $fields) {
            foreach ($fields as $field => $value) {
                if (($before[$key][$field] ?? null) !== $value) {
                    $changes[$key][$field] = $value;
                }
            }
        }
        if ($changes === []) {
            return;
        }

        // A picture sent as a data-URL is only a storage path once written.
        $stored = null;
        foreach ($changes as $key => $fields) {
            if (isset($fields['image']) && str_starts_with($fields['image'], 'data:')) {
                $stored ??= $storedImages();
                $path = $stored['promo:' . $key] ?? '';
                if ($path !== '' && !str_starts_with($path, 'data:')) {
                    $changes[$key]['image'] = HotelImageStore::relativize($path);
                } else {
                    unset($changes[$key]['image']);
                }
            }
        }

        foreach (self::forTeam($groupName, $facultyId)->whereIn('template_key', array_keys($changes))->get() as $promo) {
            $promo->fill($changes[$promo->template_key])->save();
        }
    }

    /**
     * This template row's stored promo card text, keyed like its customizations.
     *
     * @return array<string, string>
     */
    public static function storedTemplateText(int $templateId): array
    {
        return TemplateElement::query()
            ->where('team_role_template_id', $templateId)
            ->where('version_id', \App\Support\TemplateCustomizationStore::LIVE_VERSION_ID)
            ->where('element_key', 'like', '%data-hms-category="promo-%')
            ->pluck('text_content', 'element_key')
            ->map(fn ($text) => (string) $text)
            ->all();
    }

    /**
     * The team's promo a stay starting on $checkIn may be booked under: switched on
     * and running that day. Throws with a message the booking forms show as is.
     */
    public static function bookable(string $groupName, int $facultyId, int $promoId, string $checkIn): self
    {
        $promo = self::forTeam($groupName, $facultyId)->whereKey($promoId)->first();
        $day = \Illuminate\Support\Carbon::parse($checkIn)->toDateString();

        if (!$promo || !$promo->is_active
            || $day < $promo->start_date->toDateString() || $day > $promo->end_date->toDateString()) {
            throw new \RuntimeException('That promo is not available for a stay starting on '
                . \Illuminate\Support\Carbon::parse($day)->format('M j, Y') . '. Please choose another.');
        }

        return $promo;
    }

    public static function forTeam(string $groupName, ?int $facultyId)
    {
        return self::where('group_name', $groupName)
            ->where('faculty_id', $facultyId)
            ->orderBy('start_date')
            ->orderBy('hotel_promo_id');
    }

    /**
     * What the team's site reads: only the promos it may show, plus whether the
     * team has any promo at all, which is what decides between these and the
     * section's starting cards.
     */
    public static function siteFeed(string $groupName, ?int $facultyId): array
    {
        $all = self::forTeam($groupName, $facultyId)->get();

        return [
            'items'      => $all->map->toTemplateArray()->filter(fn ($p) => $p['live'])->values(),
            'has_promos' => $all->isNotEmpty(),
        ];
    }

    public function toTemplateArray(): array
    {
        // Users are in Manila while the app runs on UTC (see e568ecc).
        $today = now('Asia/Manila')->toDateString();
        $start = $this->start_date->toDateString();
        $end = $this->end_date->toDateString();

        $state = !$this->is_active ? 'Inactive' : ($end < $today ? 'Expired' : ($start > $today ? 'Scheduled' : 'Running'));

        $range = $this->start_date->year === $this->end_date->year
            ? $this->start_date->format('M j') . ' – ' . $this->end_date->format('M j, Y')
            : $this->start_date->format('M j, Y') . ' – ' . $this->end_date->format('M j, Y');
        $peso = fn (int $n) => '₱' . number_format($n);

        return [
            'id'             => 'db-promo-' . $this->hotel_promo_id,
            'dbId'           => $this->hotel_promo_id,
            'name'           => $this->name,
            'description'    => $this->description,
            'img'            => HotelImageStore::url($this->image),
            'original_price' => $this->original_price,
            'promo_price'    => (int) $this->promo_price,
            'start_date'     => $start,
            'end_date'       => $end,
            'status'         => $this->is_active ? 'active' : 'inactive',
            'state'          => $state,
            // Scheduled promos show too: they are active and not expired, and
            // their dates are printed on the card.
            'live'           => $state === 'Running' || $state === 'Scheduled',
            // The Promos and Packages section's own card fields, so both
            // templates draw these with the component they already have.
            'title'          => $this->name,
            'desc'           => $this->description,
            'offer'          => $peso((int) $this->promo_price),
            'terms'          => ($this->original_price ? 'Regular ' . $peso((int) $this->original_price) . ' · ' : '') . 'Valid ' . $range,
        ];
    }
}
