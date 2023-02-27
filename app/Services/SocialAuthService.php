<?php
namespace App\Services;
use App\Ecommerce\Facebook\Facebook;
use App\Ecommerce\Google\Google;
use App\Traits\Response;
use Illuminate\Support\Facades\Redirect;
use Mockery\Exception;

class SocialAuthService{
    use Response;
    protected $facebook,$google;
    public function __construct(Facebook $facebook,Google $google)
    {
        $this->facebook=$facebook;
        $this->google=$google;
    }

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
                default:
                    return $this->ApiResponseError('Platform not found');
            }
            return $this->ApiResponse($result);
        }catch (Exception $exception){
            return $this->ApiResponseError($exception->getMessage());
        }
    }

        public function auth($request){
            switch ($request['state']['platform']){
                case 'facebook':
                    $this->facebook->authHandle($request);
                    break;

                case 'google':
                    $this->google->authHandle($request);
                    break;

                default:
                    return $this->ApiResponseError('Connect access denied');
            }
//            return Redirect::to(config('common.url.font_end'));
        }
}
