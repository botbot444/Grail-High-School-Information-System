<?php

namespace Database\Factories;

use App\Models\ParentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParentProfileFactory extends Factory
{
    protected $model = ParentProfile::class;

    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName  = fake()->lastName();
        $email     = fake()->unique()->safeEmail();

        return [
            'user_id'     => User::factory()->state(['role' => 'parent']),
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'email'       => $email,
            'phone'       => fake()->phoneNumber(),
            'address'     => fake()->address(),
            'occupation'  => fake()->jobTitle(),
            'national_id' => strtoupper(fake()->bothify('######/##/##/####')), // NRC format
        ];
    }

    /** State: parent with an existing user (pass user_id in manually) */
    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'user_id'    => $user->id,
            'email'      => $user->email,
            'first_name' => explode(' ', $user->name)[0] ?? 'Parent',
            'last_name'  => explode(' ', $user->name)[1] ?? 'User',
        ]);
    }
}
