<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tags\CreateRequest;
use App\Services\TagsService;
use Illuminate\Http\Request;

class TagsController extends Controller
{
    protected $tagService;
    public function __construct(TagsService $tagsService)
    {
        $this->tagService=$tagsService;
    }


    public function index(Request $request)
    {
        $payload=[
            'filter'=>[
                'keyword'=>$request->input('keyword'),
                'limit'=>$request->input('limit',10),

            ],
            'pagination'=>[
                'page'=>$request->input('page',1),
            ],
            'shop_id'=>$request->input('userInfo.id')
        ];
        return $this->tagService->getList($payload);
    }


    public function store(CreateRequest $request)
    {
        $request->merge(['user_id'=>$request->input('userInfo.id')]);
        return $this->tagService->create($request->only(['user_id','name']));
    }

    public function show($id)
    {
        return $this->tagService->detail($id);
    }


    public function update(CreateRequest $request, $id)
    {
        return $this->tagService->update($id,$request->only([
            'name'
        ]));
    }


    public function destroy($id)
    {
        return $this->tagService->delete($id);
    }
}
