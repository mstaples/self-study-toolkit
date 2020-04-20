<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class PromptResponses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the "prompt_responses" table
        if (!Schema::hasTable('prompt_responses')) {
            Schema::create('prompt_responses', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('operator_id');
                $table->integer('prompt_id');
                $table->string('prompt_title');
                $table->string('response');
                $table->boolean('indicate')->nullable();
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
        Schema::drop('prompt_responses');
    }
}
