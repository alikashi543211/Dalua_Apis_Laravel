<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequestedAtColumnInSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('schedules', 'requested_at')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->string('requested_at')->nullable();
            });
        }
        if (Schema::hasColumn('schedules', 'approval')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropColumn('approval');
            });
        }
        if (!Schema::hasColumn('schedules', 'approval')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->string('approval')->nullable();
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
