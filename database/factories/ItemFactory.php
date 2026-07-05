<?php

namespace Database\Factories;

use App\Enums\Item\Status;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->lexify('????????'),
            'status' => Status::ACTIVE,
        ];
    }

    /** 啟用 */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::ACTIVE,
        ]);
    }

    /** 停用 */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::DISABLED,
        ]);
    }
}
