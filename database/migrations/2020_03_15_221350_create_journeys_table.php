<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJourneysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('journeys', function (Blueprint $table) {
            $table->id();
            $table->integer('operator_id')->unsigned();
            $table->integer('active_path_one')->unsigned()->nullable();
            $table->integer('active_path_two')->unsigned()->nullable();
            $table->integer('active_path_three')->unsigned()->nullable();
            $table->string('difficulty');// basic, student, mentor, leader, teacher
            $table->json('notebook');
            $table->timestamps();

            $table->foreign('operator_id')
                ->references('id')->on('operators')
                ->onDelete('cascade');

            $table->foreign('active_path_one')
                ->references('id')->on('paths')
                ->onDelete('cascade');

            $table->foreign('active_path_two')
                ->references('id')->on('paths')
                ->onDelete('cascade');

            $table->foreign('active_path_three')
                ->references('id')->on('paths')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('journeys');
    }
}
