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
        Schema::create('year_grade', function (Blueprint $table) {
            $table->id(); // sc_goo_grade と同じ id
            $table->unsignedBigInteger('maker_name_id')->nullable();
            $table->unsignedBigInteger('model_name_id')->nullable();
            $table->string('grade_name')->nullable();
            $table->string('model_number')->nullable();
            $table->string('engine_model')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->string('sc_url')->nullable();
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('year_grade');
    }
};
