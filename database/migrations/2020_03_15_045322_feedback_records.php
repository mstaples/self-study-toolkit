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
                $table->unsignedInteger('author_id')->nullable();
                $table->string('relationship'); // primary, secondary, tertiary
                $table->integer('feedback_request_id');
                $table->string('feedback_request');
                $table->string('answer')->nullable();
                $table->integer('rating')->nullable();
                $table->timestamps();

                $table->foreign('operator_id')
                    ->references('id')->on('operators')
                    ->onDelete('cascade');

                $table->foreign('author_id')
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
        Schema::drop('feedback_records');
    }
}
