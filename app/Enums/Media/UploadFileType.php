<?php

namespace App\Enums\Media;

/**
 * 上傳檔案類型.
 */
enum UploadFileType: string
{
    case IMAGE = 'image';

    public static function detectType(string $mimeType): UploadFileType
    {
        return match (true) {
            str_starts_with($mimeType, 'image/') => UploadFileType::IMAGE,
            default => UploadFileType::IMAGE, // 匹配不到則預設圖片
        };
    }

    public function mimes(): string
    {
        return match ($this) {
            self::IMAGE => 'jpg,jpeg,png',
        };
    }

    public function maxes(): string
    {
        return match ($this) {
            self::IMAGE => '10240',
        };
    }
}
