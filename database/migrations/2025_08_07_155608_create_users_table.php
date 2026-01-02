<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('username', 100)->unique()->nullable();
            $table->string('email', 100)->unique()->nullable();
            $table->string('status', 12)->nullable();
            $table->string('password', 255)->nullable();
            $table->string('profile_picture', 255)->nullable();
            $table->enum('privLevel', ['admin', 'super_admin', 'support', 'spv', 'developer'])->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->unsignedBigInteger('devRoleID')->nullable();
            $table->tinyInteger('isDeleted')->default(0);
            $table->tinyInteger('isRequestingPassword')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};