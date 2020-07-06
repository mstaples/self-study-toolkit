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
            $table->unsignedInteger('operator_id');
            $table->unsignedInteger('prompt_path_id')->nullable();
            $table->unsignedInteger('prompt_id')->nullable();
            $table->boolean('completed');
            $table->string('level'); // basic, student, mentor, leader, teacher
            $table->integer('completed_prompts');
            $table->integer('completed_segments');
            $table->json('notebook');
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->foreign('operator_id')
                ->references('id')->on('operators')
                ->onDelete('cascade');

            $table->foreign('prompt_path_id')
                ->references('id')->on('prompt_paths')
                ->onDelete('set null');

            $table->foreign('prompt_id')
                ->references('id')->on('prompts')
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
