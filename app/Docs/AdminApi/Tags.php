<?php

namespace App\Docs\AdminApi;

use OpenApi\Attributes as OA;

#[OA\Tag(name: Tags::AUTH, description: '後台認證相關 API')]
#[OA\Tag(name: Tags::ADMIN, description: '後台人員管理相關 API')]
#[OA\Tag(name: Tags::PLAYER, description: '遊戲會員管理相關 API')]
#[OA\Tag(name: Tags::ROLE, description: '角色管理相關 API')]
#[OA\Tag(name: Tags::MEDIA, description: '媒體上傳相關 API')]
#[OA\Tag(name: Tags::ANNOUNCEMENT, description: '公告管理相關 API')]
class Tags
{
    public const string AUTH = '後台認證';
    public const string ADMIN = '後台人員管理';
    public const string PLAYER = '遊戲會員管理';
    public const string ROLE = '角色管理';
    public const string MEDIA = '媒體管理';
    public const string ANNOUNCEMENT = '公告管理';
}
