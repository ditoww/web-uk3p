<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'appName' => $this->faker->words(2, true),
            'roleID' => $this->faker->randomElement([1, 2, 3]),
        ];
    }
}