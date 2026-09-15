<?php

namespace App\Http\Controllers\User\Bags\Bags;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Resources\User\Bag\BagResource;
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

        $this->hasGallery = true;

        $this->withRelationships = ['items', 'gallery', 'category', 'purchases'];
    }

    protected function getIndexRelationships(): array
    {
        return [
            'purchases' => function ($query) {
                $query->where('user_id', auth('api')->id());
            },
        ];
    }

    protected function getShowRelationships(): array
    {
        return array_merge($this->withRelationships, [
            'purchases' => function ($query) {
                $query->where('user_id', auth('api')->id());
            },
        ]);
    }
}
