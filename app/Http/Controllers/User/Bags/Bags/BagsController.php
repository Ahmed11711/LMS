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

        $this->withRelationships = [
            'category',
        ];
    }

    /**
     * index: العلاقات الأساسية بس (خفيف)
     */
    protected function getIndexRelationships(): array
    {
        return $this->withRelationships;
    }

    /**
     * show: العلاقات الأساسية + التقيلة
     */
    protected function getShowRelationships(): array
    {
        return array_merge($this->withRelationships, [
            'items',
            'gallery',
            'userPaymentInfos.receiverAccount',
            'purchases' => function ($query) {
                $query->where('user_id', auth('api')->id());
            },
        ]);
    }


    protected function afterStore($record, Request $request): void
    {
        $record->load($this->getShowRelationships());
    }


    protected function afterUpdate($updatedRecord, $oldRecord, Request $request): void
    {
        $updatedRecord->load($this->getShowRelationships());
    }
}
