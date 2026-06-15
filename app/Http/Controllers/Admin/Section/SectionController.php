<?php

namespace App\Http\Controllers\Admin\Section;

use App\Repositories\Section\SectionRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Section\SectionStoreRequest;
use App\Http\Requests\Admin\Section\SectionUpdateRequest;
use App\Http\Resources\Admin\Section\SectionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SectionController extends BaseController
{
    public function __construct(SectionRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Section'
        );

        $this->storeRequestClass  = SectionStoreRequest::class;
        $this->updateRequestClass = SectionUpdateRequest::class;
        $this->resourceClass      = SectionResource::class;
        $this->withRelationships  = ['items'];
    }

    /**
     * جلب الأقسام بناءً على رقم الصفحة
     */
    public function byPage(int $pageId): JsonResponse
    {
        $sections = $this->repository->query()
            ->where('pages_id', $pageId)
            ->with('items')
            ->orderBy('order')
            ->get();

        return $this->successResponse(
            SectionResource::collection($sections),
            'Sections retrieved successfully'
        );
    }

    /**
     * تغيير ترتيب السيكشنز (drag & drop)
     * Body: [{ id: 1, order: 1 }, { id: 2, order: 2 }, ...]
     */
    public function reorder(Request $request): JsonResponse
    {
        $items = $request->validate([
            '*.id'    => 'required|integer|exists:sections,id',
            '*.order' => 'required|integer|min:0',
        ]);

        foreach ($items as $item) {
            $this->repository->query()
                ->where('id', $item['id'])
                ->update(['order' => $item['order']]);
        }

        return $this->successResponse(null, 'Sections reordered successfully');
    }

    // ----------------------------------------------------------------
    // Hooks
    // ----------------------------------------------------------------

    protected function beforeStore(array $data, Request $request): array
    {
        if (isset($data['props']) && is_array($data['props'])) {
            $data['props'] = json_encode($data['props'], JSON_UNESCAPED_UNICODE);
        }

        // 🌟 تنظيف الـ items منعاً لخطأ الـ Array to string conversion في الـ BaseController
        if (isset($data['items'])) {
            unset($data['items']);
        }

        return $data;
    }

    protected function afterStore($record, Request $request): void
    {
        $this->syncItems($record, $request->input('items', []));
    }

    protected function beforeUpdate(array $data, $existingRecord, Request $request): array
    {
        if (isset($data['props']) && is_array($data['props'])) {
            $data['props'] = json_encode($data['props'], JSON_UNESCAPED_UNICODE);
        }

        // 🌟 تنظيف الـ items منعاً للخطأ في حالة التعديل برضه
        if (isset($data['items'])) {
            unset($data['items']);
        }

        return $data;
    }

    protected function afterUpdate($record, $oldRecord, Request $request): void
    {
        // لو الفرونت بعت items نعمل sync، لو مبعتش نسيب الـitems زي ما هي
        if ($request->has('items')) {
            $this->syncItems($record, $request->input('items', []));
        }
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    private function syncItems($section, array $items): void
    {
        $section->items()->delete();

        foreach ($items as $index => $item) {
            $section->items()->create([
                'order' => $item['order'] ?? $index + 1,
                'props' => isset($item['props']) && is_array($item['props'])
                    ? json_encode($item['props'], JSON_UNESCAPED_UNICODE)
                    : ($item['props'] ?? '{}'),
            ]);
        }
    }
}
