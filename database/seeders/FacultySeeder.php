<?php

namespace Database\Seeders;

use App\Models\Faculty;
use App\Models\FacultyClass;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FacultySeeder extends Seeder
{
    /**
     * Create the demo Faculty account, mirroring DeanController::storeFaculty:
     * a users row plus its faculty row of user_information, then the Class A tab
     * that Manage Students would otherwise create on first open.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'faculty@hms.edu'],
            [
                'name'              => 'System Faculty',
                'first_name'        => 'System',
                'middle_name'       => '',
                'last_name'         => 'Faculty',
                'email'             => 'faculty@hms.edu',
                'password'          => Hash::make('Faculty@1234'),
                'role'              => 'faculty',
                'phone_number'      => '',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        $faculty = Faculty::updateOrCreate(
            ['user_id' => $user->user_id],
            [
                'phone_number' => '',
                'status'       => 'active',
                'block'        => 'A',
            ]
        );

        FacultyClass::ensureForFaculty((int) $faculty->user_information_id);
    }
}
