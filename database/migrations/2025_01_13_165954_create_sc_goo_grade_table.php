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
        Schema::create('sc_goo_grade', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->unsignedBigInteger('maker_name_id'); // Foreign Key to sc_goo_maker
            $table->unsignedBigInteger('model_name_id'); // Foreign Key to sc_goo_model
            $table->string('grade_name'); // Grade Name
            $table->string('model_number'); // Model Number
            $table->string('engine_model'); // Engine_model
            $table->integer('year')->nullable(); // 年式データを格納するカラム (nullableで年式データがない場合にも対応)
            $table->integer('month')->nullable(); 
            $table->timestamps(); // created_at and updated_at

            // Foreign key constraints
            $table->foreign('maker_name_id')->references('id')->on('sc_goo_maker')->onDelete('cascade');
            $table->foreign('model_name_id')->references('id')->on('sc_goo_model')->onDelete('cascade');

            // Unique constraint to prevent duplicate entries
            $table->unique(['maker_name_id', 'model_name_id', 'grade_name', 'model_number'], 'unique_grade_entry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_goo_grade');
    }
};
