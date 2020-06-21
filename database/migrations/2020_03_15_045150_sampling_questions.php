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
                $table->string('state');
                $table->string('depth');
                $table->text('question');
                $table->timestamps();
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
