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
        Schema::create('product_types', function (Blueprint $table) {
            $table->comment('商品類別');
            $table->id();
            $table->string('name', 10)->comment('類別名稱，例如：道具、點數、通行證');
            $table->string('code', 8)->unique()->comment('類別代碼，系統自動產生的唯一 8 碼，建立後不可修改，供 products.type 與下拉選單使用');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_types');
    }
};
