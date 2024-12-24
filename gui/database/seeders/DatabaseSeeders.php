<?php
namespace Scarlett\DMDD\GUI\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeders extends Seeder
{
    public function run(): void
    {
        // Create a default admin user
        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'admin@dmdd.eu',
            'password' => Hash::make('dmdd-changeme-password-935372095'),
        ]);
    }
}
