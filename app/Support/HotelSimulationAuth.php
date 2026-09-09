<?php

namespace App\Support;

use App\Models\HotelCustomer;
use App\Models\StudentGroup;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

/**
 * Hotel-website simulation auth: Staff vs Customer (browse/book).
 * Separate from HMS login — used inside the team's hotel template.
 * Redesign is gated by assigned department role in the builder, not Staff login.
 */
class HotelSimulationAuth
{
    public const SESSION_KEY = 'hotel_sim_auth';

    /**
     * "Remember me" on the guest sign-in.
     *
     * The session cookie dies with the browser, which is right for a shared lab
     * machine and wrong for the guest who ticked the box. This one carries the
     * account the session would have held, for thirty days, and `restore()` reads
     * it back when there is no session left. Laravel encrypts and signs it, so it
     * is no more forgeable than the session cookie beside it, and it holds an
     * account id rather than anything worth stealing on its own.
     */
    public const REMEMBER_COOKIE = 'hms_guest_remember';
    public const REMEMBER_DAYS = 30;

    public static function teamContext(User $user): ?array
    {
        $membership = HotelTemplateBuilder::membershipFor($user);
        if (!$membership) {
            return null;
        }

        return [
            'group_name' => (string) $membership->group_name,
            'faculty_id' => (int) $membership->faculty_id,
            'group_id' => $membership->group_id,
            'membership' => $membership,
        ];
    }

    public static function current(): ?array
    {
        $data = Session::get(self::SESSION_KEY);
        return is_array($data) ? $data : null;
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
        Cookie::queue(Cookie::forget(self::REMEMBER_COOKIE));
    }

    public static function payload(?array $auth = null): array
    {
        $auth = $auth ?? self::current();
        if (!$auth) {
            return [
                'authenticated' => false,
                'type' => null,
                'name' => null,
                'email' => null,
                'can_redesign' => false,
                'editable_pages' => [],
                'preferred_page' => 'home',
                'stay' => ['checked_in' => false, 'booking_id' => null, 'room' => null, 'guest_name' => null],
            ];
        }

        $type = $auth['type'] ?? null;
        $editable = $type === 'staff' ? ($auth['editable_pages'] ?? []) : [];

        return [
            'authenticated' => true,
            'type' => $type,
            'name' => $auth['name'] ?? null,
            'email' => $auth['email'] ?? null,
            'can_redesign' => $type === 'staff' && count($editable) > 0,
            'editable_pages' => $editable,
            'preferred_page' => $editable[0] ?? 'home',
            'role_label' => $auth['role_label'] ?? ($type === 'customer' ? 'Customer' : 'Staff'),
            /* The stay behind the account, when there is one. The Amenities page
               reads it: the pool, the spa and the function room are for the guests
               in the building, so the booking controls appear for somebody checked
               in and explain themselves to everybody else. */
            'stay' => $type === 'customer'
                ? HotelGuestStay::payload()
                : ['checked_in' => false, 'booking_id' => null, 'room' => null, 'guest_name' => null],
        ];
    }

    /**
     * Staff login: HMS email + password of a teammate (or self) on the same hotel team.
     */
    public static function loginStaff(User $viewer, string $email, string $password): array
    {
        $ctx = self::teamContext($viewer);
        if (!$ctx) {
            return ['ok' => false, 'error' => 'Join a hotel team first before staff login.', 'status' => 422];
        }

        $staffUser = User::whereEmail($email)->first();
        if (!$staffUser || !Hash::check($password, $staffUser->password)) {
            return ['ok' => false, 'error' => 'Invalid staff email or password.', 'status' => 401];
        }

        $student = $staffUser->student;
        if (!$student) {
            return ['ok' => false, 'error' => 'That account is not a student staff account.', 'status' => 422];
        }

        $membership = StudentGroup::with('roles')
            ->where('student_id', $student->user_information_id)
            ->where('group_name', $ctx['group_name'])
            ->where('faculty_id', $ctx['faculty_id'])
            ->first();

        if (!$membership) {
            return ['ok' => false, 'error' => 'That staff account is not on your hotel team.', 'status' => 403];
        }

        $roleKeys = $membership->roles->pluck('role')->filter()->values()->all();
        $editablePages = [];
        foreach ($roleKeys as $role) {
            $editablePages = array_merge($editablePages, HotelTemplateBuilder::editablePagesForRole($role));
        }
        $editablePages = array_values(array_unique($editablePages));

        $name = trim(implode(' ', array_filter([
            $staffUser->first_name,
            $staffUser->middle_name,
            $staffUser->last_name,
        ]))) ?: ($staffUser->name ?? 'Staff');

        $roleLabels = array_map(
            fn ($r) => HotelTemplateBuilder::ROLES[$r] ?? $r,
            $roleKeys
        );

        $auth = [
            'type' => 'staff',
            'user_id' => $staffUser->user_id,
            'student_id' => $student->user_information_id,
            'name' => $name,
            'email' => $staffUser->email,
            'editable_pages' => $editablePages,
            'roles' => $roleKeys,
            'role_label' => $roleLabels ? implode(', ', $roleLabels) : 'Staff',
            'group_name' => $ctx['group_name'],
            'faculty_id' => $ctx['faculty_id'],
        ];

        Session::put(self::SESSION_KEY, $auth);

        return ['ok' => true, 'auth' => self::payload($auth)];
    }

