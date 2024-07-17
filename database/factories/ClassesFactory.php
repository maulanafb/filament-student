<?php

namespace Database\Factories;

use App\Models\Classes;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassesFactory extends Factory
{
    protected $model = Classes::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word . ' Class',
        ];
    }
}
