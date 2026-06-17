<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->comment('後台管理員資料表');
            $table->id();
            $table->string('name', 15)->unique()->comment('後台人員顯示名稱，最長 15 字');
            $table->string('email', 100)->unique()->comment('登入帳號');
            $table->string('password')->comment('bcrypt 雜湊密碼');
            $table->string('status', 25)->default('active')->comment('帳號狀態：active,suspended');
            $table->date('last_login_date')->nullable()->comment('最後登入日期');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
