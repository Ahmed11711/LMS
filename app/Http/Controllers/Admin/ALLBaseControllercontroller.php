<?php

namespace App\Http\Controllers\Admin;

use App\Repositories\User\UserRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\User\UserStoreRequest;
use App\Http\Requests\Admin\User\UserUpdateRequest;
use App\Http\Resources\Admin\User\UserResource;
use Illuminate\Http\Request;

class ALLBaseControllercontroller extends BaseController
{
    public function __construct(UserRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'User',
            fileFields: ['profile_image'],

        );

        $this->storeRequestClass = UserStoreRequest::class;
        $this->updateRequestClass = UserUpdateRequest::class;
        $this->resourceClass = UserResource::class;
    }

    // get if user get for user 

    protected function applyScoping($query)
    {
        $user = auth('api')->user();

        if ($user->role === 'user') {
            return $query->where('id', $user->id);
        }

        if ($user->role === 'company_admin') {
            return $query->where('company_id', $user->company_id);
        }

        return parent::applyScoping($query);
    }

    protected function beforeStore(array $data, Request $request): array
    {
        $data = parent::beforeStore($data, $request);

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        return $data;
    }
    protected function beforeUpdate(array $data, $existingRecord, Request $request): array
    {
        $data = parent::beforeUpdate($data, $existingRecord, $request);

        if ($existingRecord->is_super_admin && isset($data['email'])) {
            unset($data['email']);
        }

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        return $data;
    }
    protected function beforeDestroy($record): void
    {
        parent::beforeDestroy($record);
    }
}
