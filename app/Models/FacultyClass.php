<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FacultyClass extends Model
{
    public const CAPACITY = 40;

    protected $primaryKey = 'faculty_class_id';

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
        return $this->belongsTo(Faculty::class, 'faculty_id', 'user_information_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'faculty_class_id', 'faculty_class_id');
    }

    /**
     * The block this tab is, as faculty and students see it.
     *
     * Tabs are lettered per faculty from A, but a faculty's first tab is their own
     * block: a Block B faculty's tab "A" is Block B, and the tab after it, opened
     * when no faculty holds the next block, is Block C. The stored letter stays
     * as it is, since URLs and filters key on it.
     */
    public function getBlockLetterAttribute(): string
    {
        $letter = strtoupper(trim((string) $this->letter)) ?: 'A';
        $block = strtoupper(trim((string) $this->faculty?->block));
        if ($block === '' || $block === 'A') {
            return $letter;
        }

        // nextLetter() has no inverse, so step both up together. Bounded for bad data.
        for ($walk = 'A', $i = 0; $i < 1000 && $walk !== $letter; $i++) {
            $walk = static::nextLetter($walk);
            $block = static::nextLetter($block);
        }

        return $block;
    }

    /** Always "Block X", whatever older rows stored ("Class A"). */
    public function getNameAttribute(): string
    {
        return 'Block ' . $this->block_letter;
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
                    'name' => 'Block A',
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
                    'name' => 'Block A',
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

            // If every kept class is full, open the next letter — unless another
            // faculty holds the next block, which takes over from here instead.
            // See Faculty::intakeFaculty().
            if (!$openAssigned && !Faculty::find($facultyId)?->nextBlockFaculty()) {
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
                $open = static::where('faculty_class_id', $open->faculty_class_id)->lockForUpdate()->first();
            }

            // Counted without a row lock on purpose. PostgreSQL rejects
            // "SELECT count(*) ... FOR UPDATE" outright ("FOR UPDATE is not allowed
            // with aggregate functions") where MySQL simply allowed it. The lock is
            // not needed anyway: the class row itself is already locked above, and
            // that is what serialises two students claiming the last seat at once.
            if ($open->students()->count() >= ($open->capacity ?: self::CAPACITY)) {
                $open->update(['status' => 'closed']);
                $open = static::openNextClass($facultyId, $open);
                $open = static::where('faculty_class_id', $open->faculty_class_id)->lockForUpdate()->first();
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
                    'name' => 'Block A',
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
            // "Block", matching Faculty::blockLabel(). The grouping had two names
            // for one thing: a faculty's block letter and this row's class letter
            // were always the same letter.
            'name' => 'Block ' . $nextLetter,
            'letter' => $nextLetter,
            'capacity' => self::CAPACITY,
            'status' => 'open',
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Hand this block, with its students and their teams, to another faculty. Used
     * when a faculty ran the next block only because nobody held it, and the dean
     * has since made a faculty for it.
     *
     * The receiving faculty must have no students yet: their own block letter
     * becomes this block's, and this row becomes their first tab. Every team-owned
     * row (each table carrying faculty_id and group_name) follows its team.
     *
     * @throws \RuntimeException with a message fit for the dean when it cannot move.
     */
    public function handOverTo(Faculty $to): void
    {
        $fromId = (int) $this->faculty_id;
        $toId = (int) $to->user_information_id;
        $block = $this->block_letter;

        if ($fromId === $toId) {
            throw new \RuntimeException('Block ' . $block . ' is already theirs.');
        }
        if ($to->students()->exists()) {
            throw new \RuntimeException('Block ' . $block . ' can only go to a faculty with no students yet.');
        }

        DB::transaction(function () use ($fromId, $toId, $to, $block) {
            $studentIds = $this->students()->pluck('user_information_id')->all();

            $teams = DB::table('student_groups')
                ->where('faculty_id', $fromId)
                ->whereIn('student_id', $studentIds)
                ->distinct()
                ->pluck('group_name')
                ->all();

            // A team is moved whole, so it must sit entirely inside this block.
            $mixed = DB::table('student_groups')
                ->where('faculty_id', $fromId)
                ->whereIn('group_name', $teams)
                ->whereNotIn('student_id', $studentIds)
                ->value('group_name');
            if ($mixed !== null) {
                throw new \RuntimeException('Team "' . $mixed . '" has students in Block ' . $block
                    . ' and in another block. Move them into one block first.');
            }

            $clash = DB::table('groups')->where('faculty_id', $toId)->whereIn('group_name', $teams)->value('group_name');
            if ($clash !== null) {
                throw new \RuntimeException('The receiving faculty already has a team named "' . $clash . '".');
            }

            // Their empty tabs give way; this block becomes their first tab.
            static::where('faculty_id', $toId)->delete();
            $this->update(['faculty_id' => $toId, 'letter' => 'A', 'sort_order' => 1, 'name' => 'Block ' . $block]);

            Student::whereIn('user_information_id', $studentIds)->update(['faculty_id' => $toId]);

            if ($teams !== []) {
                // On PostgreSQL getTables() lists every schema (Supabase's auth and
                // storage too); only the app's own tables are team-owned.
                $schema = DB::getDriverName() === 'pgsql' ? DB::scalar('select current_schema()') : null;
                foreach (\Illuminate\Support\Facades\Schema::getTables() as $table) {
                    if ($schema !== null && ($table['schema'] ?? null) !== $schema) {
                        continue;
                    }
                    $name = $table['name'];
                    $columns = \Illuminate\Support\Facades\Schema::getColumnListing($name);
                    if (in_array('faculty_id', $columns, true) && in_array('group_name', $columns, true)) {
                        DB::table($name)->where('faculty_id', $fromId)->whereIn('group_name', $teams)
                            ->update(['faculty_id' => $toId]);
                    }
                }
            }

            // A student's own tasks outside any team follow them too.
            DB::table('tasks')->where('faculty_id', $fromId)->whereNull('group_name')
                ->whereIn('student_id', $studentIds)->update(['faculty_id' => $toId]);

            $to->update(['block' => $block]);

            static::reconcileForFaculty($fromId);
            static::reconcileForFaculty($toId);
        });
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
