<?php

namespace App\Services;

use App\Repositories\AppointmentRepository;
use App\Repositories\Mongo\RelationshipRepository;
use App\Traits\Response;
use MongoDB\BSON\ObjectId;

class AppointmentService
{
    use Response;
    protected $appointmentRepository,$relationshipRepository;

    public function __construct(AppointmentRepository $appointmentRepository,RelationshipRepository $relationshipRepository)
    {
        $this->appointmentRepository=$appointmentRepository;
        $this->relationshipRepository=$relationshipRepository;
    }

    public function getList(array $payload){
        $appointments=$this->appointmentRepository->getListByFilter($payload['shop_id'],$payload['filter'],$payload['pagination']);
        $array=$appointments->toArray();
        dd($array);//todo
        $relationShipId=array_column($array['data'],'relationship_id');
        $relationships=$this->relationshipRepository->setCollection($payload['shop_id'])->finds($relationShipId,[
            'avatar','email','phone','_id'
        ]);
        if(!empty($relationships)){
            foreach ($relationships as $relationship){
                foreach ($array['data'] as $key => $item){
                    if(new ObjectId($relationShipId['_id']) === $item['relationship_id']){
                        $array['data'][$key]['info']=$relationship;
                    }

                }
            }
        }
        $data=[
            'appointment'=>$appointments->groupBy('date')->toArray(),
            'pagination'=>[
                'next'=>$array['next_page_url'],
                'prev'=>$array['prev_page_url'],
            ]
        ];
        return $this->ApiResponse($data,"List appointment");
    }
}
