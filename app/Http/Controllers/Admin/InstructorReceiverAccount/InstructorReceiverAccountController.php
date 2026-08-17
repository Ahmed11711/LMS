<?php

namespace App\Http\Controllers\Admin\InstructorReceiverAccount;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\InstructorReceiverAccount\InstructorReceiverAccountStoreRequest;
use App\Http\Requests\Admin\InstructorReceiverAccount\InstructorReceiverAccountUpdateRequest;
use App\Http\Resources\Admin\InstructorReceiverAccount\InstructorReceiverAccountResource;
use App\Repositories\InstructorReceiverAccount\InstructorReceiverAccountRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $this->withRelationships = ['receiverAccount'];

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
    #[Override]
    public function store(Request $request): JsonResponse
    {
        $validated = app($this->storeRequestClass)->validated();

        try {
            DB::beginTransaction();

            $validated = $this->beforeStore($validated, $request);

            $record = $this->repository->updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'receiver_account_id' => $validated['receiver_account_id'],
                ],
                $validated
            );

            $this->afterStore($record, $request);

            DB::commit();

            $record->load($this->withRelationships);

            return $this->successResponse(
                new $this->resourceClass($record),
                'تم حفظ الحساب بنجاح'
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error creating {$this->collectionName}: " . $e->getMessage());
            return $this->errorResponse("Failed to create {$this->collectionName}: " . $e->getMessage(), 500);
        }
    }
}
