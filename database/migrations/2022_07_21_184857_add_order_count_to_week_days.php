<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderCountToWeekDays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('week_days', function (Blueprint $table) {
            $table->integer('order_count_customer')->nullable()->after('end_time');
            $table->integer('order_count_wholesale')->nullable()->after('order_count_customer');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('week_days', function (Blueprint $table) {
        });
    }
}
