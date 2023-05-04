<?php

namespace App\Http\Controllers;

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
            'keyword'=>$request->input('keyword'),
            'filter'=>[
                'from_date'=>$request->input('from_date',Carbon::now()->toDateTimeString()),
                'to_date'=>$request->input('to_date',Carbon::now()->addMonth()->toDateTimeString())
            ],
            'pagination'=>[
                'limit'=>$request->input('limit',10)
            ],
            'shop_id'=>$request->input('userInfo.id')
        ];
        return $this->appointmentService->getList($payload);
    }
}
