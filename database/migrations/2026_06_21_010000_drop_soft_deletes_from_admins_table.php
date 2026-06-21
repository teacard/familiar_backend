<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            // 後台人員改為硬刪除，移除軟刪除欄位 deleted_at
            $table->dropSoftDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->softDeletes();
        });
    }
};
