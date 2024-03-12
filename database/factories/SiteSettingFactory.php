<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SiteSetting>
 */
class SiteSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group' => strtolower(fake()->unique()->word()),
            'key' => strtolower(fake()->unique()->word() . date('is')),
            'content' => [
                'type' => $type = \Arr::random([
                    'bool',
                    'int',
                    'float',
                    'url',
                    'email',
                    'domain',
                    'string',
                ]),
                'value' => match ($type ?? null) {
                    'bool' => (rand() % 3) != 0,
                    'int' => rand(100, 800),
                    'float' => floatval(rand(15, 800) / 11),
                    'url' => 'http://fakeurl.com/any',
                    'email' => 'fake@email.com',
                    'domain' => 'site.com',
                    'string' => 'any value',
                    default => null
                },
                'castValueUsing' => null,
            ],
            'active' => fake()->boolean(90),
        ];
    }
}
