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

    /**
     * @param array $conditions
     * @param array $select
     * @return array
     */
    public function findBy(array $conditions,array $select=['*']){
        $query=$this->user->query();
        foreach ($conditions as $key => $value){
            $query->where($key,$value);
        }
        $result=$query->select($select)->first();
        if(empty($result)) return [];
        return$result->toArray();
    }

    /**
     * @param array $conditions
     * @param array $date
     * @return array
     */
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

    /**
     * @Override updateOrInsert
     * @param $conditions
     * @param $attributes
     * @return array|object
     */
    public function updateOrInsert($conditions,$attributes):array|object {
        $users=$this->findBy($conditions,['id']);
        if(empty($users)){
            $result=$this->create($attributes);
        }else{
            $result=$this->update($users['id'],$attributes);
        }
        return $result;
    }
}
