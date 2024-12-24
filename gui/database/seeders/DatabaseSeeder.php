<?php

namespace Scarlett\DMDD\GUI\Seeders;

use Scarlett\DMDD\GUI\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@dmdd.eu',
        ]);
    }
}
