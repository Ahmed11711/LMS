<?php

namespace App\Http\Controllers\Instructor\UserWithdraw;

use App\Repositories\UserWithdraw\UserWithdrawRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\UserWithdraw\UserWithdrawStoreRequest;
use App\Http\Requests\Admin\UserWithdraw\UserWithdrawUpdateRequest;
use App\Http\Resources\Admin\UserWithdraw\UserWithdrawResource;
use App\Models\UserBalance;
use App\Models\UserPaymentInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserWithdrawController extends BaseController
{
    public function __construct(UserWithdrawRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'UserWithdraw'
        );
        $this->isUserBound = true;
        $this->storeRequestClass = UserWithdrawStoreRequest::class;
        $this->updateRequestClass = UserWithdrawUpdateRequest::class;
        $this->resourceClass = UserWithdrawResource::class;
    }

    protected function beforeStore(array $data, Request $request): array
    {
        $data['user_id'] = auth()->id();

        $paymentInfo = UserPaymentInfo::where('id', $data['user_payment_info_id'])
            ->where('user_id', auth()->id())
            ->first();

        if (!$paymentInfo) {
            abort(403, 'Payment info not found or unauthorized');
        }

        $balance = UserBalance::where('user_id', auth()->id())
            ->lockForUpdate() //  
            ->first();
        if (!$balance || $balance->available_balance < $data['amount']) {
            abort(422, 'Insufficient balance');
        }
        $data['transaction_id'] = 'WDR-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        return $data;
    }

    protected function afterStore($record, Request $request): void
    {
        UserBalance::where('user_id', auth()->id())
            ->decrement('available_balance', $record->amount);
    }
    public function applyScoping($query)
    {

        return $query->where('user_id', auth()->id());
    }
}
