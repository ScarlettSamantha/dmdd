<?php
namespace Database\Seeders;

use Scarlett\DMDD\GUI\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        // Create a default admin user
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@dmdd.eu',
            'password' => Hash::make('dmdd-changeme-password-935372095'), 
        ]);
    }
}
