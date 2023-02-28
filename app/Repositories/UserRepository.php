<?php
namespace App\Repositories;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Mockery\Exception;

class UserRepository extends BaseRepository {
    protected $user;
    public function __construct(User $user)
    {
        $this->user=$user;
        parent::__construct($user);
    }

    public function findBy(array $conditions,array $select=['*']){
        $query=$this->user->query();
        foreach ($conditions as $key => $value){
            $query->where($key,$value);
        }
        $result=$query->select($select)->get();
        if(empty($result)) return [];
        return$result->toArray();
    }

    public function updateBy(array $conditions,array $date){
        try {
            $query=$this->user->query();
            foreach ($conditions as $key => $value){
                $query->where($key,$value);
            }
            return[
                'status'=>true,
                'data'=>$query->update($date)
            ];
        }catch (Exception $exception){
            return [
                'status'=>false,
                'error'=>$exception->getMessage()
            ];
        }
    }
}
