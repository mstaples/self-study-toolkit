<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LearningsTravels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('learnings_travels', function (Blueprint $table) {
            $table->id();
            $table->integer('travel_id')->unsigned();
            $table->integer('knowledge_id')->unsigned();
            $table->timestamps();

            $table->foreign('travel_id')
                ->references('id')->on('travels')
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
        Schema::dropIfExists('operator_knowledges');
    }
}
