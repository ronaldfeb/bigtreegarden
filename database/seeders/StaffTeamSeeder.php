<?php

namespace Database\Seeders;

use App\Enums\StaffRole;
use App\Models\StaffUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class StaffTeamSeeder extends Seeder
{
    /**
     * @var list<array{name: string, email: string, password: string}>
     */
    private const STAFF = [
        [
            'name' => 'Graham Abrahams',
            'email' => 'graham.abrahams@bigtreegarden.com',
            'password' => '!@#GrahamAbrahams123',
        ],
        [
            'name' => 'Tiro Mathe',
            'email' => 'tiro.mathe@bigtreegarden.com',
            'password' => '!@#TiroMathe123',
        ],
        [
            'name' => 'Kealeboga Sibiya',
            'email' => 'kealeboga.sibiya@bigtreegarden.com',
            'password' => '!@#KealebogaSibiya123',
        ],
        [
            'name' => 'Lynn Meyers',
            'email' => 'lynn.meyers@bigtreegarden.com',
            'password' => '!@#LynnMeyers123',
        ],
        [
            'name' => 'Noxolo Ngqoyana',
            'email' => 'noxolo.ngqoyana@bigtreegarden.com',
            'password' => '!@#NoxoloNgqoyana123',
        ],
        [
            'name' => 'Eddie Kampher',
            'email' => 'eddie.kampher@bigtreegarden.com',
            'password' => '!@#EddieKampher123',
        ],
    ];

    public function run(): void
    {
        foreach (self::STAFF as $staff) {
            $user = User::query()->updateOrCreate(
                ['email' => $staff['email']],
                [
                    'name' => $staff['name'],
                    'password' => $staff['password'],
                    'email_verified_at' => now(),
                ],
            );

            StaffUser::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'role' => StaffRole::Admin,
                    'job_title' => 'Platform Administrator',
                    'is_active' => true,
                ],
            );
        }
    }
}
