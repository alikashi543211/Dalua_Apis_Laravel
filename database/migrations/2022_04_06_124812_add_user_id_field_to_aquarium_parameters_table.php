<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdFieldToAquariumParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('aquarium_parameters', 'user_id')) {

            Schema::table('aquarium_parameters', function (Blueprint $table) {
                $table->bigInteger('user_id')->unsigned()->after('aquarium_id');
            });

            Schema::table('aquarium_parameters', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::table('aquarium_parameters', function (Blueprint $table) {
            //
        });
    }
}
