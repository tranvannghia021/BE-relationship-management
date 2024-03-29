<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRelationshipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relationships', function (Blueprint $table) {
            // move mongo
//        Schema::create('relationships', function (Blueprint $table) {
//            $table->id();
//            $table->unsignedBigInteger('user_id')->index();
//            $table->foreign('user_id')->references('id')->on('users');
//            $table->integer('relationship_id')->default(0);
//            $table->unsignedBigInteger('category_id');
//            $table->foreign('category_id')->references('id')->on('categories');
//            $table->string('first_name')->nullable();
//            $table->string('last_name')->nullable();
//            $table->text('avatar')->nullable();
//            $table->date('birthday')->nullable();
//            $table->bigInteger('phone')->nullable();
//            $table->bigInteger('point')->default(0);
//            $table->enum('gender',['male','female','other'])->default('male');
//            $table->date('last_meeting')->nullable();
//            $table->jsonb('profiles')->nullable();
//            $table->jsonb('notes')->nullable();
//            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('relationships');
    }
}
