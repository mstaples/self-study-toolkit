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
                $table->string('state');
                $table->string('request')->unique();
                $table->string('request_type');
                $table->json('answer_options')->nullable();
                $table->json('tags');
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
        Schema::drop('feedback_requests');
    }
}
