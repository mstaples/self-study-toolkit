<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FeedbackOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the "feedback_requests" table
        if (!Schema::hasTable('feedback_options')) {
            Schema::create('feedback_options', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('question_id')->nullable();
                $table->unsignedInteger('created_by_id')->nullable();
                $table->string('question_text');
                $table->string('option');
                $table->string('depth');
                $table->string('state');
                $table->boolean('correct');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('question_id')
                    ->references('id')->on('feedback_requests')
                    ->onDelete('set null');

                $table->foreign('created_by_id')
                    ->references('id')->on('users')
                    ->onDelete('set null');
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
        Schema::drop('feedback_options');
    }
}
