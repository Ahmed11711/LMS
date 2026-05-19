<?php

namespace App\Http\Controllers\Admin\AcademyWithdraw;

use App\Repositories\AcademyWithdraw\AcademyWithdrawRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\AcademyWithdraw\AcademyWithdrawStoreRequest;
use App\Http\Requests\Admin\AcademyWithdraw\AcademyWithdrawUpdateRequest;
use App\Http\Resources\Admin\AcademyWithdraw\AcademyWithdrawResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Override;
use Illuminate\Support\Str;

class AcademyWithdrawController extends BaseController
{
    public function __construct(AcademyWithdrawRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'AcademyWithdraw',
            fileFields: ['receipt_image']
        );

        $this->storeRequestClass = AcademyWithdrawStoreRequest::class;
        $this->updateRequestClass = AcademyWithdrawUpdateRequest::class;
        $this->resourceClass = AcademyWithdrawResource::class;
    }

    #[Override]
    protected function beforeStore(array $data, Request $request): array
    {
        $tenant = app('tenant');
        $data['academy_id'] = $tenant->id; // هنا بتجيب id الأكاديمية من الـ tenant
        $data['transaction_id'] = 'ACW-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        // باقي الـ balance check هنا

        return $data;
    }
}
