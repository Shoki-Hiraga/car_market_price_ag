<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('market_price_rakuten', function (Blueprint $table) {
            $table->id(); // 主キー
            $table->unsignedBigInteger('maker_name_id');
            $table->unsignedBigInteger('model_name_id');
            $table->unsignedBigInteger('grade_name_id');
            $table->year('year')->nullable(); // 車の年式
            $table->decimal('mileage', 5, 1)->nullable(); // 走行距離（小数点対応）
            $table->unsignedInteger('min_price')->nullable(); // 最低価格
            $table->unsignedInteger('max_price')->nullable(); // 最高価格
            $table->string('sc_url')->unique(); // スクレイピングURL
            $table->date('date')->nullable(); 
            $table->timestamps(); // created_at, updated_at

            // 外部キー制約
            $table->foreign('maker_name_id')->references('id')->on('sc_goo_maker')->onDelete('cascade');
            $table->foreign('model_name_id')->references('id')->on('sc_goo_model')->onDelete('cascade');
            $table->foreign('grade_name_id')->references('id')->on('sc_goo_grade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // 外部キー制約を一時的に無効化
        Schema::disableForeignKeyConstraints();
    
        // テーブルを削除
        Schema::dropIfExists('market_price_rakuten');
    
        // 外部キー制約を再度有効化
        Schema::enableForeignKeyConstraints();
    }
};
