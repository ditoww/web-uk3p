<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('clientTickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userID')->nullable();
            $table->unsignedBigInteger('supportID')->nullable();
            $table->unsignedBigInteger('appID')->nullable();
            $table->string('title', 255)->nullable();
            $table->enum('status', ['BELUM DIPROSES', 'SEDANG DIPROSES', 'SELESAI', 'DITUTUP'])->default('BELUM DIPROSES');
            $table->string('link', 255)->nullable();
            $table->tinyInteger('isDeleted')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clientTickets');
    }
};