<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePathsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paths', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('operator_id')->unsigned();
            $table->integer('prompt_path_id')->unsigned();
            $table->integer('next_prompt_id')->unsigned()->nullable();
            $table->timestamp('last_prompt');
            $table->integer('repeat');
            $table->boolean('active');
            $table->timestamps();

            $table->foreign('operator_id')
                ->references('id')->on('operators')
                ->onDelete('cascade');

            $table->foreign('prompt_path_id')
                ->references('id')->on('prompt_paths')
                ->onDelete('cascade');

            $table->foreign('next_prompt_id')
                ->references('id')->on('prompts')
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
        Schema::dropIfExists('paths');
    }
}
