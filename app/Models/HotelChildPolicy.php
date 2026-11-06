<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * What a hotel charges for children, set by Room Management.
 *
 * bands are age ranges with a nightly rate: [{min_age, max_age, rate}], rate in whole
 * pesos, 0 for free. A child whose age no band covers stays free. From adult_age a
 * guest is priced as an adult, which the room rate already covers. With
 * free_with_existing_bedding on, the children who fit in the room's own beds stay
 * free whatever their age, and only a child past the room's limit (on an extra bed)
 * pays their age rate.
 *
 * A team that never saved one gets the unsaved default from forTeam(): ages 0-5
 * free, 6-17 at PHP 500 a night, priced as an adult from 18.
 *
 * price() is the one place a child's charge is worked out. The booking screens
 * preview it in the browser (childLines() in walk-in and both templates) and the
 * server prices the stay here again when it is booked.
 */
class HotelChildPolicy extends Model
{
    public const MAX_CHILD_AGE = 17;

    /** The house rule until a team saves its own: under 6 free, 6-17 pay 500 a night. */
    public const DEFAULT_BANDS = [
        ['min_age' => 0, 'max_age' => 5, 'rate' => 0],
        ['min_age' => 6, 'max_age' => 17, 'rate' => 500],
    ];

    protected $primaryKey = 'hotel_child_policy_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'adult_age',
        'free_with_existing_bedding',
        'bands',
    ];

    protected $casts = [
        'adult_age' => 'integer',
        'free_with_existing_bedding' => 'boolean',
        'bands' => 'array',
    ];

    protected $attributes = [
        'adult_age' => 18,
        'free_with_existing_bedding' => false,
    ];

    /** The team's saved policy, or the DEFAULT_BANDS policy when it has none. */
    public static function forTeam(string $groupName, int $facultyId): self
    {
        return self::where('group_name', $groupName)->where('faculty_id', $facultyId)->first()
            ?? new self(['group_name' => $groupName, 'faculty_id' => $facultyId, 'bands' => self::DEFAULT_BANDS]);
    }

    /**
     * Bands cleaned for storage: inside 0..adult_age-1, sorted, no overlaps. Throws
     * with a message the Room Management screen can show as it is.
     *
     * @return list<array{min_age: int, max_age: int, rate: int}>
     */
    public static function cleanBands(array $bands, int $adultAge): array
    {
        $out = [];
        foreach ($bands as $band) {
            $min = (int) ($band['min_age'] ?? 0);
            $max = (int) ($band['max_age'] ?? 0);
            if ($min < 0 || $max < $min) {
                throw new \InvalidArgumentException('Each age range needs a "from" age no higher than its "to" age.');
            }
            if ($max >= $adultAge) {
                throw new \InvalidArgumentException('Age ranges stop below ' . $adultAge . ', the age a guest is priced as an adult.');
            }
            $out[] = ['min_age' => $min, 'max_age' => $max, 'rate' => max(0, (int) ($band['rate'] ?? 0))];
        }

        usort($out, fn ($a, $b) => $a['min_age'] <=> $b['min_age']);
        for ($i = 1; $i < count($out); $i++) {
            if ($out[$i]['min_age'] <= $out[$i - 1]['max_age']) {
                throw new \InvalidArgumentException('Two age ranges overlap at age ' . $out[$i]['min_age'] . '.');
            }
        }

        return $out;
    }

    /**
     * One line per child, in the order the ages were given: [{age, rate, label}], rate
     * per night. $capacity is what the room sleeps without extra beds, 0 for no limit.
     *
     * @return list<array{age: int, rate: int, label: string}>
     */
    public function price(array $ages, int $adults, int $capacity): array
    {
        $adultAge = (int) $this->adult_age;
        $lines = [];
        foreach (array_values($ages) as $age) {
            $age = max(0, min(self::MAX_CHILD_AGE, (int) $age));
            if ($age >= $adultAge) {
                $lines[] = ['age' => $age, 'rate' => 0, 'label' => 'Priced as adult (' . $adultAge . '+), in the room rate'];
                continue;
            }
            $band = collect($this->bands ?: [])->first(fn ($b) => $age >= (int) $b['min_age'] && $age <= (int) $b['max_age']);
            $rate = $band ? (int) $band['rate'] : 0;
            $range = $band ? 'ages ' . $band['min_age'] . '–' . $band['max_age'] : 'no charge set for this age';
            $lines[] = ['age' => $age, 'rate' => $rate, 'label' => ($rate ? 'Child rate, ' : 'Free, ') . $range];
        }

        if ($this->free_with_existing_bedding) {
            // Adults, and children priced as adults, take the room's beds first. The
            // beds left go to the children who would otherwise cost the most.
            $spare = $capacity ? $capacity - $adults - count(array_filter($lines, fn ($l) => $l['age'] >= $adultAge)) : PHP_INT_MAX;
            $charged = array_keys(array_filter($lines, fn ($l) => $l['rate'] > 0));
            usort($charged, fn ($a, $b) => $lines[$b]['rate'] <=> $lines[$a]['rate']);
            foreach (array_slice($charged, 0, max(0, $spare)) as $i) {
                $lines[$i]['rate'] = 0;
                $lines[$i]['label'] = 'Free, shares the existing beds';
            }
        }

        return $lines;
    }

    public function toTemplateArray(): array
    {
        return [
            'saved' => $this->exists,
            'adultAge' => (int) $this->adult_age,
            'freeWithExistingBedding' => (bool) $this->free_with_existing_bedding,
            'bands' => array_values($this->bands ?: []),
            // Not part of the child policy, but every screen that prices a party reads this.
            'extraAdultRate' => HotelBooking::EXTRA_ADULT_RATE,
        ];
    }
}
