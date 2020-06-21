<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Operators extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the "operators" table
        Schema::create('operators', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slack_user_id');
            $table->string('name');
            $table->string('nickname')->nullable();
            $table->string('frequency')->nullable();
            $table->string('count');
            $table->boolean('opt_in')->nullable();
            $table->json('journey_state')->nullable();
            $table->json('preferences')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('operators');
    }
}
