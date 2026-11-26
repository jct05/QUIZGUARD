<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Preset Admin, after migration, use seed command to set the admin account
        // firstOrCreate method checks if there is already an email like the one under
        // if there are none it will create the row
        // if there is it will not do anything
        User::firstOrCreate(
            ['dwcc_email' => 'johncarlo.tolentino@dwcc.edu.ph'], // Checks if this email already exists
            [
                'role' => 'admin',
                'school_id_num' => 47696,
                'first_name' => 'John Carlo',
                'last_name' => 'Tolentino',
                'password_hash' => Hash::make('password'),
                'department' => 'IT/Admin',
                'status' => 'active',
            ]
        );
    }
}
