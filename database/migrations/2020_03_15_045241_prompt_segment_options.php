<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class PromptSegmentOptions extends Migration
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
        if (!Schema::hasTable('prompt_segment_options')) {
            Schema::create('prompt_segment_options', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('question_id');
                $table->unsignedInteger('created_by_id')->nullable();
                $table->string('question_text');
                $table->string('state');
                $table->string('option');
                $table->string('depth');
                $table->boolean('correct');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('created_by_id')
                    ->references('id')->on('users')
                    ->onDelete('set null');

                $table->foreign('question_id')
                    ->references('id')->on('prompt_segments')
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
        Schema::drop('prompt_segment_options');
    }
}
