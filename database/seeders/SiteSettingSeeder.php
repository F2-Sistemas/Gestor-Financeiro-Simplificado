<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->initialData();

        if (app()->environment('testing')) {
            $this->fakeData();
        }
    }

    public function initialData(): void
    {
        // WIP

        $settings = [
            /* [
                'group' => ...,
                'key' => ...,
                'content' => [
                    'type' => ...,
                    'value' => ...,
                    'castValueUsing' => ...,
                ],
                'active' => ...,
            ], */
        ];
    }

    public function fakeData(): void
    {
        // TODO
        // SiteSetting::factory()->create();
    }
}
