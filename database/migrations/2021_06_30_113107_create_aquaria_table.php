<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAquariaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aquaria', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('temperature')->nullable();
            $table->string('ph')->nullable();
            $table->string('salinity')->nullable();
            $table->string('alkalinity')->nullable();
            $table->string('magnesium')->nullable();
            $table->string('nitrate')->nullable();
            $table->string('phosphate')->nullable();
            $table->bigInteger('user_id')->unsigned();
            $table->timestamps();
        });

        Schema::table('aquaria', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aquaria');
    }
}
