<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAquariumParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('aquaria', 'temperature')) {
            Schema::table('aquaria', function (Blueprint $table) {
                $table->dropColumn('temperature');
                $table->dropColumn('ph');
                $table->dropColumn('salinity');
                $table->dropColumn('alkalinity');
                $table->dropColumn('magnesium');
                $table->dropColumn('nitrate');
                $table->dropColumn('phosphate');
                $table->integer('test_frequency')->unsigned()->default(FREQUENCY_WEEK);
                $table->string('last_test')->default(date('Y-m-d'));
                $table->integer('clean_frequency')->unsigned()->default(FREQUENCY_WEEK);
                $table->string('last_clean')->default(date('Y-m-d'));
            });
        }

        Schema::create('aquarium_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('temperature')->nullable();
            $table->string('ph')->nullable();
            $table->string('salinity')->nullable();
            $table->string('calcium')->nullable();
            $table->string('alkalinity')->nullable();
            $table->string('magnesium')->nullable();
            $table->string('nitrate')->nullable();
            $table->string('phosphate')->nullable();
            $table->bigInteger('aquarium_id')->unsigned();
            $table->timestamps();
        });

        Schema::table('aquarium_parameters', function (Blueprint $table) {
            $table->foreign('aquarium_id')->references('id')->on('aquaria')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aquarium_parameters');
    }
}
