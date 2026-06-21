<?php

namespace Database\Seeders;

use App\Enums\TemporaryMedia\SystemName;
use App\Models\TemporaryMedia;
use Illuminate\Database\Seeder;

class TemporaryMediaSeeder extends Seeder
{
    public function run(): void
    {
        // 每個系統別各建立一列常駐 owner，承載該系統的暫存媒體
        foreach (SystemName::cases() as $systemName) {
            TemporaryMedia::firstOrCreate(['system_name' => $systemName->value]);
        }
    }
}
