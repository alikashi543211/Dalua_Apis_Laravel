<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusColumnInNotificationDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('notification_devices', 'status')) {
            Schema::table('notification_devices', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
        if (!Schema::hasColumn('notification_devices', 'status')) {
            Schema::table('notification_devices', function (Blueprint $table) {
                $table->boolean('status')->nullable()->default(true);
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
        Schema::table('notification_devices', function (Blueprint $table) {
            //
        });
    }
}
