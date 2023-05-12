<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use App\Http\Requests\Relationship\CreatePayloadPeopleRequest;
use App\Services\RelationShipService;
use Illuminate\Http\Request;

class RelationShipController extends Controller
{
    protected $relationShipService;
    public function __construct(RelationShipService $relationShipService)
    {
        $this->relationShipService=$relationShipService;
    }
    public function getList(Request $request){
        $payload=[
            'filter'=>[
                'keyword'=>$request->input('keyword',''),
                'type'=>$request->input('type'),
            ],
            'pagination'=>[
                'limit'=>$request->input('limit',10),
                'page'=>$request->input('page',1),
//                'next'=>$request->input('next'),
//                'prev'=>$request->input('prev'),
//                'has_prev_next'=>!(@$request->input('prev')===null && @$request->input('next') === null),
            ],
            'shop_id'=>$request->input('userInfo.id')
        ];
        return $this->relationShipService->getList($payload);
    }

    public function getDetail(Request $request,$id){
        $payload=[
            'shop_id'=>$request->input('userInfo.id'),
            'id'=>$id,
            'user_info'=>$request->input('userInfo')
        ];
        return $this->relationShipService->getDetail($payload);
    }

    public function createPeople(CreatePayloadPeopleRequest $request){
        $payload=[
            'full_name'=>$request->input('name'),
            'tag'=>$request->input('tag'),
            'first_meeting'=>$request->input('first_meeting'),
            'email'=>$request->input('email'),
            'phone'=>$request->input('phone'),
            'avatar'=>$request->input('avatar'),
            'notes'=>$request->input('notes'),
            'shop_id'=>$request->input('userInfo.id')
        ];
        return $this->relationShipService->createPeople($payload);
    }

    public function updatePeople(Request $request,$id){
        $payload=[
            'full_name'=>$request->input('name'),
            'tag'=>$request->input('tag'),
            'first_meeting'=>$request->input('first_meeting'),
            'email'=>$request->input('email'),
            'phone'=>$request->input('phone'),
            'avatar'=>$request->input('avatar'),
            'notes'=>$request->input('notes'),
            'shop_id'=>$request->input('userInfo.id')
        ];
        return $this->relationShipService->updatePeople($id,$payload);
    }

    public function deletePeople(Request $request,$id){
        $payload=[
            'shop_id'=>$request->input('userInfo.id')
        ];
        return $this->relationShipService->deletePeople($id,$payload);
    }
}
