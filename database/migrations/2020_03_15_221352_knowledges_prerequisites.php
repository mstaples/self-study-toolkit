<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class KnowledgesPrerequisites extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('knowledges_prerequisites', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('knowledge_id')->unsigned();
            $table->integer('prerequisite_id')->unsigned();
            $table->timestamps();

            $table->foreign('knowledge_id')
                ->references('id')->on('knowledges')
                ->onDelete('cascade');

            $table->foreign('prerequisite_id')
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
        Schema::dropIfExists('knowledges_prerequisites');
    }
}
