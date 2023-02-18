<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommandLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('command_logs', function (Blueprint $table) {
            $table->id();
            $table->text('topic')->nullable();
            $table->string('timestamp')->nullable();
            $table->integer('command_id')->unsigned()->default(4);
            $table->text('response')->nullable();
            $table->bigInteger('user_id')->unsigned();
            $table->longText('payload')->nullable();
            $table->boolean('status')->default(false);
            $table->bigInteger('device_id')->nullable()->unsigned();
            $table->bigInteger('group_id')->nullable()->unsigned();
            $table->timestamps();
        });

        Schema::table('command_logs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('command_logs');
    }
}
