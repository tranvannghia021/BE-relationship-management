<?php
namespace App\Helpers;
class PusherHelper{
    /**
     * @param $id
     * @param $data
     * @param $prefix
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Pusher\ApiErrorException
     * @throws \Pusher\PusherException
     */
    public static function pusher($id,$data,$prefix='auth_'){
        Common::pushSocket(
            config('services.pusher.channel'),
            config('services.pusher.event').$prefix.$id,
            $data);
    }
}
