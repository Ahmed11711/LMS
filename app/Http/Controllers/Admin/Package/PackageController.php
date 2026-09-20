<?php

namespace App\Http\Controllers\Admin\Package;

use App\Repositories\Package\PackageRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Package\PackageStoreRequest;
use App\Http\Requests\Admin\Package\PackageUpdateRequest;
use App\Http\Resources\Admin\Package\PackageResource;
use Illuminate\Http\Request;

class PackageController extends BaseController
{

    protected array $pendingFeatures = [];

    public function __construct(PackageRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Package'
        );

        $this->storeRequestClass = PackageStoreRequest::class;
        $this->updateRequestClass = PackageUpdateRequest::class;
        $this->resourceClass = PackageResource::class;
        $this->withRelationships = ['packageFeatures.feature'];
    }

    protected function getShowRelationships(): array
    {
        return [
            'packageFeatures.feature'
        ];
    }


    protected function beforeStore(array $data, Request $request): array
    {
        $this->pendingFeatures = $data['features'] ?? [];
        unset($data['features']);

        return $data;
    }

    /**
     */
    protected function afterStore($record, Request $request): void
    {
        $this->syncFeatures($record, $this->pendingFeatures);
    }

    /**
     */
    protected function beforeUpdate(array $data, $existingRecord, Request $request): array
    {
        $this->pendingFeatures = $data['features'] ?? [];
        unset($data['features']);

        return $data;
    }

    /**
     */
    protected function afterUpdate($updatedRecord, $oldRecord, Request $request): void
    {
        $this->syncFeatures($updatedRecord, $this->pendingFeatures);
    }

    /**
     */
    protected function syncFeatures($package, array $features): void
    {
        if (empty($features)) {
            $package->packageFeatures()->delete();
            return;
        }

        $featureIds = collect($features)
            ->pluck('feature_id')
            ->filter()
            ->toArray();

        $package->packageFeatures()
            ->whereNotIn('feature_id', $featureIds)
            ->delete();

        foreach ($features as $feature) {
            if (!isset($feature['feature_id'])) {
                continue;
            }

            $package->packageFeatures()->updateOrCreate(
                ['feature_id' => $feature['feature_id']],
                ['value' => $feature['value'] ?? null]
            );
        }
    }
}
