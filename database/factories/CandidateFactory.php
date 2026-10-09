<?php

namespace Database\Factories;

use App\Models\Candidate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Candidate>
 */
class CandidateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dob = fake()->dateTimeBetween('-50 years', '-18 years');

        return [
            'name' => fake()->name(),
            'id_no' => strtoupper(fake()->bothify('CMS-####')),
            'working' => fake()->randomElement(['Yes', 'No']),
            'address' => fake()->streetAddress(),
            'village' => fake()->city(),
            'tehsil' => fake()->city(),
            'district' => fake()->randomElement(['Jaipur', 'Ajmer', 'Alwar', 'Sikar', 'Kota']),
            'state' => 'Rajasthan',
            'pincode' => (string) fake()->numberBetween(300001, 345999),
            'dob' => $dob,
            'age' => Candidate::ageFromDob($dob),
            'marital_status' => fake()->randomElement(Candidate::MARITAL_STATUSES),
            'gender' => fake()->randomElement(Candidate::GENDERS),
            'education' => fake()->randomElement(['8th', '10th', '12th', 'Graduate', 'ITI']),
            'applied_date' => fake()->dateTimeBetween('-3 months'),
        ];
    }
}
