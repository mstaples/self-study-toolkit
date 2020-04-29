<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SamplingOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the "sampling_answers" table
        if (!Schema::hasTable('sampling_options')) {
            Schema::create('sampling_options', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('sampling_question_id');
                $table->string('state');
                $table->string('question_text');
                $table->string('option');
                $table->boolean('correct');
                $table->timestamps();

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
        Schema::drop('sampling_options');
    }
}
