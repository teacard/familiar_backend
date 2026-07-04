<?php

namespace Database\Factories;

use App\Enums\Product\Status;
use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'product_type_id' => fn () => ProductType::factory()->create()->id,
            'amount' => fake()->numberBetween(0, 5000),
            'status' => Status::UNPUBLISHED,
        ];
    }

    /** 上架 */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::PUBLISHED,
        ]);
    }

    /** 下架 */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::UNPUBLISHED,
        ]);
    }
}
