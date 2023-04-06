<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateUrlRequest;
use App\Services\SocialAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use OpenApi\Annotations as OA;


class SocialAuthController extends Controller
{

    protected $socialAuthService;
    public function __construct(SocialAuthService $socialAuthService)
    {
        $this->socialAuthService=$socialAuthService;

    }

    /**
     * @OA\Post(
     *     path="/api/{platform}/generate-url",
     *      summary="Generate url",
     *      description="Generate link social auth",
     *      operationId="generateUrl",
     *      tags={"auth"},
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Uuid session",
     *     @OA\JsonContent(
     *     required={"uuid"},
     *     @OA\Property(property="uuid",type="string",format="string",example="aadajdhs37487tfdf")
     *      ),
     * ),
     *     @OA\Response(
     *     response="200",
     *     description="link social auth",
     *     @OA\JsonContent(
     *     @OA\Property(property="status",type="boolean",format="boolean",example=false),
     *     @OA\Property(property="message",type="string",format="string",example="link social auth")
     * )
     * )
     * )
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
