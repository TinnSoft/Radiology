<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('comments')) {
            Schema::create('comments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('image_id') ->references('id')->on('images');
                $table->unsignedBigInteger('user_id') ->references('id')->on('users');
                $table->text('comment');            
                $table->softDeletes();
                $table->timestamps();
                $table->foreign('image_id')->references('id')->on('images');
                $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('comments');
    }
}
