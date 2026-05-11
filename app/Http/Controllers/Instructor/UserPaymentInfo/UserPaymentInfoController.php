<?php

namespace App\Http\Controllers\Instructor\UserPaymentInfo;

use App\Repositories\UserPaymentInfo\UserPaymentInfoRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\UserPaymentInfo\UserPaymentInfoStoreRequest;
use App\Http\Requests\Admin\UserPaymentInfo\UserPaymentInfoUpdateRequest;
use App\Http\Resources\Admin\UserPaymentInfo\UserPaymentInfoResource;
use Illuminate\Http\Request;

class UserPaymentInfoController extends BaseController
{
    public function __construct(UserPaymentInfoRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'UserPaymentInfo'
        );
        $this->isUserBound = true;
        $this->storeRequestClass = UserPaymentInfoStoreRequest::class;
        $this->updateRequestClass = UserPaymentInfoUpdateRequest::class;
        $this->resourceClass = UserPaymentInfoResource::class;
    }

    protected function beforeStore(array $data, Request $request): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }


    public function applyScoping($query)
    {

        return $query->where('user_id', auth()->id());
    }
}
