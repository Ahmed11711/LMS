<?php

namespace App\Http\Controllers\Admin\User;

use App\Repositories\User\UserRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\User\UserStoreRequest;
use App\Http\Requests\Admin\User\UserUpdateRequest;
use App\Http\Resources\Admin\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends BaseController
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

    protected function beforeStore(array $data, Request $request): array
    {
        $data = parent::beforeStore($data, $request);

        dd($data);
        Log::alert("SSS", [$data]);

        return $data;
    }
    protected function beforeUpdate(array $data, $existingRecord, Request $request): array
    {
        $data = parent::beforeUpdate($data, $existingRecord, $request);

        Log::alert("SSS", [$data]);

        return $data;
    }
    protected function beforeDestroy($record): void
    {
        parent::beforeDestroy($record);
        Log::alert("SSS", ["SS"]);
    }
}
