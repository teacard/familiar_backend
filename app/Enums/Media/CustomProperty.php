<?php

namespace App\Enums\Media;

enum CustomProperty: string
{
    /** 上傳用途（對應 UploadFileType） */
    case TYPE = 'type';
    /** 上傳時的原始檔名 */
    case ORIGINAL_FILE_NAME = 'original_file_name';
}
