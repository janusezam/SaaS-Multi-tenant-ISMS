<?php

namespace Database\Factories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Module>
 */
class ModuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    private static $planId = 1;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(),
            'plan_id' => self::$planId++,
        ];
    }
}
