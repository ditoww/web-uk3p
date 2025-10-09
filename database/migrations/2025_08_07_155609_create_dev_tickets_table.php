<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('devTickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supportID')->nullable();
            $table->unsignedBigInteger('devID')->nullable();
            $table->unsignedBigInteger('roleID')->nullable();
            $table->string('title', 255)->nullable();
            $table->enum('status', ['Open', 'In Progress', 'Resolved', 'Closed'])->default('Open');
            $table->string('link', 255)->nullable();
            $table->tinyInteger('isDeleted')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('devTickets');
    }
};