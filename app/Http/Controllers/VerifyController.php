<?php

namespace App\Http\Controllers;

use App\Services\VerifyService;
use Illuminate\Http\Request;

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
}
