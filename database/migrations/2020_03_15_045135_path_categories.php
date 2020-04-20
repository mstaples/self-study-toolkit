<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class PathCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // habits > communication > interaction > process > initiative
        // Create the "prompt_categories" table
        if (!Schema::hasTable('path_categories')) {
            Schema::create('path_categories', function (Blueprint $table) {
                $table->increments('id');
                $table->string('state');
                $table->string('name');
                $table->integer('min');
                $table->integer('max');
                $table->integer('span');
                $table->string('per');
                $table->text('description')->nullable();
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
        Schema::drop('path_categories');
    }
}
