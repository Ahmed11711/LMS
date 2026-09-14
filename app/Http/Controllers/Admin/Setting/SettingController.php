<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Repositories\Setting\SettingRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Setting\SettingStoreRequest;
use App\Http\Requests\Admin\Setting\SettingUpdateRequest;
use App\Http\Resources\Admin\Setting\SettingResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    /**
     * Override store() to handle array-of-{key,value} payload
     * instead of a single record.
     */
    public function store(Request $request): JsonResponse
    {
        $payload = $request->all(); // array of {key, value}

        try {
            DB::beginTransaction();

            foreach ($payload as $item) {
                $this->repository->query()->updateOrCreate(
                    ['key' => $item['key']],
                    ['value' => $item['value']]
                );
            }

            DB::commit();

            $settings = $this->repository->query()->get();

            return $this->successResponse(
                SettingResource::collection($settings),
                'Settings updated successfully'
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error updating settings: " . $e->getMessage());
            return $this->errorResponse("Failed to update settings", 500);
        }
    }
}
