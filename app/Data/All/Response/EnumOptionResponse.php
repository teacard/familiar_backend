<?php

namespace App\Data\All\Response;

use BackedEnum;

readonly class EnumOptionResponse
{
    public function __construct(
        public string $value,
        public string $label,
    ) {}

    // 將任意 enum 的 swaggerApiEnumOptions() 結果轉為 EnumOptionResponse 陣列
    public static function collection(array $enumOptions): array
    {
        return collect($enumOptions)
            ->map(fn (BackedEnum $enum, string $label) => new self(
                value: $enum->value,
                label: $label,
            ))
            ->values()
            ->toArray();
    }
}
