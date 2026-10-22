<?php

namespace App\Models;

use App\Support\HotelComplaintAccess;
use App\Support\HotelImageStore;
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
            'image'          => 'https://picsum.photos/seed/hotelweekend/800/600.jpg',
            'original_price' => 14000,
            'promo_price'    => 11200,
        ],
        [
            'name'           => 'Dine and Stay',
            'description'    => 'Any suite booked for three nights comes with a chef-curated dinner for two at the restaurant.',
            'image'          => 'https://picsum.photos/seed/hoteldinner/800/600.jpg',
            'original_price' => 32500,
            'promo_price'    => 30000,
        ],
        [
            'name'           => 'Early Bird Rate',
            'description'    => 'Reserve at least thirty days ahead and keep the low season rate whichever month you arrive.',
            'image'          => 'https://picsum.photos/seed/hotelearlybird/800/600.jpg',
            'original_price' => 5000,
            'promo_price'    => 4250,
        ],
        [
            'name'           => 'Luxury Package',
            'description'    => 'A suite, a spa afternoon for two and dinner at the restaurant, booked as one stay.',
            'image'          => 'https://picsum.photos/seed/hotelluxurypackage/800/600.jpg',
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

                foreach (self::DEFAULTS as $promo) {
                    self::create($promo + [
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
