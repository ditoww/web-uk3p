<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dev_note_blobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('file_name');
            $table->string('mime_type', 100)->default('text/plain');
            $table->unsignedBigInteger('size')->default(0);
            $table->binary('blob')->nullable(); // simpan isi file sebagai blob
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dev_note_blobs');
    }
};