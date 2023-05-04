<?php

namespace App\Services;

use App\Helpers\Common;
use App\Repositories\Mongo\RelationshipRepository;
use App\Traits\Response;
use MongoDB\BSON\ObjectId;

class RelationShipService
{
    use Response;

    protected $relationShipRepository;

    public function __construct(RelationshipRepository $relationShipRepository)
    {
        $this->relationShipRepository = $relationShipRepository;
    }

    public function getList(array $payload)
    {
        try {
            $listRela = $this->relationShipRepository->setCollection($payload['shop_id'])
                ->getByFilter($payload['filter'], $payload['pagination'], [
                    '_id',
                    'avatar',
                    'last_meeting',
                    'notes',
                    'category_name',
                    'full_name'
                ])->toArray();

            $people = [];
            if (!empty($listRela['data'])) {
                foreach ($listRela['data'] as $user) {
                    $people[] = [
                        '_id' => (string)new ObjectId($user['_id']),
                        'avatar' => @$user['avatar'],
                        'last_meeting' => @$user['last_meeting'],
                        'notes' => $user['notes'],
                        'tag' => $user['category_name'],
                        'full_name' => $user['full_name']
                    ];
                }
            }
            $data = [
                'tags' => Common::getTags($payload['shop_id']) ?? [],
                'people' => $people,
                'pagination' => [
                    'prev' => $listRela['prev_page_url'],
                    'next' => $listRela['next_page_url'],
                ],
            ];
            return $this->ApiResponse($data, 'List relationship');
        } catch (\Exception $exception) {
            return $this->ApiResponseError('Error,Please try again');
        }
    }

    public function getDetail(array $payload)
    {
        $userRaw = $this->relationShipRepository->setCollection($payload['shop_id'])
            ->find($payload['id'], [
                '_id',
                'avatar',
                'date_meeting',
                'notes',
                'category_name',
                'full_name',
                'email',
                'phone'
            ]);
        if (empty($userRaw)) {
            return $this->ApiResponseError("People not found");
        }
        $data = [
            '_id' => (string)new ObjectId($userRaw['_id']),
            'avatar' => @$userRaw['avatar'],
            'date_meeting' => $userRaw['date_meeting'],
            'notes' => $userRaw['notes'],
            'email' => $userRaw['email'],
            'phone' => $userRaw['phone'],
            'tag' => $userRaw['category_name'],
            'full_name' => @$userRaw['full_name']
        ];
        return $this->ApiResponse($data);
    }

    public function createPeople(array $payload)
    {
        $avatar = null;
        if (!empty($payload['avatar'])) {
            $avatar = Common::saveImgBase64('relationship', $payload['avatar']);
            if ($avatar === false) {
                return $this->ApiResponseError("Type image is invalid");
            }
            $avatar = config('app.url') . '/storage/relationship/' . $avatar;
        }
        $payloadInsert[] = [
            'user_id' => $payload['shop_id'],
            'relationship_id' => 0,
            'category_name' => $payload['tag'],
            'full_name' => $payload['full_name'],
            'avatar' => $avatar,
            'phone' => $payload['phone'],
            'date_meeting' => $payload['date_meeting'],
            'email' => $payload['email'],
            'notes' => $payload['notes'],
        ];
        $this->relationShipRepository->setCollection($payload['shop_id'])
            ->insert($payloadInsert);
        return $this->ApiResponse([], 'Create people success', 201);
    }

    public function updatePeople($id,array $payload){
        $payloadInsert = [
            'user_id' => $payload['shop_id'],
            'relationship_id' => 0,
            'category_name' => $payload['tag'],
            'full_name' => $payload['full_name'],
            'phone' => $payload['phone'],
            'date_meeting' => $payload['date_meeting'],
            'email' => $payload['email'],
            'notes' => $payload['notes'],
        ];
        if (!empty($payload['avatar'])) {
            $avatar = Common::saveImgBase64('relationship', $payload['avatar']);
            if ($avatar === false) {
                return $this->ApiResponseError("Type image is invalid");
            }
            $payloadInsert['avatar'] = config('app.url') . '/storage/relationship/' . $avatar;
        }
        $this->relationShipRepository->setCollection($payload['shop_id'])
            ->updateOne($id,$payloadInsert);
        return $this->ApiResponse([], 'Update people success');
    }
}