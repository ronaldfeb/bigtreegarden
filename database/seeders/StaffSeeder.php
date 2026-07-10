<?php

namespace Database\Seeders;

use App\Enums\StaffRole;
use App\Models\StaffUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Staff Admin',
            'email' => 'staff@bigtreegarden.test',
        ]);

        StaffUser::query()->create([
            'user_id' => $user->id,
            'role' => StaffRole::Admin,
            'job_title' => 'Platform Administrator',
            'is_active' => true,
        ]);
    }
}
