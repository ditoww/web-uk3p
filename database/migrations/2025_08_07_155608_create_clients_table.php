<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('username', 31)->unique()->nullable();
            $table->string('email', 191)->unique()->nullable();
            $table->string('password', 255)->nullable();
            $table->string('profile_picture', 255)->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('country_code', 4);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->string('phone_otp')->nullable();
            $table->string('phone_otp_expires_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->tinyInteger('isDeleted')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clients');
    }
};
