<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Travels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('travels', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('operator_id')->unsigned();
            $table->integer('path_id')->unsigned()->nullable();
            $table->boolean('completed');
            $table->string('level'); // basic, student, mentor, leader, teacher
            $table->json('notebook');
            $table->timestamp('completed_on');
            $table->timestamps();

            $table->foreign('operator_id')
                ->references('id')->on('operators')
                ->onDelete('cascade');

            $table->foreign('path_id')
                ->references('id')->on('prompt_paths')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('travels');
    }
}
