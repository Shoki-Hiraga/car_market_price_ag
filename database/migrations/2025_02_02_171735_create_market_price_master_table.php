<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketPriceMasterTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('market_price_master', function (Blueprint $table) {
            $table->id();
            $table->integer('maker_name_id');
            $table->integer('model_name_id');
            $table->integer('grade_name_id');
            $table->integer('year');
            $table->integer('mileage');
            $table->integer('min_price');
            $table->integer('max_price');
            $table->string('sc_url');
            $table->timestamps();
            // 重複を防ぐためのユニーク制約（必要に応じて調整してください）
            $table->unique(['maker_name_id', 'model_name_id', 'grade_name_id', 'year', 'mileage', 'sc_url']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('market_price_master');
    }
}
