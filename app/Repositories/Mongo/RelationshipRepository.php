<?php
namespace App\Repositories\Mongo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MongoDB\BSON\ObjectId;

class RelationshipRepository extends MongoBaseRepository{
    protected $prefixCollection = 'relationship_', $db;

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
        $dataBulkWrite = [];

        if (!empty($payload)) {
            foreach ($payload as $product) {
                if (empty($product['shopify_product_id'])) {
                    continue;
                }
                $dataBulkWrite[] = [
                    'updateOne' => [
                        ['shopify_product_id' => $product['shopify_product_id']],
                        [
                            '$set' => $product,
                        ],
                        ['upsert' => true]
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

        $importListings = $this->db->select($select);

        if (!empty($filter['quantity_from'])) {
            $importListings->where('quantity', '>=', (integer)$filter['quantity_from']);
        }

        if (!empty($filter['quantity_to'])) {
            $importListings->where('quantity', '<=', (integer)$filter['quantity_to']);
        }

        if (!empty($filter['shipping_template_ids'])) {
            $filter['shipping_template_ids'] = array_map('intval', $filter['shipping_template_ids']);
            $importListings->whereIn('shipping_profile_id', $filter['shipping_template_ids']);
        }

        if (!empty($filter['profile_template_ids'])) {
            $filter['profile_template_ids'] = array_map('intval', $filter['profile_template_ids']);
            $importListings->whereIn('profile_template_id', $filter['profile_template_ids']);
        }

        if (@$filter['shopify_status'] !== null && @$filter['shopify_status'] !== '') {
            $importListings->where('shopify_status', (integer)$filter['shopify_status']);
        }

        if (!empty($filter['keyword'])) {
            $filter['keyword'] = trim($filter['keyword']);
            $importListings->where('title', 'like', "%{$filter['keyword']}%");
        }

        if (@$filter['ready_to_push'] !== null && @$filter['ready_to_push'] !== '') {
            if ((integer)$filter['ready_to_push'] === config('constants.READY_TO_PUSH.ERRORS_ON_ETSY')) {
                $importListings->where('is_error', 1);
            }else {
                $importListings->where('ready_to_push', (integer)$filter['ready_to_push']);
            }
        }

        $hasOrderBy = false;

        if (@$filter['sort_quantity'] !== null && @$filter['sort_quantity'] !== '') {
            if ((integer)$filter['sort_quantity'] === 0) {
                $hasOrderBy = true;
                $importListings->orderBy('quantity', $paginate['page_status'] === 'next' ? 'ASC' : 'DESC');
            }else if ((integer)$filter['sort_quantity'] === 1) {
                $hasOrderBy = true;
                $importListings->orderBy('quantity', $paginate['page_status'] === 'next' ? 'DESC' : 'ASC');
            }
        }

        if (!$hasOrderBy) {
            if (!empty($paginate['prev'])) {
                $importListings->where('_id', '>=', $paginate['prev']);
            }

            if (!empty($paginate['next'])) {
                $importListings->where('_id', '<', $paginate['next']);
            }

            $importListings->orderBy('_id', $paginate['page_status'] === 'next' ? 'DESC' : 'ASC')->take($paginate['limit'] + 1);
        }else {
            $importListings->skip($paginate['limit'] * ($paginate['page'] - 1))->take($paginate['limit'] + 1);
        }

        return $importListings->get()->toArray();
    }

    public function dropCollection($suffixCollection): void
    {
        Schema::connection('mongodb')->dropIfExists($this->prefixCollection . $suffixCollection);
    }

    public function finds($ids, $select = ['*']) {
        return $this->db->select($select)->whereIn('_id', $ids)->get()->toArray();
    }

    public function findsWithReadyToPush($ids, $select = ['*']) {
        return $this->db->select($select)->whereIn('_id', $ids)->where('ready_to_push', config('constants.READY_TO_PUSH.PRODUCT_IS_READY'))->get()->toArray();
    }

    public function deleteOne($payload) {
        $dataBulkWrite = [];

        if (!empty($payload)) {
            foreach ($payload as $product) {
                if (empty($product['_id'])) {
                    continue;
                }
                $dataBulkWrite[] = [
                    'deleteOne' => [
                        [
                            '_id' => new ObjectId($product['_id'])
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
}
