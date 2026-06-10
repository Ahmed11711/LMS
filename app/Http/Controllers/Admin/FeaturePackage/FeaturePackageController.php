<?php

namespace App\Http\Controllers\Admin\FeaturePackage;

use App\Repositories\FeaturePackage\FeaturePackageRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\FeaturePackage\FeaturePackageStoreRequest;
use App\Http\Requests\Admin\FeaturePackage\FeaturePackageUpdateRequest;
use App\Http\Resources\Admin\FeaturePackage\FeaturePackageResource;
use App\Models\Central\FeaturePackage;
use App\Models\Central\Features;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FeaturePackageController extends BaseController
{
    public function __construct(FeaturePackageRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'FeaturePackage'
        );

        $this->storeRequestClass = FeaturePackageStoreRequest::class;
        $this->updateRequestClass = FeaturePackageUpdateRequest::class;
        $this->resourceClass = FeaturePackageResource::class;
    }
    protected function beforeStore(array $data, Request $request): array
    {
        $exists = FeaturePackage::where('package_id', $data['package_id'])
            ->where('feature_id', $data['feature_id'])
            ->exists();

        if ($exists) {
            throw new HttpException(422, 'This feature already exists in this package.');
        }

        $feature = Features::findOrFail($data['feature_id']);
        $data['key_feature'] = str_replace(' ', '_', $feature->key);

        return $data;
    }

    protected function beforeDestroy($record): void
    {
        request()->merge([
            '_deleted_feature_key' => $record->key_feature,
            '_deleted_package_id'  => $record->package_id,
        ]);
    }
}
