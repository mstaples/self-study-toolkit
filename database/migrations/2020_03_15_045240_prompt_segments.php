<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class PromptSegments extends Migration
{
    /**
     * Run the migrations.
    protected $fillable = [ 'title', 'text', 'type', 'imageUrl', 'url', 'accessory' ];
     *
     * @return void
     */
    public function up()
    {
        // Create the "prompts" table
        if (!Schema::hasTable('prompt_segments')) {
            Schema::create('prompt_segments', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('prompt_segment_order');
                $table->unsignedInteger('prompt_id');
                $table->string('segment_title');
                $table->string('segment_type');
                $table->string('segment_imageUrl')->nullable();
                $table->string('segment_url')->nullable();
                $table->text('segment_text')->nullable();
                $table->json('segment_elements')->nullable();
                $table->json('segment_key')->nullable();
                $table->json('segment_accessory');
                $table->timestamps();

                $table->foreign('prompt_id')
                    ->references('id')->on('prompts')
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
        Schema::drop('prompt_segments');
    }
}
