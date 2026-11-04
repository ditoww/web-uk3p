<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('devTickets', 'priority')) {
            Schema::table('devTickets', function (Blueprint $table) {
                $table->unsignedInteger('priority')->nullable()->after('link');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('devTickets', 'priority')) {
            Schema::table('devTickets', function (Blueprint $table) {
                $table->dropColumn('priority');
            });
        }
    }
};
