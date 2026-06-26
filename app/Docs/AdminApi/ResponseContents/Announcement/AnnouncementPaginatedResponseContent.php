<?php

namespace App\Docs\AdminApi\ResponseContents\Announcement;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Announcement.AnnouncementPaginatedResponseContent', description: '公告分頁列表')]
class AnnouncementPaginatedResponseContent
{
    #[OA\Property(items: new OA\Items(ref: AnnouncementListResponseContent::class))]
    public array $items;

    #[OA\Property(description: '當前頁碼', example: 1)]
    public int $currentPage;

    #[OA\Property(description: '每頁筆數', example: 15)]
    public int $perPage;

    #[OA\Property(description: '最後頁碼', example: 7)]
    public int $lastPage;
}
