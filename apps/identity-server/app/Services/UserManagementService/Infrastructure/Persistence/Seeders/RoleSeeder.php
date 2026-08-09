<?php

namespace App\Services\UserManagementService\Infrastructure\Persistence\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'id' => Str::uuid(),
                'role' => 'Admin',
            ],
            [
                'id' => Str::uuid(),
                'role' => 'User',
            ],
        ]);
    }
}
