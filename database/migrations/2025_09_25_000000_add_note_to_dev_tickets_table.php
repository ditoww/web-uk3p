<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('devTickets') && !Schema::hasColumn('devTickets', 'note')) {
            Schema::table('devTickets', function (Blueprint $table) {
                $table->text('note')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('devTickets') && Schema::hasColumn('devTickets', 'note')) {
            Schema::table('devTickets', function (Blueprint $table) {
                $table->dropColumn('note');
            });
        }
    }
};
