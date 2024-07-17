<?php

namespace Database\Factories;

use App\Models\Classes;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Section A', 'Section B']),
            'class_id' => Classes::first()->id, // This will be overridden by the seeder
        ];
    }
}
