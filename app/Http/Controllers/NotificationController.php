<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService=$notificationService;
    }
    public function getList(Request $request){
        $payload=[
            'shop_id'=>$request->input('userInfo.id'),
            'filter'=>[
                'keyword'=>$request->input('keyword')
            ]
        ];
        return $this->notificationService->getAll($payload);
    }
}
