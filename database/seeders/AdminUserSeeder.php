<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'Jome Alawuru',
            'email' => 'jomealawuru@hotmail.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: jomealawuru@hotmail.com');
        $this->command->info('Password: password123');
    }
}