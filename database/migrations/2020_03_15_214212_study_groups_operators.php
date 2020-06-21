<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StudyGroupsOperators extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the "study_groups_operators" table
        if (!Schema::hasTable('study_groups_operators')) {
            Schema::create('study_groups_operators', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('operator_id');
                $table->unsignedInteger('study_group_id');
                $table->timestamps();

                $table->foreign('operator_id')
                    ->references('id')->on('operators')
                    ->onDelete('cascade');

                $table->foreign('study_group_id')
                    ->references('id')->on('study_groups')
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
        Schema::dropIfExists('study_groups_operators');
    }
}
