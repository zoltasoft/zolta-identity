<?php

namespace Database\Seeders\UserManagementService;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $providers = [
            ['id' => Str::uuid(), 'social_provider' => 'google', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'social_provider' => 'microsoft', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'social_provider' => 'github', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('social_providers')->insert($providers);
    }
}
