<?php
namespace Scarlett\DMDD\GUI\Seeders;

use Scarlett\DMDD\GUI\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeders extends Seeder
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
