<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateUrlRequest;
use App\Services\SocialAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;


class SocialAuthController extends Controller
{

    protected $socialAuthService;
    public function __construct(SocialAuthService $socialAuthService)
    {
        $this->socialAuthService=$socialAuthService;

    }

    /**
     * @param GenerateUrlRequest $request
     * @param $platform
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateUrl(GenerateUrlRequest $request, $platform){
        return $this->socialAuthService->generateUrl($request,$platform);
    }

    /**
     * @param Request $request
     * @return array|bool[]|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function auth(Request $request){
      return $this->socialAuthService->auth($request->all());
    }
}
