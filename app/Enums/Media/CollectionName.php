<?php

namespace App\Enums\Media;

enum CollectionName: string
{
    /** 暫存：上傳後先進此集合，未被採用者由排程定時清除（暫存流程於上傳 API change 實作） */
    case TEMPORARY = 'temporary';
    /** 後台：後台人員相關媒體（目前為頭像，單檔覆蓋） */
    case ADMIN = 'admin';
}
