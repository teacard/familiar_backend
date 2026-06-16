<?php

namespace App\Docs\AdminApi;

use OpenApi\Attributes as OA;

#[OA\Tag(name: Tags::AUTH,  description: '後台認證相關 API')]
#[OA\Tag(name: Tags::ADMIN, description: '後台人員管理相關 API')]
#[OA\Tag(name: Tags::ROLE,  description: '角色管理相關 API')]
class Tags
{
    const string AUTH  = '後台認證';
    const string ADMIN = '後台人員管理';
    const string ROLE  = '角色管理';
}
