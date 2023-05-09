<?php

namespace App\Http\Controllers;

use App\Http\Requests\Appointment\CreateRequest;
use App\Services\AppointmentService;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class AppointmentController extends Controller
{
    protected $appointmentService;
    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService=$appointmentService;
    }
    public function getList(Request $request){

        $payload=[
            'filter'=>[
                'keyword'=>$request->input('keyword'),
                'from_date'=>(new Carbon($request->input('from_date',Carbon::now()->addDay(-10)->toISOString())))->toDateTimeString(),
                'to_date'=> (new Carbon($request->input('to_date',Carbon::now()->addMonth(2)->toISOString())))->toDateTimeString()
            ],
            'pagination'=>[
                'limit'=>$request->input('limit',10),
                'page'=>$request->input('page',1),
            ],
            'shop_id'=>$request->input('userInfo.id')
        ];
        return $this->appointmentService->getList($payload);
    }

    public function getDetail(Request $request,$id){
        $payload=[
            'shop_id'=>$request->input('userInfo.id')
        ];
        return $this->appointmentService->detail($payload,$id);
    }

    public function createAppointment(CreateRequest $request){
        $request->merge([
            'shop_id'=>$request->input('userInfo.id')
        ]);
        return $this->appointmentService->create($request->only([
            'shop_id',
            'type',
            'ids_people',
            'notes',
            'date_meeting',
            'address',
            'name'
        ]));
    }

    public function update(Request $request,$id){
        $request->merge([
            'shop_id'=>$request->input('userInfo.id')
        ]);
        return $this->appointmentService->update($request->only([
            'shop_id',
            'type',
            'ids_people',
            'notes',
            'date_meeting',
            'address',
            'name'
        ]),$id);
    }

    public function delete(Request $request,$id){

        return $this->appointmentService->delete($id);
    }
}
