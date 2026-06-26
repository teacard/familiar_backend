<?php

namespace Database\Factories;

use App\Enums\Player\Status;
use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    protected $model = Player::class;

    public function definition(): array
    {
        return [
            'player_number' => 'PL' . str_pad((string)fake()->unique()->numberBetween(0, 99999999), 8, '0', STR_PAD_LEFT),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->numerify('09########'),
            'password' => Hash::make('password'),
            'status' => Status::ACTIVE,
        ];
    }

    /** 停用狀態玩家 */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::SUSPENDED,
        ]);
    }
}
