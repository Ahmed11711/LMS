<?php

namespace App\Http\Controllers\Admin\Pages;

use App\Repositories\Pages\PagesRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Pages\PagesStoreRequest;
use App\Http\Requests\Admin\Pages\PagesUpdateRequest;
use App\Http\Resources\Admin\Pages\PagesResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PagesController extends BaseController
{
    public function __construct(PagesRepositoryInterface $repository)
    {
        parent::__construct();
        $this->initService(
            repository: $repository,
            collectionName: 'Pages'
        );
        $this->storeRequestClass = PagesStoreRequest::class;
        $this->updateRequestClass = PagesUpdateRequest::class;
        $this->resourceClass = PagesResource::class;
    }

    protected function beforeStore(array $data, Request $request): array
    {
        $data['slug'] = Str::slug($data['title']);

        if (!empty($data['is_active']) && $data['is_active'] == 1) {
            $this->repository->query()->where('is_active', 1)->update(['is_active' => 0]);
        }

        return $data;
    }

    protected function beforeUpdate(array $data, $existingRecord, Request $request): array
    {
        if (!empty($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if (isset($data['is_active']) && $data['is_active'] == 1) {
            $this->repository->query()
                ->where('id', '!=', $existingRecord->id)
                ->where('is_active', 1)
                ->update(['is_active' => 0]);
        }

        return $data;
    }
}
