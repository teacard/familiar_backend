<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('draft_players', function (Blueprint $table) {
            $table->comment('玩家自助註冊草稿資料表（暫存尚未完成註冊的流程資料，後台不可見/不可操作）');
            $table->id();
            $table->string('email')->unique()->comment('電子郵件（註冊帳號，唯一）');
            $table->string('token')->comment('一次性 token，bcrypt 雜湊儲存');
            $table->string('verification_code')->nullable()->comment('email 驗證碼，6 碼數字，明碼儲存；Step①建立當下為 null，寄送驗證碼後才寫入');
            $table->timestamp('verification_code_expires_at')->nullable()->comment('驗證碼過期時間（寄送時間 + 5 分鐘）；Step①建立當下為 null');
            $table->unsignedTinyInteger('verification_attempts')->default(0)->comment('驗證碼錯誤嘗試次數，達 3 次即鎖定');
            $table->unsignedTinyInteger('registration_step')->default(1)->comment('目前註冊進度，見 App\\Enums\\DraftPlayer\\Step');
            $table->string('name')->nullable()->comment('暱稱（等同正式玩家的 name），Step③填寫前為 null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draft_players');
    }
};
