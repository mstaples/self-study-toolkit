<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class PermissionsSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the "permissions_settings" table
        if (!Schema::hasTable('permissions_settings')) {
            Schema::create('permissions_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('operator_id')->unsigned();
                $table->integer('permission_option_id')->unsigned();
                $table->string('name');
                $table->boolean('setting');
                $table->timestamps();

                $table->foreign('operator_id')
                    ->references('id')->on('operators')
                    ->onDelete('cascade');

                $table->foreign('permission_option_id')
                    ->references('id')->on('permissions_options')
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
        Schema::drop('permissions_settings');
    }
}
