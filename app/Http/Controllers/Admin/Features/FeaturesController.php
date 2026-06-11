<?php

namespace App\Http\Controllers\Admin\Features;

use App\Repositories\Features\FeaturesRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Features\FeaturesStoreRequest;
use App\Http\Requests\Admin\Features\FeaturesUpdateRequest;
use App\Http\Resources\Admin\Features\FeaturesResource;
use App\Models\Central\Features;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FeaturesController extends BaseController
{
    public function __construct(FeaturesRepositoryInterface $repository)
    {
        parent::__construct();
        $this->initService(
            repository: $repository,
            collectionName: 'Features'
        );
        $this->storeRequestClass = FeaturesStoreRequest::class;
        $this->updateRequestClass = FeaturesUpdateRequest::class;
        $this->resourceClass = FeaturesResource::class;
    }

    protected function beforeStore(array $data, Request $request): array
    {
        if (!empty($data['title'])) {
            $data['key'] = $this->generateUniqueKey($data['title']);
        }

        return $data;
    }

    protected function beforeUpdate(array $data, $existingRecord, Request $request): array
    {
        if (!empty($data['title'])) {
            $data['key'] = $this->generateUniqueKey($data['title'], $existingRecord->id);
        }

        return $data;
    }

    protected function generateUniqueKey(string $title, ?int $ignoreId = null): string
    {
        $baseKey = Str::of($title)
            ->trim()
            ->snake()
            ->upper()
            ->toString();

        $key = $baseKey;
        $counter = 1;

        while (
            Features::where('key', $key)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $key = $baseKey . '_' . $counter;
            $counter++;
        }

        return $key;
    }
}
