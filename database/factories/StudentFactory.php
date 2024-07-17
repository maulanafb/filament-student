<?php

namespace Database\Factories;

use App\Models\Classes;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'phone_number' => $this->faker->phoneNumber,
            'password' => bcrypt('password'), // Default password
            'class_id' => Classes::first()->id, // This will be overridden by the seeder
            'section_id' => Section::first()->id, // This will be overridden by the seeder
        ];
    }
}
