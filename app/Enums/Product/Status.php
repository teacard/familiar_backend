<?php

namespace App\Enums\Product;

enum Status: string
{
    /** 上架 */
    case PUBLISHED = 'published';
    /** 下架 */
    case UNPUBLISHED = 'unpublished';

    public const string SWAGGER_API_ENUM_PROPERTY = 'product.status';
    public const array SWAGGER_API_ENUM_OPTIONS = [
        '上架' => self::PUBLISHED,
        '下架' => self::UNPUBLISHED,
    ];
}
