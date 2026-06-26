<?php

namespace App\Docs\AdminApi\Requests\Announcement;

use App\Enums\Announcement\TargetAudience;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminApi.Announcement.UpdateRequest',
    required: ['title', 'content', 'targetAudience', 'publishAt', 'expiresAt'],
)]
class UpdateRequest
{
    #[OA\Property(description: '公告標題', maxLength: 100, example: '系統維護公告')]
    public string $title;

    #[OA\Property(description: '公告內容（富文字）', maxLength: 5000, example: '系統將於本週日進行維護。')]
    public string $content;

    #[OA\Property(description: '目標對象（已發佈公告不可修改）', enum: [TargetAudience::class], example: TargetAudience::ALL_USERS->value)]
    public string $targetAudience;

    #[OA\Property(description: '發布時間（須晚於或等於現在；已發佈公告不可修改）', example: '2026-07-01 00:00:00')]
    public string $publishAt;

    #[OA\Property(description: '到期時間（須晚於發布時間；null 代表永久發布；已發佈公告不可修改）', example: '2026-07-31 23:59:59')]
    public ?string $expiresAt;

    #[OA\Property(description: '是否置頂（最多 3 則）', example: false)]
    public bool $shouldPin;
}
