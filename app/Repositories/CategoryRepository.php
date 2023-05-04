<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository extends BaseRepository
{
    protected $category;
    public function __construct(Category $category)
    {
        $this->category=$category;
        parent::__construct($category);
    }
    public function getAllById($id){
       return $this->category->where('user_id',$id)->get();
    }
}
