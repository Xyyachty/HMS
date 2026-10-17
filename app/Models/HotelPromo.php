<?php

namespace App\Models;

use App\Support\HotelComplaintAccess;
use App\Support\HotelImageStore;
use Illuminate\Database\Eloquent\Model;

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
