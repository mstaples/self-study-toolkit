<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Learnings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('learnings', function (Blueprint $table) {
            $table->id();
            $table->integer('operator_id')->unsigned();
            $table->integer('knowledge_id')->unsigned();
            $table->integer('feedback_received');
            $table->integer('questions_answered');
            $table->integer('paths_complete');
            $table->string('depth');
            $table->string('level');
            $table->timestamp('last_evaluation_completed')->nullable();
            $table->timestamps();

            $table->foreign('operator_id')
                ->references('id')->on('operators')
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
        Schema::dropIfExists('learnings');
    }
}
