<?php

namespace App\Http\Controllers\Admin\InstructorReceiverAccount;

use App\Repositories\InstructorReceiverAccount\InstructorReceiverAccountRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\InstructorReceiverAccount\InstructorReceiverAccountStoreRequest;
use App\Http\Requests\Admin\InstructorReceiverAccount\InstructorReceiverAccountUpdateRequest;
use App\Http\Resources\Admin\InstructorReceiverAccount\InstructorReceiverAccountResource;
use Illuminate\Http\Request;
use Override;

class InstructorReceiverAccountController extends BaseController
{
    public function __construct(InstructorReceiverAccountRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'InstructorReceiverAccount'
        );

        $this->storeRequestClass = InstructorReceiverAccountStoreRequest::class;
        $this->updateRequestClass = InstructorReceiverAccountUpdateRequest::class;
        $this->resourceClass = InstructorReceiverAccountResource::class;
    }

    #[Override]
    protected function applyScoping($query)
    {
        return $query->where('user_id', auth()->id());
    }
    #[Override]
    protected function beforeStore(array $data, Request $request): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }
}
