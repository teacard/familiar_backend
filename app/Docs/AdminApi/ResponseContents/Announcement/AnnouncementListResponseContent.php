<?php

namespace App\Docs\AdminApi\ResponseContents\Announcement;

use App\Enums\Announcement\Status;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Announcement.AnnouncementListResponseContent', description: '公告列表項目')]
class AnnouncementListResponseContent
{
    #[OA\Property(description: '公告 ID', example: 1)]
    public int $id;

    #[OA\Property(description: '公告標題', example: '系統維護公告')]
    public string $title;

    #[OA\Property(description: '公告內容（富文字）', example: '系統將於本週日進行維護。')]
    public string $content;

    #[OA\Property(description: '狀態', enum: [Status::class], example: Status::SCHEDULED->value)]
    public string $status;

    #[OA\Property(description: '發布時間', example: '2026-07-01 00:00:00')]
    public string $publishAt;

    #[OA\Property(description: '到期時間（null 代表永久發布）', example: '2026-07-31 23:59:59')]
    public ?string $expiresAt;

    #[OA\Property(description: '是否置頂', example: false)]
    public bool $isPinned;
}
