<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One school term ("1st Sem 2026-2027"). The dean ends it from the Faculties page,
 * which archives that batch of students — see AcademicTermDesk::end().
 */
class AcademicTerm extends Model
{
    /** Days before ends_on that the dean starts seeing the reminder. */
    public const REMIND_DAYS = 7;

    protected $primaryKey = 'academic_term_id';

    protected $fillable = [
        'name',
        'ends_on',
        'ended_at',
        'ended_by',
    ];

    protected $casts = [
        'ends_on'  => 'date',
        'ended_at' => 'datetime',
    ];

    /** The term running now, or null when the dean has not started one. */
    public static function current(): ?self
    {
        return self::whereNull('ended_at')->latest('academic_term_id')->first();
    }

    /** Students archived with this term. */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'academic_term_id', 'academic_term_id')
            ->withoutGlobalScope(Student::CURRENT_SCOPE);
    }

    /** Whether the dean should be reminded to end it: the end date is near or past. */
    public function isDueToEnd(): bool
    {
        return $this->ended_at === null
            && $this->ends_on !== null
            && $this->ends_on->lte(now('Asia/Manila')->startOfDay()->addDays(self::REMIND_DAYS));
    }
}
