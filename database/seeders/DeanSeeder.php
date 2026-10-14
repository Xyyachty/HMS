<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DeanSeeder extends Seeder
{
    /**
     * Create the Dean account, only when no dean exists yet.
     *
     * The container runs db:seed on every deploy. Updating an existing dean here
     * would reset the name, email, phone and password the dean edited on their
     * profile, and a changed email would spawn a second "System Dean" account.
     */
    public function run(): void
    {
        if (User::where('role', 'dean')->exists()) {
            return;
        }

        User::create([
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
        ]);
    }
}
