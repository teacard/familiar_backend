<?php

namespace Database\Factories;

use App\Enums\Announcement\Status;
use App\Enums\Announcement\TargetAudience;
use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'content' => fake()->paragraph(),
            'status' => Status::SCHEDULED,
            'target_audience' => TargetAudience::ALL_USERS,
            'publish_at' => now()->addDay(),
            'expires_at' => now()->addDays(8),
            'order_by' => Announcement::UNPINNED_ORDER,
        ];
    }

    /** 置頂（order_by 0~2） */
    public function pinned(int $order = 0): static
    {
        return $this->state(fn (array $attributes) => [
            'order_by' => $order,
        ]);
    }

    /** 已發布 */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::PUBLISHED,
            'publish_at' => now()->subDay(),
            'expires_at' => now()->addDays(7),
        ]);
    }

    /** 已到期 */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::EXPIRED,
            'publish_at' => now()->subDays(10),
            'expires_at' => now()->subDay(),
        ]);
    }

    /** 永久發布（無到期時間） */
    public function permanent(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => null,
        ]);
    }
}
