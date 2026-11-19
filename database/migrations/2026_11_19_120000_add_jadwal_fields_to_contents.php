<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $t) {
            $t->date('event_date')->nullable()->after('subtitle');
            $t->string('event_time', 60)->nullable()->after('event_date');
            $t->string('location', 150)->nullable()->after('event_time');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $t) {
            $t->dropColumn(['event_date', 'event_time', 'location']);
        });
    }
};