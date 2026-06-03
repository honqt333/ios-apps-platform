<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        $super = User::firstOrCreate(
            ['email' => 'admin@platform.local'],
            [
                'name'      => 'Super Admin',
                'username'  => 'admin',
                'password'  => Hash::make('password'),
                'is_active' => true,
                'locale'    => 'en',
            ]
        );
        $super->assignRole('super-admin');

        // Demo admin
        $admin = User::firstOrCreate(
            ['email' => 'manager@platform.local'],
            [
                'name'      => 'Demo Manager',
                'username'  => 'manager',
                'password'  => Hash::make('password'),
                'is_active' => true,
                'locale'    => 'en',
            ]
        );
        $admin->assignRole('admin');

        // Editor
        $editor = User::firstOrCreate(
            ['email' => 'editor@platform.local'],
            [
                'name'      => 'Demo Editor',
                'username'  => 'editor',
                'password'  => Hash::make('password'),
                'is_active' => true,
                'locale'    => 'ar',
            ]
        );
        $editor->assignRole('editor');
    }
}
