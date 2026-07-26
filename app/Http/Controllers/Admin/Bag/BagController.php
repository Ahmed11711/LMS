<?php

namespace App\Http\Controllers\Admin\Bag;

use App\Repositories\Bag\BagRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Bag\BagStoreRequest;
use App\Http\Requests\Admin\Bag\BagUpdateRequest;
use App\Http\Resources\Admin\Bag\BagResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BagController extends BaseController
{
    public function __construct(BagRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Bag',
            fileFields: ['image']
        );

        $this->storeRequestClass = BagStoreRequest::class;
        $this->updateRequestClass = BagUpdateRequest::class;
        $this->resourceClass = BagResource::class;

        $this->isUserBound = true;
        $this->hasGallery = true;

        $this->withRelationships = ['items', 'userPaymentInfos', 'gallery'];
    }

    /**
     */
    protected function getIndexRelationships(): array
    {
        return [];
    }

    protected function beforeStore(array $data, Request $request): array
    {
        $data['user_id'] = auth('api')->id();

        unset($data['items'], $data['payment_info_ids'], $data['gallery']);

        return $data;
    }

    protected function afterStore($record, Request $request): void
    {
        $this->syncItems($record, $request);
        $this->syncPaymentInfos($record, $request);
    }

    protected function beforeUpdate(array $data, $existingRecord, Request $request): array
    {
        unset($data['items'], $data['payment_info_ids'], $data['gallery']);

        return $data;
    }

    /**
     */
    protected function afterUpdate($updatedRecord, $oldRecord, Request $request): void
    {
        $this->syncItems($updatedRecord, $request, true);
        $this->syncPaymentInfos($updatedRecord, $request);
    }

    /**
     */
    protected function syncItems($bag, Request $request, bool $isUpdate = false): void
    {
        if (!$request->has('items')) return;

        if ($isUpdate) {
            $this->deleteOldItems($bag);
        }

        foreach ($request->file('items', []) as $index => $itemFiles) {
            if (!isset($itemFiles['file'])) continue;

            try {
                $file = $itemFiles['file'];
                $type = $request->input("items.{$index}.type");

                $originalName = $file->getClientOriginalName();
                $decodedName  = urldecode($originalName);
                $cleanName    = preg_replace('/\s+/', '_', trim($decodedName));

                $filename = time() . '_' . $index . '_' . $cleanName;
                $path = $file->storeAs("uploads/{$this->collectionName}/items", $filename, $this->uploadDisk);

                $bag->items()->create([
                    'path' => "/storage/" . $path,
                    'type' => $type ?? $file->getClientOriginalExtension(),
                ]);
            } catch (\Throwable $e) {
                Log::error("Bag item upload failed: " . $e->getMessage());
            }
        }
    }

    /**
     */
    protected function deleteOldItems($bag): void
    {
        foreach ($bag->items as $item) {
            $relativePath = str_replace('/storage/', '', $item->path);
            Storage::disk($this->uploadDisk)->delete($relativePath);
        }

        $bag->items()->delete();
    }

    /**
     */
    protected function syncPaymentInfos($bag, Request $request): void
    {
        if (!$request->has('payment_info_ids')) return;

        $bag->userPaymentInfos()->sync($request->input('payment_info_ids', []));
    }
}
