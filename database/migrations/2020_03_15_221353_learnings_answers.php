<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LearningsAnswers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('learnings_answers', function (Blueprint $table) {
            $table->id();
            $table->integer('answer_id')->unsigned();
            $table->integer('knowledge_id')->unsigned();
            $table->timestamps();

            $table->foreign('answer_id')
                ->references('id')->on('sampling_answers')
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
        Schema::dropIfExists('learnings_answers');
    }
}
