<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Repositories\Setting\SettingRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Setting\SettingStoreRequest;
use App\Http\Requests\Admin\Setting\SettingUpdateRequest;
use App\Http\Resources\Admin\Setting\SettingResource;

class SettingController extends BaseController
{
    public function __construct(SettingRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Setting'
        );

        $this->storeRequestClass = SettingStoreRequest::class;
        $this->updateRequestClass = SettingUpdateRequest::class;
        $this->resourceClass = SettingResource::class;
    }
}
