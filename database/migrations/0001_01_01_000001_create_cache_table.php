<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        if ('database' !== config('cache.default')) {
            return;
        }

        Schema::create('cache', function (Blueprint $table) {
            $table->comment('快取資料表');
            $table->string('key')->primary()->comment('快取鍵值');
            $table->mediumText('value')->comment('快取內容');
            $table->bigInteger('expiration')->index()->comment('過期時間（Unix timestamp）');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->comment('快取鎖定資料表');
            $table->string('key')->primary()->comment('鎖定鍵值');
            $table->string('owner')->comment('鎖定持有者');
            $table->bigInteger('expiration')->index()->comment('鎖定過期時間（Unix timestamp）');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
