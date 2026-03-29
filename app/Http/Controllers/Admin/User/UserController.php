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
    protected function applyScoping($query)
    {
        return  $user = auth('api')->user();

        if ($user->role === 'user') {
            return $query->where('id', $user->id);
        }

        if ($user->role === 'company_admin') {
            return $query->where('company_id', $user->company_id);
        }

        // لو سوبر أدمن، الميثود في الـ Base هتتنفذ وهيشوف الكل
        return parent::applyScoping($query);
    }
}
