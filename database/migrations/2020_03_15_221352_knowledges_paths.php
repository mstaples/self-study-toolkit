<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class KnowledgesPaths extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('knowledges_paths', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('path_id')->unsigned();
            $table->integer('knowledge_id')->unsigned();
            $table->timestamps();

            $table->foreign('path_id')
                ->references('id')->on('prompt_paths')
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
        Schema::dropIfExists('knowledges_paths');
    }
}
