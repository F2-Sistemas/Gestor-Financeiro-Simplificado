<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;

class InitialSiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->initialUsers();
        $this->initialSettings();
        $this->dummyData();
    }

    public function initialUsers(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@mail.com',
                'password' => 'power@123',
                'toCreate' => !app()->isProduction(),
            ],
        ];

        foreach ($users as $userData) {
            if (!($userData['toCreate'] ?? false)) {
                continue;
            }

            $userData['password'] = Hash::make($userData['password'] ?? 'password');

            User::updateOrCreate(
                [
                    'email' => $userData['email'],
                ],
                Arr::except($userData, [
                    'toCreate',
                ])
            );
        }
    }

    public function initialSettings(): void
    {
        // TODO
    }

    public function dummyData(): void
    {
        if (app()->isProduction()) {
            return;
        }

        // TODO
    }
}
