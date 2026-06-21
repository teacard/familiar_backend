<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->comment('媒體檔案資料表（spatie medialibrary，儲存檔案 metadata）');
            $table->id();

            // 多型關聯：媒體擁有者（model_type / model_id），如 Admin 頭像
            $table->morphs('model');
            $table->uuid()->nullable()->unique()->comment('媒體唯一識別碼');
            $table->string('collection_name')->comment('媒體集合名稱，如 admin、temporary（見 CollectionName enum）');
            $table->string('name')->comment('顯示名稱');
            $table->string('file_name')->comment('實際儲存檔名');
            $table->string('mime_type')->nullable()->comment('MIME 類型');
            $table->string('disk')->comment('原始檔儲存的 disk 名稱');
            $table->string('conversions_disk')->nullable()->comment('轉換檔（縮圖等）儲存的 disk 名稱');
            $table->unsignedBigInteger('size')->comment('檔案大小（bytes）');
            $table->json('manipulations')->comment('圖片操作參數');
            $table->json('custom_properties')->comment('自訂屬性');
            $table->json('generated_conversions')->comment('已產生的轉換檔清單');
            $table->json('responsive_images')->comment('響應式圖片資料');
            $table->unsignedInteger('order_column')->nullable()->index()->comment('排序欄位');

            $table->nullableTimestamps();
        });
    }
};
