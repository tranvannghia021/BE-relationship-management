<?php
namespace App\Repositories\Mongo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MongoDB\BSON\ObjectId;

class RelationshipRepository extends MongoBaseRepository{
    protected $prefixCollection = 'relationships_', $db;

    /**
     * @return mixed
     */
    public function setCollection($suffixCollection)
    {
        $this->db = DB::connection('mongodb')
            ->collection($this->prefixCollection . $suffixCollection);
        return $this;
    }

    public function insert($payload) {
        return$this->db->insert($payload);
//        $dataBulkWrite = [];
//
//        if (!empty($payload)) {
//            foreach ($payload as $people) {
//                if (empty($people['user_id'])) {
//                    continue;
//                }
//                $dataBulkWrite[] = [
//                    'updateOne' => [
//                        ['user_id' => $people['user_id']],
//                        [
//                            '$set' => $people,
//                        ],
//                        ['upsert' => true]
//                    ]
//
//                ];
//            }
//        }
//
//        if (!empty($dataBulkWrite)) {
//            $this->db->raw(static function ($collection) use ($dataBulkWrite) {
//                return $collection->bulkWrite(
//                    $dataBulkWrite
//                );
//            });
//        }
    }

    public function find($id, $select = []) {
        if (empty($select)) {
            $select = ['*'];
        }
        return $this->db->select(
            $select)->find($id);
    }

    public function findOne() {
        return $this->db->select(
            ['_id'])->first();
    }

    public function getByFilter($filter, $paginate, $select = ['*']) {

        $rawData = $this->db->select($select);
        if(!empty($filter['keyword'])){
            $rawData->where('full_name','like','%'.trim($filter['keyword']).'%');
        }
        $rawData->orderBy('_id','DESC');
        if(!empty($filter['type'])){
           $data= $rawData->get();
        }else{
            $data= $rawData->Paginate($paginate['limit']);
        }
        return $data;
    }

    public function dropCollection($suffixCollection): void
    {
        Schema::connection('mongodb')->dropIfExists($this->prefixCollection . $suffixCollection);
    }

    public function finds($ids, $select = ['*']) {
        return $this->db->select($select)->whereIn('_id', $ids)->get()->toArray();
    }

    public function findsUpdate($ids,$payload) {
        return $this->db->whereIn('_id', $ids)->update($payload);
    }

    public function findsWithReadyToPush($ids, $select = ['*']) {
        return $this->db->select($select)->whereIn('_id', $ids)->where('ready_to_push', config('constants.READY_TO_PUSH.PRODUCT_IS_READY'))->get()->toArray();
    }

    public function deleteOne($id) {
        $dataBulkWrite = [];

        if (!empty($id)) {
//            foreach ($payload as $product) {
//                if (empty($product['_id'])) {
//                    continue;
//                }
                $dataBulkWrite[] = [
                    'deleteOne' => [
                        [
                            '_id' => new ObjectId($id)
                        ]
                    ]
                ];
//            }
        }

        if (!empty($dataBulkWrite)) {
            $this->db->raw(static function ($collection) use ($dataBulkWrite) {
                return $collection->bulkWrite(
                    $dataBulkWrite
                );
            });
        }
    }

    public function updateOne($_id, $payload) {
        $dataBulkWrite = [];
        if (!empty($payload) && !empty($_id)) {
            $dataBulkWrite[] = [
                'updateOne' => [
                    ['_id' => new ObjectId($_id)],
                    [
                        '$set' => $payload,
                    ],
                ]
            ];
        }

        if (!empty($dataBulkWrite)) {
            $this->db->raw(static function ($collection) use ($dataBulkWrite) {
                return $collection->bulkWrite(
                    $dataBulkWrite
                );
            });
        }
    }

    public function update($payload) {
        $dataBulkWrite = [];

        if (!empty($payload)) {
            foreach ($payload as $product) {
                if (empty($product['_id'])) {
                    continue;
                }
                $_id = new ObjectId($product['_id']);
                unset($product['_id']);
                $dataBulkWrite[] = [
                    'updateOne' => [
                        ['_id' => $_id],
                        [
                            '$set' => $product,
                        ]
                    ]

                ];
            }
        }

        if (!empty($dataBulkWrite)) {
            $this->db->raw(static function ($collection) use ($dataBulkWrite) {
                return $collection->bulkWrite(
                    $dataBulkWrite
                );
            });
        }
    }

    public function getUserLongTimeBySetting($day){
        return $this->db
            ->whereNotNull('last_meeting')
            ->whereNotNull('is_notification')
            ->where('last_meeting','>=',Carbon::now()->addDay(-$day)->toDateTimeString())
            ->where('is_notification',false)
            ->select([
                '_id',
                'full_name',
                'tag',
                'avatar'
            ])
            ->get()->toArray();
    }

    public function getUserLongTimeBySettingTEST($day){
        return $this->db
//            ->whereNotNull('last_meeting')
//            ->whereNotNull('is_notification')
//            ->where('last_meeting','>=',Carbon::now()->addDay(-$day)->toDateTimeString())
//            ->where('is_notification',false)
            ->select([
                '_id',
                'full_name',
                'tag',
                'avatar'
            ])
            ->get()->toArray();
    }


}
