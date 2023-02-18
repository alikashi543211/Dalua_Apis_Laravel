<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveAddScheduleTypeFieldInSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('schedules', 'schedule_type')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropColumn('schedule_type');
            });
        }
        if (!Schema::hasColumn('schedules', 'schedule_type')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->string('schedule_type')->nullable()->default(WATER_MARINE);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            //
        });
    }
}
