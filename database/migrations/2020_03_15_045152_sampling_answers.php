<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SamplingAnswers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the "sampling_answers" table
        if (!Schema::hasTable('sampling_answers')) {
            Schema::create('sampling_answers', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('operator_id')->unsigned();
                $table->unsignedInteger('question_id')->nullable();
                $table->string('question_text');
                $table->string('depth');
                $table->string('freeform_answer')->nullable();
                $table->json('selected_options')->nullable();
                $table->json('available_options')->nullable();
                $table->integer('eval_percent')->nullable();
                $table->timestamps();

                $table->foreign('operator_id')
                    ->references('id')->on('operators')
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
        Schema::drop('sampling_answers');
    }
}
