<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompletedColumnToDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('devices', 'completed')) {

            Schema::table('devices', function (Blueprint $table) {
                $table->boolean('completed')->default(1)->after('status');
            });

        }
        if (!Schema::hasColumn('devices', 'wifi')) {

            Schema::table('devices', function (Blueprint $table) {
                $table->string('wifi')->nullable()->after('topic');
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
        if (Schema::hasColumn('devices', 'completed')) {

            Schema::table('devices', function (Blueprint $table) {
                $table->dropColumn('completed');
            });

        }
        if (Schema::hasColumn('devices', 'wifi')) {

            Schema::table('devices', function (Blueprint $table) {
                $table->dropColumn('wifi');
            });

        }
    }
}
