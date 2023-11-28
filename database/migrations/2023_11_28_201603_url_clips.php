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
        Schema::create('url_clips', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('url');
            $table->string('titulo_clip')->nullable();
            $table->string('url_thumbnail')->nullable();
            $table->text('body_clip')->nullable();
            $table->boolean('obtenido_video')->default(false);

            $table->unsignedBigInteger('id_url_canal')->nullable();
            $table->foreign('id_url_canal')->references('id')->on('url_canales');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('url_clips');
    }
};
