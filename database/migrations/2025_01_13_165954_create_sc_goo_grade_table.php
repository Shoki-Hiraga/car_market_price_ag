<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 外部キー制約を一時的に無効化
        Schema::disableForeignKeyConstraints();

        Schema::create('sc_goo_grade', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->unsignedBigInteger('maker_name_id'); // Foreign Key to sc_goo_maker
            $table->unsignedBigInteger('model_name_id'); // Foreign Key to sc_goo_model
            $table->string('grade_name', 100); // Grade Name
            $table->string('model_number', 30); // Model Number
            $table->string('engine_model', 30); // Engine_model
            $table->integer('year'); // 年式データを格納するカラム
            $table->integer('month'); 
            $table->string('sc_url', 100); 
            $table->timestamps(); // created_at and updated_at

            // Foreign key constraints
            $table->foreign('maker_name_id')->references('id')->on('sc_goo_maker')->onDelete('cascade');
            $table->foreign('model_name_id')->references('id')->on('sc_goo_model')->onDelete('cascade');

            // Unique constraint to prevent duplicate entries
            $table->unique(
                ['maker_name_id', 'model_name_id', 'grade_name', 'model_number', 'engine_model', 'year', 'month', 'sc_url'], 
                'unique_grade_entry'
            );
        });

        // 外部キー制約を再度有効化
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 外部キー制約を一時的に無効化
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('sc_goo_grade');

        // 外部キー制約を再度有効化
        Schema::enableForeignKeyConstraints();
    }
};
