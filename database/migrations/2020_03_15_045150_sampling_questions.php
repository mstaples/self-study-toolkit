<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class SamplingQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the "sampling_questions" table
        if (!Schema::hasTable('sampling_questions')) {
            Schema::create('sampling_questions', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('path_id');
                $table->string('state');
                $table->text('question');
                $table->json('answer_options')->nullable();
                $table->timestamps();

                $table->foreign('path_id')
                    ->references('id')->on('prompt_paths')
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
        Schema::drop('sampling_questions');
    }
}
