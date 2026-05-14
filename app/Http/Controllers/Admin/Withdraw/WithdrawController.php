<?php

namespace App\Http\Controllers\Admin\Withdraw;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\UserWithdraw\UserWithdrawStoreRequest;
use App\Http\Requests\Admin\Withdraw\WithdrawRequest;
use App\Http\Resources\Admin\UserWithdraw\UserWithdrawResource;
use App\Repositories\UserWithdraw\UserWithdrawRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Override;

class WithdrawController  extends BaseController
{
    public function __construct(UserWithdrawRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'UserWithdraw',
            fileFields: ['receipt_image'],


        );
        $this->withRelationships = ['user:id,name,email'];
        $this->storeRequestClass = UserWithdrawStoreRequest::class;
        $this->updateRequestClass = WithdrawRequest::class;
        $this->resourceClass = UserWithdrawResource::class;
    }

    #[Override]
    protected function beforeUpdate(array $data, $existingRecord, Request $request): array
    {
        $data['admin_id'] =  auth()->id();
        if ($existingRecord->status !== 'pending') {
            abort(422, "This request has already been processed (Accepted or Rejected) and cannot be modified.");
        }
        return $data;
    }

    protected function afterUpdate($updatedRecord, $oldRecord, Request $request): void
    {

        if ($updatedRecord->status === 'rejected') {

            $user = $updatedRecord->user;

            if ($user) {

                $userBalance = $user->balance;

                if ($userBalance) {
                    $userBalance->increment('available_balance', $updatedRecord->amount);
                } else {
                }
            }
        }
    }

    #[Override]
    protected function getIndexRelationships(): array
    {
        return [
            'userPaymentInfo'
        ];
    }
}
