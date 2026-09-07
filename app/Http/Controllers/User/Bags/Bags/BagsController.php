<?php

namespace App\Http\Controllers\User\Bags\Bags;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Bag\BagResource;
use App\Repositories\Bag\BagRepositoryInterface;
use Illuminate\Http\Request;

class BagsController extends BaseController
{
    public function __construct(BagRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Bag',
            fileFields: ['image']
        );


        $this->resourceClass = BagResource::class;

        $this->isUserBound = true;
        $this->hasGallery = true;

        $this->withRelationships = ['items', 'userPaymentInfos', 'gallery', 'category'];
    }

    /**
     */
    protected function getIndexRelationships(): array
    {
        return [];
    }
}
