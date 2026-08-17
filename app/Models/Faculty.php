<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * A faculty row of user_information. The primary key is user_information_id — students
 * and faculty share one table and one key space. A column named faculty_id elsewhere
 * (and on a student row of this same table) means "the faculty this row belongs to" and
 * points at user_information_id; on a faculty row itself it is null.
 */
class Faculty extends UserInformation
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope('faculty', function (Builder $query) {
            // Qualified through the model rather than hardcoded: a self-referencing
            // relation (Faculty::students) aliases the table, and getTable() follows it.
            $query->where(
                $query->getModel()->getTable() . '.user_type',
                UserInformation::TYPE_FACULTY
            );
        });

        static::creating(function (Faculty $faculty) {
            $faculty->user_type = UserInformation::TYPE_FACULTY;
        });
    }

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
            ->when($exceptFacultyId, fn ($q) => $q->where('user_information_id', '!=', $exceptFacultyId))
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

    public function studentGroups()
    {
        return $this->hasMany(StudentGroup::class, 'faculty_id', 'user_information_id');
    }

    public function classes()
    {
        return $this->hasMany(FacultyClass::class, 'faculty_id', 'user_information_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'faculty_id', 'user_information_id');
    }
}
