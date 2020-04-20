<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class PromptPaths extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the "prompt_paths" table
        if (!Schema::hasTable('prompt_paths')) {
            Schema::create('prompt_paths', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('path_category_id');
                $table->unsignedInteger('created_by_id')->nullable();
                $table->string('state');
                $table->string('path_difficulty');
                $table->string('path_category');
                $table->string('path_title')->unique();
                $table->text('path_thesis');
                $table->integer('steps');
                $table->json('tags');
                $table->timestamps();

                $table->foreign('path_category_id')
                    ->references('id')->on('path_categories')
                    ->onDelete('cascade');

                $table->foreign('created_by_id')
                    ->references('id')->on('users')
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
        Schema::drop('prompt_paths');
    }
}
