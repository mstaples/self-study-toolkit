<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FeedbackRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the "feedback_records" table
        if (!Schema::hasTable('feedback_records')) {
            Schema::create('feedback_records', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('operator_id');
                $table->unsignedInteger('secondary_operator_id')->nullable();
                $table->unsignedInteger('tertiary_operator_id')->nullable();
                $table->integer('feedback_request_id');
                $table->string('feedback_request');
                $table->string('answer')->nullable();
                $table->integer('rating')->nullable();
                $table->json('notebook_snapshot')->nullable();
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
        Schema::drop('feedback_records');
    }
}
