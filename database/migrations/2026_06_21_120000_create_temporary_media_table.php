<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('temporary_media', function (Blueprint $table) {
            $table->comment('暫存媒體承載者（常駐單例 owner，依 system_name 區分系統別）');
            $table->id();
            $table->string('system_name')->unique()->comment('系統別，如 admin（見 SystemName enum）');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temporary_media');
    }
};
