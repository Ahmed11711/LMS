<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    { {
            DB::connection('LMS_CENTER')->table('users')->updateOrInsert(
                ['email' => 'superadmin@gmail.com'],
                [
                    'name' => 'Super Admin',
                    'password' => bcrypt('12345678'),
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
