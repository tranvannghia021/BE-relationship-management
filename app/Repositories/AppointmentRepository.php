<?php

namespace App\Repositories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use function Sodium\add;

class AppointmentRepository extends BaseRepository
{
    protected $appointment;
    public function __construct(Appointment $appointment)
    {
        parent::__construct($appointment);
        $this->appointment=$appointment;
    }
    public function getListByFilter($userId,$filter,$paginate,$select=['*']){
        $rawData=$this->appointment->where('user_id',$userId)->select($select);
        if(!empty($filter['keyword'])){
            $rawData->where('name','like','%'.$filter['keyword'].'%');
        }
        if(!empty($filter['from_date'])){
            $rawData->where('date_meeting','>=',$filter['from_date']);
        }
        if(!empty($filter['to_date'])){
            $rawData->where('date_meeting','<',$filter['to_date']);
        }
        $rawData->orderBy('id','DESC');
        return $rawData->orderBy('date','ASC')
                ->Paginate($paginate['limit']);

    }

    public function getAllAutoUpdateStatus(){
        return$this->appointment->whereNotNull('date_meeting')
            ->where('date_meeting','>=',Carbon::now()->addDay()->toDateTimeString())
            ->where('status','coming')
            ->update([
                'status'=>'cancel'
            ]);
    }

    public function getUserReadyTimeBySetting($day){
        $date=Carbon::now()->addDay($day)->toDateTimeString();
        $currentDate=Carbon::now()->toDateTimeString();
       return $this->appointment->whereNotNull('date_meeting')
           ->whereBetween('date_meeting',[$currentDate,$date])
           ->where('status','coming')
           ->where('is_notification',false)->select([
               'id',
               'name',
               'date_meeting',
               'address',
               'type',
           ])->get()
           ->toArray();
    }

    public function getUserReadyTimeBySettingTEST($day){
        return $this->appointment
//            ->whereNotNull('date_meeting')
//            ->where('date_meeting','>=',Carbon::now()->addDay($day)->toDateTimeString())
//            ->where('status','coming')
//            ->where('is_notification',false)
            ->select([
                'id',
                'name',
                'date_meeting',
                'address',
                'type',
            ])->get()
            ->toArray();
    }

    public function whereInUpdateIsNotification(array $ids){
        try {
            return $this->appointment->whereIn('id',$ids)->update([
                'is_notification'=>true
            ]);
        }catch (\Exception $exception){
            throw $exception;
        }
    }
}
