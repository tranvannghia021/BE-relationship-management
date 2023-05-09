<?php

namespace App\Repositories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
        return $rawData->orderBy('date','DESC')
                ->Paginate($paginate['limit']);

    }
}
