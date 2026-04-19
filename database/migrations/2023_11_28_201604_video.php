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
        Schema::create('videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('url');
            $table->boolean('subido')->default(false);
            $table->boolean('descargado_video')->default(false);
            $table->string('id_contenedor_publicacion')->nullable();
            $table->string('id_publicacion')->nullable();

            $table->unsignedBigInteger('id_url_clip')->nullable();
            $table->foreign('id_url_clip')->references('id')->on('url_clips');



            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
