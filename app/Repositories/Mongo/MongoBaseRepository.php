<?php

namespace App\Repositories\Mongo;

use Illuminate\Support\Facades\Schema;
use Jenssegers\Mongodb\Schema\Blueprint;

class MongoBaseRepository
{
    private $prefix = 'relationships_';

    public function __construct()
    {
    }

    public function createCollection(int $id)
    {
        Schema::connection('mongodb')->table($this->prefix . $id, static function (Blueprint $table) {
            $table->index('user_id');
            $table->index('relationship_id');
            $table->index('category_id');
            $table->index('category_name');
            $table->index('first_name');
            $table->index('last_name');
            $table->index('full_name');
            $table->index('avatar');
            $table->index('birthday');
            $table->index('phone');
            $table->index('email');
            $table->index('point');
            $table->index('gender')->default('male');
            $table->index('last_meeting');
            $table->index('date_meeting');
            $table->index('profiles');
            $table->index('notes');
            $table->index('is_notification');
        });

    }

    public function collectionExist(int $id)
    {
        return Schema::connection('mongodb')->hasTable($this->prefix . $id);
    }

    public function dropCollection(int $id)
    {
        Schema::connection('mongodb')->drop($this->prefix . $id);
    }
}
