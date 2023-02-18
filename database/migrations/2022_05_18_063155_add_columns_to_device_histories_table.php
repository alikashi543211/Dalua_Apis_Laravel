<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToDeviceHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('device_histories', 'device_id')) {
            Schema::table('device_histories', function (Blueprint $table) {
                $table->bigInteger('device_id')->unsigned()->nullable();
                $table->bigInteger('type')->default(LOG_TYPE_SUBSCRIBE);
                $table->mediumText('message')->nullable();
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
        Schema::table('device_histories', function (Blueprint $table) {
            //
        });
    }
}
