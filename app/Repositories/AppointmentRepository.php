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
        $rawData=$this->appointment->where('user_id',$userId)->select([
            'id',
            'relationship_id',
            'name',
            'address',
            'notes',
            'time',
            DB::raw("to_char(created_at, 'YYYY-MM') AS date")
        ]);
        if(!empty($filter['from_date'])){
            $rawData->where('time','>=',$filter['from_date']);
        }
        if(!empty($filter['to_date'])){
            $rawData->where('time','<',$filter['to_date']);
        }
        return $rawData->orderBy('date','DESC')
                ->simplePaginate($paginate['limit']);

    }
}
