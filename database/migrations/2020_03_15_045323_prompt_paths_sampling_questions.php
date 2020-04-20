<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class PromptPathsSamplingQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the "prompt_paths_sampling_questions" table
        if (!Schema::hasTable('prompt_paths_sampling_questions')) {
            Schema::create('prompt_paths_sampling_questions', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('prompt_path_id');
                $table->unsignedInteger('sampling_question_id');
                $table->timestamps();

                $table->foreign('prompt_path_id')
                    ->references('id')->on('prompt_paths')
                    ->onDelete('cascade');

                $table->foreign('sampling_question_id')
                    ->references('id')->on('sampling_questions')
                    ->onDelete('cascade');
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
        Schema::drop('prompt_paths_sampling_questions');
    }
}
