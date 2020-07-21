<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class PromptSegmentResponses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('prompt_segment_responses')) {
            Schema::create('prompt_segment_responses', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('operator_id');
                $table->unsignedInteger('prompt_id')->nullable();
                $table->unsignedInteger('question_id')->nullable();
                $table->unsignedInteger('travel_id');
                $table->string('question_text');
                $table->string('freeform_answer')->nullable();
                $table->json('selected_options')->nullable();
                $table->json('available_options')->nullable();
                $table->integer('eval_percent')->nullable();
                $table->timestamps();

                $table->foreign('operator_id')
                    ->references('id')->on('operators')
                    ->onDelete('cascade');

                $table->foreign('travel_id')
                    ->references('id')->on('travels')
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
        Schema::drop('prompt_segment_responses');
    }
}
