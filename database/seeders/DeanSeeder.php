<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DeanSeeder extends Seeder
{
    /**
     * Create the Dean account.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'dean@hms.edu'],
            [
                'name'         => 'Dean',
                'first_name'   => 'System',
                'middle_name'  => '',
                'last_name'    => 'Dean',
                'email'        => 'dean@hms.edu',
                'password'     => Hash::make('Dean@1234'),
                'role'         => 'dean',
                'phone_number' => '',
                'status'       => 'active',
                'email_verified_at' => now(),
            ]
        );
    }
}
