<?php

namespace App\Docs\Default;

use OpenApi\Attributes as OA;

#[OA\Tag(name: Tags::REGISTRATION, description: '玩家自助註冊相關 API')]
class Tags
{
    public const string REGISTRATION = '玩家自助註冊';
}
