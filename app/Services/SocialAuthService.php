<?php
namespace App\Services;
use App\Ecommerce\Facebook\Facebook;
use App\Ecommerce\Github\Github;
use App\Ecommerce\Google\Google;
use App\Helpers\Common;
use App\Traits\Response;
use Illuminate\Support\Facades\Redirect;
use Mockery\Exception;

class SocialAuthService{
    use Response;
    protected $facebook,$google,$github;
    public function __construct(Facebook $facebook,Google $google,Github $github)
    {
        $this->facebook=$facebook;
        $this->google=$google;
        $this->github=$github;
    }

    /**
     * @param $request
     * @param $platform
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateUrl($request,$platform){
        try {
            $result=[];
            $payload=[
                'platform'=>$platform,
                'uuid'=>$request->input('uuid')
            ];
            switch ($platform){
                case 'facebook':
                    $result['url']= $this->facebook->generateUrl($payload);
                    break;
                case 'google':
                    $result['url']= $this->google->generateUrl($payload);
                    break;
                case 'github':
                    $result['url']= $this->github->generateUrl($payload);
                    break;
                default:
                    return $this->ApiResponseError('Platform not found');
            }
            return $this->ApiResponse($result);
        }catch (Exception $exception){
            return $this->ApiResponseError($exception->getMessage());
        }
    }

        /**
         * @param $request
         * @return array|bool[]|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
         */
        public function auth($request){
            $error=Common::handleError($request);
            if(!$error['status']) return $error;
            switch ($request['state']['platform']){
                case 'facebook':
                    $this->facebook->authHandle($request);
                    break;

                case 'google':
                    $this->google->authHandle($request);
                    break;
                case 'github':
                    $this->github->authHandle($request);
                    break;

                default:
                    return $this->ApiResponseError('Connect access denied');
            }
            return Redirect::to(config('common.url.font_end'));
        }
}
