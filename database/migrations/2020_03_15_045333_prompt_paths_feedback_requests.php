<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PromptPathsFeedbackRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the "prompt_paths_feedback_requests" table
        if (!Schema::hasTable('prompt_paths_feedback_requests')) {
            Schema::create('prompt_paths_feedback_requests', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('prompt_path_id');
                $table->unsignedInteger('feedback_request_id');
                $table->timestamps();

                $table->foreign('prompt_path_id')
                    ->references('id')->on('prompt_paths')
                    ->onDelete('cascade');

                $table->foreign('feedback_request_id')
                    ->references('id')->on('feedback_requests')
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
        Schema::drop('prompt_paths_feedback_requests');
    }
}
