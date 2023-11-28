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
        Schema::create('url_canales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre_canal')->nullable();
            $table->string('url');
            $table->dateTime('ultima_consulta')->nullable();
            $table->text('body_canal')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('url_canales');
    }
};
