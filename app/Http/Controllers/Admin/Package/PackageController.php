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
    }

    protected function getShowRelationships(): array
    {
        return [
            'packageFeatures'
        ];
    }

    /**
     * قبل الإنشاء: نطلع الـ features برة، عشان ميتبعتوش
     * لجدول packages نفسه (لإنهم مش أعمدة فيه)
     */
    protected function beforeStore(array $data, Request $request): array
    {
        $this->pendingFeatures = $data['features'] ?? [];
        unset($data['features']);

        return $data;
    }

    /**
     * بعد الإنشاء: نربط الـ features بالـ package الجديد
     */
    protected function afterStore($record, Request $request): void
    {
        $this->syncFeatures($record, $this->pendingFeatures);
    }

    /**
     * قبل التحديث: نفس الفكرة، نطلع الـ features برة
     */
    protected function beforeUpdate(array $data, $existingRecord, Request $request): array
    {
        $this->pendingFeatures = $data['features'] ?? [];
        unset($data['features']);

        return $data;
    }

    /**
     * بعد التحديث: نعمل sync للـ features (تحديث الموجود + إضافة الجديد + حذف المشطوب)
     */
    protected function afterUpdate($updatedRecord, $oldRecord, Request $request): void
    {
        $this->syncFeatures($updatedRecord, $this->pendingFeatures);
    }

    /**
     * الدالة المسؤولة عن ربط الـ Package بالـ Features بتاعته
     */
    protected function syncFeatures($package, array $features): void
    {
        if (empty($features)) {
            return;
        }

        $syncData = [];

        foreach ($features as $feature) {
            if (!isset($feature['feature_id'])) {
                continue;
            }

            $syncData[$feature['feature_id']] = [
                'value' => $feature['value'] ?? null,
            ];
        }

        $package->packageFeatures()->sync($syncData);
    }
}
