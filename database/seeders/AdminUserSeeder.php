<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrNew([
            'email' => 'sm.mou0137@gmail.com',
        ]);

        $admin->name = 'Artfy Admin';
        $admin->email = 'sm.mou0137@gmail.com';
        $admin->password = Hash::make('12345678');
        $admin->role = 'admin';
        $admin->membership_status = 'not_applicable';

        $admin->save();

        $this->command->info('Artfy Admin account created or updated successfully.');
    }
}
