<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Prompts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the "prompts" table
        if (!Schema::hasTable('prompts')) {
            Schema::create('prompts', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('prompt_path_step');
                $table->unsignedInteger('prompt_path_id');
                $table->string('prompt_title');
                $table->boolean('repeatable');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('prompt_path_id')
                    ->references('id')->on('prompt_paths')
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
        Schema::drop('prompts');
    }
}
