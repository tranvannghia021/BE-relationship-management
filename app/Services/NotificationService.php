<?php

namespace App\Services;

use App\Repositories\Mongo\NotificationRepository;
use App\Traits\Response;
use MongoDB\BSON\ObjectId;

class NotificationService
{
    use Response;
    protected $notificationRepo;
    public function __construct(NotificationRepository $notificationRepo)
    {
        $this->notificationRepo=$notificationRepo;

    }
    public function getAll(array $payload){
        $notification=$this->notificationRepo->setCollection($payload['shop_id'])
            ->getByFilter($payload['filter'])->toArray();
        $data=[];
        if(!empty($notification)){
            foreach ($notification as $item){
                $data[]=[
                    '_id'=>(string)new ObjectId($item['_id']),
                    'type'=>$item['type'],//long_time,ready_time
                    'title'=>$item['title'],
                    'info'=>$item['info'],
                    'created_at'=>$item['created_at']
                ];
            }
        }

        return $this->ApiResponse($data,"Notification...");
    }

    public function create(array $payload){
        $this->notificationRepo->setCollection($payload['shop_id'])
            ->insert($payload);
    }
}