    /**
     * @param array{first_name: string, last_name: string, email: string, contact_number: string, password: string, id_document?: string|null} $details
     */
    public static function signupCustomer(User $viewer, array $details): array
    {
        $ctx = self::teamContext($viewer);
        if (!$ctx) {
            return ['ok' => false, 'error' => 'Join a hotel team first before creating a guest account.', 'status' => 422];
        }

        $firstName = trim($details['first_name'] ?? '');
        $lastName = trim($details['last_name'] ?? '');
        $contactNumber = trim($details['contact_number'] ?? '');
        $password = $details['password'] ?? '';

        // What the site greets the guest by, composed once here so every screen
        // that shows a name reads a single field.
        $name = trim($firstName . ' ' . $lastName);

        $email = strtolower(trim($details['email'] ?? ''));
        $exists = HotelCustomer::where('group_name', $ctx['group_name'])
            ->where('faculty_id', $ctx['faculty_id'])
            ->where('email', $email)
            ->exists();

        if ($exists) {
            return ['ok' => false, 'error' => 'A guest account with that email already exists for this hotel.', 'status' => 422];
        }

        // Block using a team staff email as customer (keeps roles clear)
        $staffEmail = User::whereEmail($email)->whereHas('student')->exists();
        if ($staffEmail) {
            $onTeam = StudentGroup::where('group_name', $ctx['group_name'])
                ->where('faculty_id', $ctx['faculty_id'])
                ->whereHas('student.user', fn ($q) => $q->whereEmail($email))
                ->exists();
            if ($onTeam) {
                return ['ok' => false, 'error' => 'That email belongs to hotel staff. Use Staff login instead.', 'status' => 422];
            }
        }

        $attributes = [
            'group_name' => $ctx['group_name'],
            'faculty_id' => $ctx['faculty_id'],
            'group_id' => $ctx['group_id'],
            'name' => $name,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'contact_number' => $contactNumber,
            'email' => $email,
            'password' => Hash::make($password),
        ];

        /* The ID arrives as a data-URL the browser already shrank, and is written
           to the media disk like every other picture: the column keeps the path.
           Stored against the account rather than checked here — verifying it is
           the front desk's job at check-in, and this is what they open to do it. */
        if (HotelCustomer::supportsIdDocument()) {
            $attributes['id_document'] = HotelImageStore::persist(
                $details['id_document'] ?? null,
                $ctx['faculty_id'],
                $ctx['group_name']
            );
        }

        $customer = HotelCustomer::create($attributes);

        $auth = [
            'type' => 'customer',
            'customer_id' => $customer->hotel_customer_id,
            'name' => $customer->name,
            'email' => $customer->email,
            'editable_pages' => [],
            'role_label' => 'Customer',
            'group_name' => $ctx['group_name'],
            'faculty_id' => $ctx['faculty_id'],
        ];

        Session::put(self::SESSION_KEY, $auth);

        return ['ok' => true, 'auth' => self::payload($auth)];
    }

    public static function loginCustomer(User $viewer, string $email, string $password, bool $remember = false): array
    {
        $ctx = self::teamContext($viewer);
        if (!$ctx) {
            return ['ok' => false, 'error' => 'Join a hotel team first.', 'status' => 422];
        }

        $customer = HotelCustomer::where('group_name', $ctx['group_name'])
            ->where('faculty_id', $ctx['faculty_id'])
            ->where('email', strtolower(trim($email)))
            ->first();

        if (!$customer || !Hash::check($password, $customer->password)) {
            return ['ok' => false, 'error' => 'Invalid guest email or password.', 'status' => 401];
        }

        $auth = [
            'type' => 'customer',
            'customer_id' => $customer->hotel_customer_id,
            'name' => $customer->name,
            'email' => $customer->email,
            'editable_pages' => [],
            'role_label' => 'Customer',
            'group_name' => $ctx['group_name'],
            'faculty_id' => $ctx['faculty_id'],
        ];

        Session::put(self::SESSION_KEY, $auth);
        self::rememberCustomer($customer->hotel_customer_id, $remember);

        return ['ok' => true, 'auth' => self::payload($auth)];
    }

    /** Write or clear the thirty-day cookie behind "remember me". */
    public static function rememberCustomer(int $customerId, bool $remember): void
    {
        if (!$remember) {
            Cookie::queue(Cookie::forget(self::REMEMBER_COOKIE));

            return;
        }

        Cookie::queue(Cookie::make(
            self::REMEMBER_COOKIE,
            (string) $customerId,
            self::REMEMBER_DAYS * 24 * 60,
            null,
            null,
            null,
            true,  // httpOnly: no script has any use for it
            false,
            'Lax'
        ));
    }

    /**
     * Bring a remembered guest back after their session has gone.
     *
     * Team-scoped like everything else: the cookie names an account, and the
     * account still has to belong to the hotel being looked at, or a guest of one
     * team would be signed into another team's site by a cookie neither of them
     * knows about.
     */
    public static function restore(?User $viewer): void
    {
        if (self::current() || !$viewer) {
            return;
        }

        $customerId = (int) Request::cookie(self::REMEMBER_COOKIE);
        if (!$customerId) {
            return;
        }

        $ctx = self::teamContext($viewer);
        if (!$ctx) {
            return;
        }

        $customer = HotelCustomer::where('hotel_customer_id', $customerId)
            ->where('group_name', $ctx['group_name'])
            ->where('faculty_id', $ctx['faculty_id'])
            ->first();

        if (!$customer) {
            Cookie::queue(Cookie::forget(self::REMEMBER_COOKIE));

            return;
        }

        Session::put(self::SESSION_KEY, [
            'type' => 'customer',
            'customer_id' => $customer->hotel_customer_id,
            'name' => $customer->name,
            'email' => $customer->email,
            'editable_pages' => [],
            'role_label' => 'Customer',
            'group_name' => $ctx['group_name'],
            'faculty_id' => $ctx['faculty_id'],
        ]);
    }
}
