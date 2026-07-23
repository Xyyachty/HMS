<?php

namespace App\Support;

use App\Models\HotelCustomer;
use App\Models\StudentGroup;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

/**
 * Hotel-website simulation auth: Staff vs Customer (browse/book).
 * Separate from HMS login — used inside the team's hotel template.
 * Redesign is gated by assigned department role in the builder, not Staff login.
 */
class HotelSimulationAuth
{
    public const SESSION_KEY = 'hotel_sim_auth';

    public static function teamContext(User $user): ?array
    {
        $membership = HotelTemplateBuilder::membershipFor($user);
        if (!$membership) {
            return null;
        }

        return [
            'group_name' => (string) $membership->group_name,
            'faculty_id' => (int) $membership->faculty_id,
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

        $staffUser = User::where('email', $email)->first();
        if (!$staffUser || !Hash::check($password, $staffUser->password)) {
            return ['ok' => false, 'error' => 'Invalid staff email or password.', 'status' => 401];
        }

        $student = $staffUser->student;
        if (!$student) {
            return ['ok' => false, 'error' => 'That account is not a student staff account.', 'status' => 422];
        }

        $membership = StudentGroup::with('roles')
            ->where('student_id', $student->id)
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
            'user_id' => $staffUser->id,
            'student_id' => $student->id,
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

    public static function signupCustomer(User $viewer, string $name, string $email, string $password): array
    {
        $ctx = self::teamContext($viewer);
        if (!$ctx) {
            return ['ok' => false, 'error' => 'Join a hotel team first before creating a guest account.', 'status' => 422];
        }

        $email = strtolower(trim($email));
        $exists = HotelCustomer::where('group_name', $ctx['group_name'])
            ->where('faculty_id', $ctx['faculty_id'])
            ->where('email', $email)
            ->exists();

        if ($exists) {
            return ['ok' => false, 'error' => 'A guest account with that email already exists for this hotel.', 'status' => 422];
        }

        // Block using a team staff email as customer (keeps roles clear)
        $staffEmail = User::where('email', $email)->whereHas('student')->exists();
        if ($staffEmail) {
            $onTeam = StudentGroup::where('group_name', $ctx['group_name'])
                ->where('faculty_id', $ctx['faculty_id'])
                ->whereHas('student.user', fn ($q) => $q->where('email', $email))
                ->exists();
            if ($onTeam) {
                return ['ok' => false, 'error' => 'That email belongs to hotel staff. Use Staff login instead.', 'status' => 422];
            }
        }

        $customer = HotelCustomer::create([
            'group_name' => $ctx['group_name'],
            'faculty_id' => $ctx['faculty_id'],
            'name' => trim($name),
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $auth = [
            'type' => 'customer',
            'customer_id' => $customer->id,
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

    public static function loginCustomer(User $viewer, string $email, string $password): array
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
            'customer_id' => $customer->id,
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
}
