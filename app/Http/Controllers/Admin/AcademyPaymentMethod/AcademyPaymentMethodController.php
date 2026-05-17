<?php

namespace App\Http\Controllers\Admin\AcademyPaymentMethod;

use App\Repositories\AcademyPaymentMethod\AcademyPaymentMethodRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\AcademyPaymentMethod\AcademyPaymentMethodStoreRequest;
use App\Http\Requests\Admin\AcademyPaymentMethod\AcademyPaymentMethodUpdateRequest;
use App\Http\Resources\Admin\AcademyPaymentMethod\AcademyPaymentMethodResource;
use Illuminate\Http\Request;

class AcademyPaymentMethodController extends BaseController
{
    public function __construct(AcademyPaymentMethodRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'AcademyPaymentMethod'
        );

        $this->storeRequestClass = AcademyPaymentMethodStoreRequest::class;
        $this->updateRequestClass = AcademyPaymentMethodUpdateRequest::class;
        $this->resourceClass = AcademyPaymentMethodResource::class;
    }

    protected function beforeStore(array $data, Request $request): array
    {
        unset($data['required_fields']);

        if (isset($data['credentials']) && is_array($data['credentials'])) {
            $data['credentials'] = json_encode($data['credentials']);
        }

        return $data;
    }
}
