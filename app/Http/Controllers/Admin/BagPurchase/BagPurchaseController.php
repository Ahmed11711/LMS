<?php

namespace App\Http\Controllers\Admin\BagPurchase;

use App\Repositories\BagPurchase\BagPurchaseRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\BagPurchase\BagPurchaseStoreRequest;
use App\Http\Requests\Admin\BagPurchase\BagPurchaseUpdateRequest;
use App\Http\Resources\Admin\BagPurchase\BagPurchaseResource;
use Illuminate\Http\Request;

class BagPurchaseController extends BaseController
{
    public function __construct(BagPurchaseRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'BagPurchase'
        );

        $this->storeRequestClass = BagPurchaseStoreRequest::class;
        $this->updateRequestClass = BagPurchaseUpdateRequest::class;
        $this->resourceClass = BagPurchaseResource::class;

        $this->withRelationships = ['bag', 'user'];
    }

    protected function getIndexRelationships(): array
    {
        return ['bag', 'user'];
    }

    /**
     * فلترة الـ query الأساسي حسب دور المستخدم.
     */
    protected function applyIndexFilters($query, Request $request)
    {
        $authUser = auth('api')->user();

        if ($authUser->role !== 'admin') {
            $query->whereHas('bag', function ($q) use ($authUser) {
                $q->where('user_id', $authUser->id);
            });
        }

        return $query;
    }
}
