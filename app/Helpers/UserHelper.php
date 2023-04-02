<?php
namespace App\Helpers;
use App\Repositories\UserRepository;

class UserHelper{
    /**
     * @param $payload
     * @return bool
     */
    public static function IsUserExist($payload){
        $users= app(UserRepository::class)->findBy([
            'email'=>$payload['email'],
            'platform'=>$payload['platform'],
            'internal_id'=>$payload['internal_id'],
            'status'=>true
        ]);
        return !empty($users);
    }
}
