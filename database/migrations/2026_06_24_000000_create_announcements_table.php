<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->comment('公告');
            $table->id();
            $table->string('title', 100)->comment('公告標題');
            $table->text('content')->comment('公告內容（富文字）');
            $table->string('status', 20)->default('DRAFT')->comment('狀態：DRAFT, SCHEDULED, PUBLISHED, EXPIRED');
            $table->string('target_audience', 50)->default('ALL_USERS')->comment('目標對象：ALL_USERS, VIP, GENERAL，前端根據用戶身份篩選顯示');
            $table->timestamp('publish_at')->comment('發布時間');
            $table->timestamp('expires_at')->nullable()->comment('到期時間（null 代表永久發布）');
            $table->integer('order_by')->default(0)->comment('排序優先級：0,1,2=置頂，100+=未置頂');
            $table->timestamps();

            // 索引
            $table->index(['status', 'publish_at'], 'idx_status_publish_at');
            $table->index(['order_by', 'id'], 'idx_order_by_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
