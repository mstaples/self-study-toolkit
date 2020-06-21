<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class KnowledgesQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('knowledges_questions', function (Blueprint $table) {
            $table->id();
            $table->integer('question_id')->unsigned();
            $table->integer('knowledge_id')->unsigned();
            $table->timestamps();

            $table->foreign('question_id')
                ->references('id')->on('sampling_questions')
                ->onDelete('cascade');

            $table->foreign('knowledge_id')
                ->references('id')->on('knowledges')
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
        Schema::dropIfExists('knowledges_questions');
    }
}
