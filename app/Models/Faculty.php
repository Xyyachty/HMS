<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StudentGroup;

class Faculty extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number',
        'status',
        'block',
    ];

    /**
     * Block options come from Faculty Manage Students classes (faculty_classes).
     * e.g. Class A / Class B → Block A / Block B.
     */
    public static function existingClassLetters(): array
    {
        $letters = FacultyClass::query()
            ->select('letter')
            ->distinct()
            ->pluck('letter')
            ->map(fn ($letter) => strtoupper(trim((string) $letter)))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        // Class A is always created first in Manage Students — keep it selectable
        // before any faculty has opened that page yet.
        if ($letters === []) {
            return ['A'];
        }

        return $letters;
    }

    public static function isValidBlock(?string $block): bool
    {
        $block = strtoupper(trim((string) $block));

        return $block !== '' && in_array($block, static::existingClassLetters(), true);
    }

    public static function blockLabel(?string $block): string
    {
        $block = strtoupper(trim((string) $block));
        if ($block === '') {
            return '—';
        }

        return 'Block ' . $block;
    }

    /** Unassigned class letters (optionally excluding one faculty's current assignment). */
    public static function availableBlocks(?int $exceptFacultyId = null): array
    {
        $existing = static::existingClassLetters();

        $taken = static::query()
            ->when($exceptFacultyId, fn ($q) => $q->where('id', '!=', $exceptFacultyId))
            ->whereNotNull('block')
            ->where('block', '!=', '')
            ->pluck('block')
            ->map(fn ($b) => strtoupper((string) $b))
            ->all();

        return array_values(array_filter(
            $existing,
            fn ($letter) => !in_array($letter, $taken, true)
        ));
    }

    /** Options for a faculty update dropdown: available class letters + their current block. */
    public static function selectableBlocksForFaculty(?int $facultyId = null, ?string $currentBlock = null): array
    {
        $options = static::availableBlocks($facultyId);
        $current = strtoupper(trim((string) $currentBlock));

        if ($current !== '' && in_array($current, static::existingClassLetters(), true) && !in_array($current, $options, true)) {
            $options[] = $current;
        }

        sort($options);

        return array_values($options);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function studentGroups()
    {
        return $this->hasMany(StudentGroup::class);
    }

    public function classes()
    {
        return $this->hasMany(FacultyClass::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
