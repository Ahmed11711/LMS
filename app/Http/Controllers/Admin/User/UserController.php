<?php

namespace App\Http\Controllers\Admin\User;

use App\Repositories\User\UserRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\User\UserStoreRequest;
use App\Http\Requests\Admin\User\UserUpdateRequest;
use App\Http\Resources\Admin\User\UserResource;
use App\Services\Payment\UserSubscribeService;
use Illuminate\Http\Request;

use Override;

class UserController extends BaseController
{
    public function __construct(
        UserRepositoryInterface $repository,
        private UserSubscribeService $subscribeService,
    ) {
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

    protected function applyScoping($query)
    {
        $user = auth('api')->user();

        if ($user->role === 'admin') {
            return $query;
        }

        return $query->where('created_by', $user->id);
    }

    #[Override]
    protected function beforeStore(array $data, Request $request): array
    {
        unset($data['course_id']);
        $data['created_by'] = auth()->id();
        return $data;
    }
    #[Override]
    protected function afterStore($record, Request $request): void
    {
        if (!$request->filled('course_id')) {
            return;
        }

        $this->subscribeService->execute(
            userId: $record->id,
            courseId: $request->input('course_id'),
            customerContact: $record->email ?? $record->phone,
            payment: false,
            createdBy: auth()->id(),
            status: "active",

        );
    }
}
