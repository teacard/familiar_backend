<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->comment('遊戲玩家（會員）資料表');
            $table->id();
            $table->string('player_number', 10)->unique()->comment('玩家 ID（PlayerID），格式 PL+8 位數字，系統產生不可編輯');
            $table->string('name')->comment('玩家顯示名稱');
            $table->string('email')->unique()->comment('電子郵件（登入帳號）');
            $table->string('phone', 20)->nullable()->unique()->comment('手機號碼（選填，填寫則唯一）');
            $table->string('password')->comment('bcrypt 雜湊密碼');
            $table->string('status', 25)->default('active')->comment('帳號狀態：active,suspended');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
