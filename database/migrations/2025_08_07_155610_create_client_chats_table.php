<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('clientChats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticketID')->nullable();
            $table->enum('type', ['support', 'client'])->nullable();
            $table->text('response')->nullable();
            $table->enum('check', ['sending', 'sent', 'reached', 'read'])->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clientChats');
    }
};
