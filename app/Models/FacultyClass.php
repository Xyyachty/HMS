<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FacultyClass extends Model
{
    public const CAPACITY = 40;

    protected $fillable = [
        'faculty_id',
        'name',
        'letter',
        'capacity',
        'status',
        'sort_order',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'faculty_class_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isFull(): bool
    {
        return $this->students()->count() >= ($this->capacity ?: self::CAPACITY);
    }

    public function seatsTaken(): int
    {
        return $this->students()->count();
    }

    public function seatsLeft(): int
    {
        return max(0, ($this->capacity ?: self::CAPACITY) - $this->seatsTaken());
    }

    /**
     * Recalculate open/closed from live seat counts.
     * Reopens classes that dropped below capacity, removes empty trailing tabs,
     * and keeps only the earliest non-full class as open for new enrollments.
     */
    public static function reconcileForFaculty(int $facultyId)
    {
        return DB::transaction(function () use ($facultyId) {
            $classes = static::where('faculty_id', $facultyId)
                ->orderBy('sort_order')
                ->lockForUpdate()
                ->get();

            if ($classes->isEmpty()) {
                static::create([
                    'faculty_id' => $facultyId,
                    'name' => 'Class A',
                    'letter' => 'A',
                    'capacity' => self::CAPACITY,
                    'status' => 'open',
                    'sort_order' => 1,
                ]);

                return static::where('faculty_id', $facultyId)->orderBy('sort_order')->get();
            }

            // Drop empty classes after the first one (e.g. leftover Class B when A is empty/not full)
            $kept = collect();
            foreach ($classes as $index => $class) {
                $taken = $class->students()->count();
                $isFirst = $index === 0;

                if (!$isFirst && $taken === 0) {
                    $class->delete();
                    continue;
                }

                $kept->push(['model' => $class, 'taken' => $taken]);
            }

            if ($kept->isEmpty()) {
                static::create([
                    'faculty_id' => $facultyId,
                    'name' => 'Class A',
                    'letter' => 'A',
                    'capacity' => self::CAPACITY,
                    'status' => 'open',
                    'sort_order' => 1,
                ]);

                return static::where('faculty_id', $facultyId)->orderBy('sort_order')->get();
            }

            // Find first class that still has room — that one receives new students.
            $openAssigned = false;
            foreach ($kept as $row) {
                /** @var self $class */
                $class = $row['model'];
                $cap = $class->capacity ?: self::CAPACITY;
                $taken = $row['taken'];

                if ($taken >= $cap) {
                    $class->update(['status' => 'closed']);
                    continue;
                }

                if (!$openAssigned) {
                    $class->update(['status' => 'open']);
                    $openAssigned = true;
                } else {
                    // Later classes with students but still under capacity stay closed
                    // until earlier classes fill again — unless they already have students,
                    // keep them visible as closed so faculty can still open the tab.
                    $class->update(['status' => 'closed']);
                }
            }

            // If every kept class is full, open the next letter.
            if (!$openAssigned) {
                $last = $kept->last()['model'];
                static::openNextClass($facultyId, $last);
            }

            return static::where('faculty_id', $facultyId)->orderBy('sort_order')->get();
        });
    }

    /**
     * Ensure the faculty has classes in a consistent open/closed state.
     */
    public static function ensureForFaculty(int $facultyId)
    {
        return static::reconcileForFaculty($facultyId);
    }

    /**
     * Get an open class that still has room. Closes a full class and opens the next letter.
     */
    public static function claimSeat(int $facultyId): self
    {
        return DB::transaction(function () use ($facultyId) {
            static::reconcileForFaculty($facultyId);

            /** @var self|null $open */
            $open = static::where('faculty_id', $facultyId)
                ->where('status', 'open')
                ->orderBy('sort_order')
                ->lockForUpdate()
                ->first();

            if (!$open) {
                $last = static::where('faculty_id', $facultyId)->orderByDesc('sort_order')->first();
                $open = static::openNextClass($facultyId, $last);
                $open = static::where('id', $open->id)->lockForUpdate()->first();
            }

            if ($open->students()->lockForUpdate()->count() >= ($open->capacity ?: self::CAPACITY)) {
                $open->update(['status' => 'closed']);
                $open = static::openNextClass($facultyId, $open);
                $open = static::where('id', $open->id)->lockForUpdate()->first();
            }

            return $open;
        });
    }

    /**
     * After seat changes, re-sync class tab states from live counts.
     */
    public function syncCapacity(): void
    {
        static::reconcileForFaculty($this->faculty_id);
    }

    public static function openNextClass(int $facultyId, ?self $after = null): self
    {
        if (!$after) {
            return static::firstOrCreate(
                [
                    'faculty_id' => $facultyId,
                    'letter' => 'A',
                ],
                [
                    'name' => 'Class A',
                    'capacity' => self::CAPACITY,
                    'status' => 'open',
                    'sort_order' => 1,
                ]
            );
        }

        $nextLetter = static::nextLetter($after->letter);
        $sortOrder = ($after->sort_order ?? 0) + 1;

        $existing = static::where('faculty_id', $facultyId)
            ->where('letter', $nextLetter)
            ->first();

        if ($existing) {
            if ($existing->students()->count() < ($existing->capacity ?: self::CAPACITY)) {
                $existing->update(['status' => 'open']);
                return $existing->fresh();
            }

            return static::openNextClass($facultyId, $existing);
        }

        return static::create([
            'faculty_id' => $facultyId,
            'name' => 'Class ' . $nextLetter,
            'letter' => $nextLetter,
            'capacity' => self::CAPACITY,
            'status' => 'open',
            'sort_order' => $sortOrder,
        ]);
    }

    public static function nextLetter(string $letter): string
    {
        $letter = strtoupper($letter);
        $len = strlen($letter);
        $i = $len - 1;

        while ($i >= 0) {
            if ($letter[$i] !== 'Z') {
                $letter[$i] = chr(ord($letter[$i]) + 1);
                return $letter;
            }
            $letter[$i] = 'A';
            $i--;
        }

        return 'A' . $letter;
    }
}
