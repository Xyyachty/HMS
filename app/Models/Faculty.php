<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

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
     * Every block in use: each faculty's own block plus any next-block tab a
     * faculty opened because nobody held that block (see handledBlocks()).
     *
     * This used to list the raw faculty_classes letters, which restart at A for
     * every faculty, so Block B never appeared: a Block B faculty's Update User
     * select came up blank and refused to save, and their students read "—".
     */
    public static function existingClassLetters(): array
    {
        $letters = static::with('classes')
            ->get()
            ->flatMap(fn (self $faculty) => $faculty->handledBlocks())
            ->unique()
            ->sort()
            ->values()
            ->all();

        // Block A is always the first — keep it selectable before any faculty exists.
        if ($letters === []) {
            return ['A'];
        }

        return $letters;
    }

    /**
     * The blocks this faculty runs: their own, plus the next block when it filled
     * into a tab of theirs because no faculty held it (Block A, Block B).
     */
    public function handledBlocks(): array
    {
        $own = strtoupper(trim((string) $this->block));
        if ($own === '') {
            return [];
        }

        // toBase(): with no classes yet (a faculty the dean has just created) map()
        // keeps the Eloquent collection, and unique() on it then calls getKey() on
        // the pushed letter - a 500 on every page that lists blocks.
        return $this->classes
            ->toBase()
            ->map(fn (FacultyClass $class) => $class->setRelation('faculty', $this)->block_letter)
            ->push($own)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /** "Block A, Block B", or "—" with no block assigned. */
    public function handledBlocksLabel(): string
    {
        return implode(', ', array_map(fn ($b) => 'Block ' . $b, $this->handledBlocks())) ?: '—';
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

    /** Blocks no faculty runs (optionally ignoring one faculty's own). */
    public static function availableBlocks(?int $exceptFacultyId = null): array
    {
        $existing = static::existingClassLetters();

        // A block run through another faculty's extra tab is taken too, or a new
        // faculty would be handed a block that already has students in it.
        $taken = static::with('classes')
            ->when($exceptFacultyId, fn ($q) => $q->where('user_information_id', '!=', $exceptFacultyId))
            ->get()
            ->flatMap(fn (self $faculty) => $faculty->handledBlocks())
            ->all();

        return array_values(array_filter(
            $existing,
            fn ($letter) => !in_array($letter, $taken, true)
        ));
    }

    /**
     * The block a faculty being created should get.
     *
     * The dean is not asked for it: a block is only ever "the next free letter",
     * so there was nothing to decide and a wrong answer stopped the account
     * being made at all. The first unused class letter, or a new one past the
     * last if every existing letter is spoken for.
     */
    public static function nextAvailableBlock(): string
    {
        $free = static::availableBlocks();
        if ($free !== []) {
            return $free[0];
        }

        $letters = static::existingClassLetters();
        $taken = static::query()
            ->whereNotNull('block')
            ->where('block', '!=', '')
            ->pluck('block')
            ->map(fn ($b) => strtoupper((string) $b))
            ->all();

        $all = array_unique(array_merge($letters, $taken));
        sort($all);

        return FacultyClass::nextLetter((string) (end($all) ?: 'A'));
    }

    /**
     * Why this faculty cannot add students or create teams yet, or null when they can.
     *
     * Blocks fill in order: Block B opens only once the faculty holding Block A has a
     * full class (FacultyClass::CAPACITY students), Block C once Block B is full, and
     * so on. Block A is always open.
     */
    public function intakeLockReason(): ?string
    {
        $block = strtoupper(trim((string) $this->block));
        if ($block === '') {
            return 'No block is assigned to your account yet. Ask the dean to assign one.';
        }
        if ($block === 'A') {
            return null;
        }

        // nextLetter() has no inverse, so walk up from A. Bounded in case of bad data.
        $previous = 'A';
        for ($i = 0; $i < 1000 && FacultyClass::nextLetter($previous) !== $block; $i++) {
            $previous = FacultyClass::nextLetter($previous);
        }

        $previousFaculty = static::whereRaw('UPPER(block) = ?', [$previous])->first();
        $taken = $previousFaculty ? $previousFaculty->students()->count() : 0;
        if ($taken >= FacultyClass::CAPACITY) {
            return null;
        }

        return 'Block ' . $block . ' is not open yet. Block ' . $previous . ' has ' . $taken
            . ' of ' . FacultyClass::CAPACITY . ' students. You can add students and create teams once Block '
            . $previous . ' is full.';
    }

    /** The faculty holding the block after this one (Block B after Block A), if any. */
    public function nextBlockFaculty(): ?self
    {
        $block = strtoupper(trim((string) $this->block));
        if ($block === '') {
            return null;
        }

        return static::with('user')
            ->whereRaw('UPPER(block) = ?', [FacultyClass::nextLetter($block)])
            ->first();
    }

    /**
     * Who a new student is enrolled under.
     *
     * This faculty while their block has room, or when no faculty holds the next
     * block (they then keep enrolling into their own next block tab). Once full
     * with a faculty on the next block, the student goes to that faculty instead,
     * and on down the chain if that block is full too.
     */
    public function intakeFaculty(): self
    {
        $faculty = $this;
        for ($i = 0; $i < 26; $i++) {
            $next = $faculty->nextBlockFaculty();
            if (!$next || $faculty->students()->count() < FacultyClass::CAPACITY) {
                break;
            }
            $faculty = $next;
        }

        return $faculty;
    }

    /**
     * Extra blocks a faculty runs past their own (Block B in Block A's tabs, opened
     * because no faculty held it) that hold students, keyed by block letter. The
     * dean can hand one to another faculty; see FacultyClass::handOverTo().
     *
     * @return array<string, array{class_id:int, from_id:int, from_user_id:int, from_name:string, students:int}>
     */
    public static function handOverBlocks(): array
    {
        $blocks = [];
        foreach (static::with(['classes', 'user'])->get() as $faculty) {
            $own = strtoupper(trim((string) $faculty->block));
            foreach ($faculty->classes as $class) {
                $letter = $class->setRelation('faculty', $faculty)->block_letter;
                $students = $class->students()->count();
                if ($letter === $own || $students === 0) {
                    continue;
                }
                $blocks[$letter] = [
                    'class_id' => (int) $class->faculty_class_id,
                    'from_id' => (int) $faculty->user_information_id,
                    'from_user_id' => (int) $faculty->user_id,
                    'from_name' => (string) ($faculty->user?->name ?? 'another faculty'),
                    'students' => $students,
                ];
            }
        }
        ksort($blocks);

        return $blocks;
    }

    /**
     * Hand everything this faculty runs - every block tab, its students, their
     * teams and tasks - to another faculty, before this one is set Inactive.
     *
     * The receiver keeps what they have and gains these blocks as extra tabs.
     * Tab letters are read against the owner's own block (FacultyClass::block_letter),
     * so the receiver's own block becomes the lowest block they now hold and every
     * tab is re-lettered from it. This faculty is left with no block; Update User
     * gives them the next free one if they are made Active again.
     *
     * @throws \RuntimeException with a message fit for the dean when it cannot move.
     */
    public function transferEverythingTo(self $to): void
    {
        $fromId = (int) $this->user_information_id;
        $toId = (int) $to->user_information_id;

        if ($fromId === $toId) {
            throw new \RuntimeException('Choose a different faculty to take over.');
        }

        DB::transaction(function () use ($fromId, $toId, $to) {
            // Every tab of both, by the block it really is. Empty ones give way.
            $owners = [$fromId => $this, $toId => $to];
            $blocks = [];
            $classes = FacultyClass::whereIn('faculty_id', [$fromId, $toId])->withCount('students')->get();
            foreach ($classes as $class) {
                $block = $class->setRelation('faculty', $owners[(int) $class->faculty_id])->block_letter;
                if ($class->students_count === 0) {
                    $class->delete();
                    continue;
                }
                if (isset($blocks[$block])) {
                    throw new \RuntimeException('Both faculty have students in Block ' . $block . '.');
                }
                $blocks[$block] = $class;
            }
            if ($blocks === []) {
                return;
            }

            // Teams are keyed by name per faculty; a name the receiver already uses
            // gets this block added to it ("Team 1 (Block B)").
            $taken = DB::table('groups')->where('faculty_id', $toId)->pluck('group_name')->all();
            $teams = DB::table('groups')->where('faculty_id', $fromId)->pluck('group_name')
                ->merge(DB::table('student_groups')->where('faculty_id', $fromId)->pluck('group_name'))
                ->unique();
            $tables = Group::teamOwnedTables();
            foreach ($teams as $team) {
                if (!in_array($team, $taken, true)) {
                    $taken[] = $team;
                    continue;
                }
                $base = $team . ' (Block ' . $this->block . ')';
                for ($renamed = $base, $n = 2; in_array($renamed, $taken, true); $n++) {
                    $renamed = $base . ' ' . $n;
                }
                foreach ($tables as $name) {
                    DB::table($name)->where('faculty_id', $fromId)->where('group_name', $team)->update(['group_name' => $renamed]);
                }
                $taken[] = $renamed;
            }

            Student::where('faculty_id', $fromId)->update(['faculty_id' => $toId]);
            foreach ($tables as $name) {
                DB::table($name)->where('faculty_id', $fromId)->update(['faculty_id' => $toId]);
            }

            uksort($blocks, fn ($a, $b) => static::letterIndex($a) <=> static::letterIndex($b));
            $own = array_key_first($blocks);

            // block is unique: free this faculty's before the receiver takes it.
            $this->update(['block' => null]);
            $to->update(['block' => $own]);

            // (faculty_id, letter) is unique, so park every tab before re-lettering.
            foreach ($blocks as $class) {
                $class->update(['faculty_id' => $toId, 'letter' => '#' . $class->faculty_class_id]);
            }
            $order = 0;
            foreach ($blocks as $block => $class) {
                $class->update([
                    'letter' => static::letterAt(static::letterIndex($block) - static::letterIndex($own)),
                    'sort_order' => ++$order,
                    'name' => 'Block ' . $block,
                ]);
            }

            // ponytail: a faculty whose block sits in a gap here (B, when the receiver
            // now holds A and C) can still open a C tab once full; guard openNextClass if that happens.
            FacultyClass::reconcileForFaculty($toId);
        });
    }

    /** A=0 ... Z=25, AA=26: the same order FacultyClass::nextLetter() walks. */
    private static function letterIndex(string $letter): int
    {
        $n = 0;
        foreach (str_split(strtoupper($letter)) as $char) {
            $n = $n * 26 + (ord($char) - 64);
        }

        return $n - 1;
    }

    private static function letterAt(int $index): string
    {
        $letter = '';
        for ($index++; $index > 0; $index = intdiv($index - 1, 26)) {
            $letter = chr(65 + ($index - 1) % 26) . $letter;
        }

        return $letter;
    }

    /** Options for a faculty update dropdown: available class letters + their current block. */
    public static function selectableBlocksForFaculty(?int $facultyId = null, ?string $currentBlock = null): array
    {
        // Not the extra blocks this faculty already runs: moving their own block onto
        // one of those would relabel every tab they have.
        $extra = $facultyId ? (static::with('classes')->find($facultyId)?->handledBlocks() ?? []) : [];
        $options = array_values(array_diff(static::availableBlocks($facultyId), $extra));
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
