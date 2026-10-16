<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Models\Faculty;
use App\Models\Student;


class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Every table names its key after what it identifies, so Eloquent's "id" default
     * does not apply. Authenticatable picks this up on its own —
     * getAuthIdentifierName() returns the key name — but relationships do not:
     * getForeignKey() would build "user_user_id", so every relation below spells its
     * keys out.
     */
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'first_name',
        'middle_name',
        'last_name',
        'phone_number',
        'status',
        'email_verified_at',
        'avatar',
        'last_seen_at',
        'must_change_password',
    ];

    /**
     * Normalize optional text: empty / "None" / "null" become "" (avoid SQL NULL).
     */
    public static function cleanOptional(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '' || strcasecmp($value, 'none') === 0 || strcasecmp($value, 'null') === 0) {
            return '';
        }

        return $value;
    }

    /**
     * Phone number as stored: the 10 digits after +63 ("9123456789"), which is what
     * the forms show beside their +63 label. Spaces, dashes and a leading +63 / 63 / 0
     * are dropped; empty stays "" (see cleanOptional()).
     */
    public static function normalizePhone(?string $value): string
    {
        $digits = preg_replace('/\D/', '', static::cleanOptional($value));

        if (strlen($digits) === 12 && str_starts_with($digits, '63')) {
            return substr($digits, 2);
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return substr($digits, 1);
        }

        return $digits;
    }

    /** @deprecated Use cleanOptional() — kept so older calls still avoid NULL. */
    public static function blankToNull(?string $value): string
    {
        return static::cleanOptional($value);
    }

    /**
     * Store emails lowercased. MySQL's utf8mb4_unicode_ci matched them
     * case-insensitively for free; PostgreSQL does not, so "A@x.com" and
     * "a@x.com" would otherwise become two accounts that both pass the unique
     * index. hotel_customers already normalizes this way — see HotelSimulationAuth.
     */
    public function setEmailAttribute(?string $value): void
    {
        $this->attributes['email'] = strtolower(trim((string) $value));
    }

    /**
     * Look a user up by email. Normalizes the needle the same way the mutator
     * normalizes the stored value, so lookups keep working regardless of how the
     * address was typed.
     */
    public function scopeWhereEmail($query, ?string $email)
    {
        return $query->where('email', strtolower(trim((string) $email)));
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<int, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'must_change_password' => 'boolean',
    ];

    /**
     * Profile image URL, or the generic blank-person picture when none is uploaded.
     */
    public function getAvatarUrlAttribute(): string
    {
        if (!empty($this->avatar)) {
            return \App\Support\HotelImageStore::url($this->avatar);
        }

        return asset('images/default-avatar.svg');
    }

    /**
     * Phone number in the Philippine mobile format, "+63 912 345 6789". Stored
     * numbers come as "9123456789" (the form shows +63 beside the box), "09123456789"
     * (spreadsheets) or "+639123456789"; anything that is not a PH mobile is shown as typed.
     */
    public function getPhoneDisplayAttribute(): string
    {
        $raw = trim((string) $this->phone_number);
        $digits = preg_replace('/\D/', '', $raw);

        if (preg_match('/^(?:63|0)?(9\d{2})(\d{3})(\d{4})$/', $digits, $m)) {
            return "+63 {$m[1]} {$m[2]} {$m[3]}";
        }

        return $raw;
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->email_verified_at)) {
                $user->email_verified_at = now();
            }
            if (empty($user->remember_token)) {
                $user->remember_token = Str::random(60);
            }
        });

        static::updating(function (User $user) {
            if ($user->email_verified_at === null) {
                $user->email_verified_at = now();
            }
        });
    }

    public function faculty()
    {
        return $this->hasOne(Faculty::class, 'user_id', 'user_id');
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'user_id', 'user_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to', 'user_id');
    }

    /**
     * Which portal this account belongs to — 'dean', 'faculty', 'student' — or null
     * for none. Read from the account itself, never the URL: EnsureRole gates every
     * portal's routes on it, and sign-in and the guest pages use it to pick the
     * dashboard. Older rows without a role are known by their profile row.
     */
    public function portal(): ?string
    {
        if ($this->role === 'dean') {
            return 'dean';
        }
        if ($this->role === 'faculty' || $this->faculty) {
            return 'faculty';
        }
        if ($this->role === 'student' || $this->student) {
            return 'student';
        }

        return null;
    }

    /**
     * A faculty account the dean has created but its owner has not yet signed in to.
     * Unlike 'pending' (a student waiting for approval), it does not stop a sign-in:
     * the first one, with the emailed temporary password, is what activates it.
     */
    public const PENDING_ACTIVATION = 'pending_activation';

    /** First sign-in on a Pending Activation account: mark it, and its faculty row, Active. */
    public function activate(): void
    {
        $this->forceFill(['status' => 'active'])->save();
        $this->faculty?->forceFill(['status' => 'active'])->save();
    }

    /** Route name of this account's own dashboard, or null when it has no portal. */
    public function dashboardRoute(): ?string
    {
        return [
            'dean' => 'dean.dashboard',
            'faculty' => 'faculty.dashboard',
            'student' => 'students.dashboard',
        ][$this->portal()] ?? null;
    }

}
