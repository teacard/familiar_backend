<?php

namespace Tests\Feature\Seeders;

use App\Enums\TemporaryMedia\SystemName;
use App\Models\TemporaryMedia;
use Database\Seeders\TemporaryMediaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemporaryMediaSeederTest extends TestCase
{
    use RefreshDatabase;

    /** Seeder 執行後，每個 SystemName 各建立一列常駐 owner */
    public function testCreatesOneOwnerPerSystemName(): void
    {
        // GIVEN 空的 temporary_media 表
        // WHEN  執行 TemporaryMediaSeeder
        $this->seed(TemporaryMediaSeeder::class);

        // THEN  數量與 enum 定義一致，且每個 system_name 都存在
        $this->assertSame(count(SystemName::cases()), TemporaryMedia::count());
        foreach (SystemName::cases() as $systemName) {
            $this->assertDatabaseHas('temporary_media', ['system_name' => $systemName->value]);
        }
    }

    /** 重複執行 Seeder 不產生重複的 owner */
    public function testDoesNotDuplicateOnRerun(): void
    {
        // GIVEN Seeder 已執行過一次
        $this->seed(TemporaryMediaSeeder::class);

        // WHEN  再次執行
        $this->seed(TemporaryMediaSeeder::class);

        // THEN  數量不變
        $this->assertSame(count(SystemName::cases()), TemporaryMedia::count());
    }
}
