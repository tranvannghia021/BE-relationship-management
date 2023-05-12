<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository extends BaseRepository
{
    protected $category,$userId;
    public function __construct(Category $category)
    {
        $this->category=$category;
        parent::__construct($category);
    }
    public function setUserId(int $id):CategoryRepository{
        $this->userId=$id;
        return $this;
    }
    public function getAllById($id){
       return $this->category->where('user_id',$id)->get();
    }

    public function getByFilter($filter,$select=["*"]){
        $query=$this->category->select($select)->where('user_id',$this->userId);
        if(!empty($filter['keyword'])){
            $query->where('name','like','%'.$filter['keyword']."%");
        }
        return $query->Paginate($filter['limit']);
    }
}
