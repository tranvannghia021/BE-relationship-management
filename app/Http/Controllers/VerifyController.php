<?php

namespace App\Http\Controllers;

use App\Services\VerifyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class VerifyController extends Controller
{
    protected $verifyService;
    public function __construct(VerifyService $verifyService)
    {
        $this->verifyService=$verifyService;
    }

    public function handleVerify(Request $request){
       return $this->verifyService->Verify($request);
    }

    public function handleVerifyForgotPassword(Request $request){
        $this->verifyService->VerifyForgotPassword($request);
        return Redirect::to(config('common.font_end'));
    }
}
