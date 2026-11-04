<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('clientAttachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chatID')->nullable();
            $table->string('fileName', 255)->nullable();
            $table->string('fileExtension', 10)->nullable();
            $table->string('filePath', 255)->nullable();
            $table->unsignedBigInteger('fileSize')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clientAttachments');
    }
};