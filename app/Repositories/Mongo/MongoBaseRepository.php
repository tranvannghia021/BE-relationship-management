<?php
namespace App\Repositories\Mongo;
use Illuminate\Support\Facades\Schema;
use Jenssegers\Mongodb\Schema\Blueprint;

class MongoBaseRepository{
    public function createCollection(string $name){
        Schema::connection('mongodb')->table($name, static function (Blueprint $table)  {
            $table->index('user_id');
            $table->index('relationship_id');
            $table->index('category_id');
            $table->index('first_name');
            $table->index('last_name');
            $table->index('avatar');
            $table->index('birthday');
            $table->index('phone');
            $table->index('point');
            $table->index('gender')->default('male');
            $table->index('last_meeting');
            $table->index('profiles');
            $table->index('notes');
        });

    }
}
