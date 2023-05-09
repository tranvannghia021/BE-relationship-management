<?php

namespace App\Services;

use App\Repositories\CategoryRepository;
use App\Traits\Response;

class TagsService
{
    use Response;
    protected $categoryRepo;
    public function __construct(CategoryRepository $categoryRepo)
    {
        $this->categoryRepo=$categoryRepo;
    }

    public function getList(array $payload){
        try {
            $tags=$this->categoryRepo->setUserId($payload['shop_id'])->getByFilter($payload['filter'])->toArray();
            $data = [
                'tags' => $tags['data'],
                'pagination' => [
                    'total'=>$tags['total'],
                    'limit'=>(int)$tags['per_page'],
                    'currentPage'=>$tags['current_page'],
                    'items'=>$tags['data'],
                    'pages'=>$payload['pagination']['page'],
                    'prev' => $tags['prev_page_url'],
                    'next' => $tags['next_page_url'],
                ],
            ];
            return $this->ApiResponse($data,"List tags success");
        }catch (\Exception $exception){
            return $this->ApiResponseError($exception->getMessage());
        }
    }

    public function create(array $payload){
        try {
            $this->categoryRepo->create($payload);
            return $this->ApiResponse([],'Create tag success');
        }catch (\Exception $exception){
            return $this->ApiResponseError($exception->getMessage());
        }
    }

    public function detail($id){
        try {
            $tag=$this->categoryRepo->find($id);
            if(empty($tag)){
                return $this->ApiResponseError("Tag not found");
            }
            return $this->ApiResponse($tag->toArray(),'Detail tag success');
        }catch (\Exception $exception){
            return $this->ApiResponseError($exception->getMessage());
        }
    }

    public function update($id,array $payload){
        try {
            $tag=$this->categoryRepo->find($id);
            if(empty($tag)){
                return $this->ApiResponseError("Tag not found");
            }
            $tag->update($payload);
            return $this->ApiResponse([],'Update tag success');
        }catch (\Exception $exception){
            return $this->ApiResponseError($exception->getMessage());
        }
    }

    public function delete($id){
        try {
            $tag=$this->categoryRepo->find($id);
            if(empty($tag)){
                return $this->ApiResponseError("Tag not found");
            }
            $tag->delete();
            return $this->ApiResponse([],'Delete tag success');
        }catch(\Exception $exception){
            return $this->ApiResponseError($exception->getMessage());
        }
    }
}
