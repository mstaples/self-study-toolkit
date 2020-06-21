<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FeedbackRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the "feedback_requests" table
        if (!Schema::hasTable('feedback_requests')) {
            Schema::create('feedback_requests', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('created_by_id')->nullable();
                $table->string('state');
                $table->string('request');
                $table->string('request_type'); // primary, secondary, tertiary
                $table->string('signal_strength');
                $table->json('answer_options')->nullable();
                $table->timestamps();
                $table->softDeletes();

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
        Schema::drop('feedback_requests');
    }
}
