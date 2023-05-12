<?php

namespace App\Services;

use App\Jobs\OrtherJob;
use App\Repositories\AppointmentRepository;
use App\Repositories\Mongo\RelationshipRepository;
use App\Traits\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
        try {
            $appointments=$this->appointmentRepository->getListByFilter($payload['shop_id'],$payload['filter'],$payload['pagination'],
                [
                    'id',
                    'relationship_ids',
                    'type',
                    'address',
                    'name',
                    'notes',
                    'status',
                    'date_meeting',
                    DB::raw("to_char(date_meeting, 'MM-YYYY') AS date")
                ]);
            $array=$appointments->toArray();
            $items=$appointments->groupBy('date')->toArray();
            $data=[
                'appointment'=>$items,
                'pagination'=>[
                    'total'=>$array['total'],
                    'limit'=>(int)$array['per_page'],
                    'currentPage'=>$array['current_page'],
                    'items'=>$items,
                    'pages'=>$payload['pagination']['page'],
                    'prev' => $array['prev_page_url'],
                    'next' => $array['next_page_url'],
                ]
            ];
            return $this->ApiResponse($data,"List appointment");
        }catch (\Exception $exception){
            return $this->ApiResponseError($exception->getMessage());
        }
    }

    public function detail(array $payload,$id){
        $appointment=$this->appointmentRepository->find($id);
        if(empty($appointment)){
            return $this->ApiResponseError("Appointment not found");
        }
        $relationShips=[];
        if(!empty($appointment['relationship_ids'])){
            $relationShips=$this->relationshipRepository->setCollection($payload['shop_id'])
                ->finds($appointment['relationship_ids']);

        }

        $temp=[];

        if(!empty($relationShips)){
            foreach ($relationShips as $item){
                $temp[]=[
                    '_id'=>(string)new ObjectId($item['_id']),
                    'tag'=>$item['category_name'],
                    'full_name'=>$item['full_name'],
                    'avatar'=>$item['avatar'],
                    'phone'=>$item['phone'],
                    'email'=>$item['email'],
                ];
            }
        }
        $appointment['contribute']=$temp;
        return $this->ApiResponse($appointment,'Detail appointment');
    }

    public function create(array $payload){
        $payload['relationship_ids']=$payload['ids_people'];
        $payload['user_id']=$payload['shop_id'];
        unset($payload['ids_people'],$payload['shop_id']);

        $this->appointmentRepository->create($payload);

        return $this->ApiResponse([],"Create appointment success");
    }


    public function update(array $payload,$id){
        $appointment=$this->appointmentRepository->find($id);
        if(empty($appointment)){
            return $this->ApiResponseError("Appointment not found");
        }
        $payload['relationship_ids']=$payload['ids_people'];
        unset($payload['ids_people'],$payload['shop_id']);
        $appointment->update($payload);
        return $this->ApiResponse([],"Update appointment success");
    }


    public function delete(int $id){
        $appointment=$this->appointmentRepository->find($id);
        if(empty($appointment)){
            return $this->ApiResponseError("Appointment not found");
        }

        $appointment->delete();
        return $this->ApiResponse([],"Delete appointment success");
    }

    public function status(int $id){
        $appointment=$this->appointmentRepository->find($id);

        if(empty($appointment)){
            return $this->ApiResponseError("Appointment not found");
        }

        OrtherJob::dispatch($appointment,'updateLastMeeting');

        $appointment->update([
            'status'=>'done',
            'is_notification'=>false,
        ]);
        return $this->ApiResponse([],"Update status appointment success");
    }

    public function updateLastMeeting($userId,$ids){
       return $this->relationshipRepository->setCollection($userId)->findsUpdate($ids,[
            'last_meeting'=>Carbon::now()->toDateTimeString()
        ]);
    }


}
