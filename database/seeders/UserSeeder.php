<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User([
            'firstname' => 'Super',
            'middlename' => '',
            'lastname' => 'Admin',
            'email' => 'admin@admin',
            'email_verified_at' => now(),
            'password' => Hash::make('p4ssw0rd'), // password
            'remember_token' => Str::random(10),
        ]);
        $user->save();

        $user->assignRole('superadmin');
    }
}
