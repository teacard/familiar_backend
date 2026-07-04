<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('announcements')->update([
            'status' => DB::raw('LOWER(status)'),
            'target_audience' => DB::raw('LOWER(target_audience)'),
        ]);

        Schema::table('announcements', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->comment('狀態：draft, scheduled, published, expired')->change();
            $table->string('target_audience', 50)->default('all_users')->comment('目標對象：all_users, vip, general，前端根據用戶身份篩選顯示')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('status', 20)->default('DRAFT')->comment('狀態：DRAFT, SCHEDULED, PUBLISHED, EXPIRED')->change();
            $table->string('target_audience', 50)->default('ALL_USERS')->comment('目標對象：ALL_USERS, VIP, GENERAL，前端根據用戶身份篩選顯示')->change();
        });

        DB::table('announcements')->update([
            'status' => DB::raw('UPPER(status)'),
            'target_audience' => DB::raw('UPPER(target_audience)'),
        ]);
    }
};
