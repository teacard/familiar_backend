<?php

namespace Database\Factories;

use App\Enums\Admin\Status;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'status' => Status::ACTIVE,
            'is_super_admin' => false,
        ];
    }

    /** 超級管理員（可存取 Telescope） */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_super_admin' => true,
        ]);
    }
}
