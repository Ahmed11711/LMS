<?php

namespace App\Http\Controllers\Admin\Section;

use App\Repositories\Section\SectionRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Section\SectionStoreRequest;
use App\Http\Requests\Admin\Section\SectionUpdateRequest;
use App\Http\Requests\Admin\Section\SectionBulkStoreRequest;
use App\Http\Resources\Admin\Section\SectionResource;
use App\Models\Pages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     */
    public function byPage(): JsonResponse
    {
        $activePage = Pages::where('is_active', 1)->first();

        if (!$activePage) {
            return $this->errorResponse('No active page found', 404);
        }

        $sections = $this->repository->query()
            ->where('pages_id', $activePage->id)
            ->with('items')
            ->orderBy('order')
            ->get();

        if ($sections->isEmpty()) {
            return $this->errorResponse('No sections found for the active page', 404);
        }

        return $this->successResponse(
            SectionResource::collection($sections),
            'Sections retrieved successfully'
        );
    }

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


    public function bulkStore(SectionBulkStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $pageId    = $validated['pages_id'];
        $sections  = $validated['sections'];

        try {
            $created = DB::transaction(function () use ($pageId, $sections) {
                $oldSections = $this->repository->query()
                    ->where('pages_id', $pageId)
                    ->get();

                foreach ($oldSections as $oldSection) {
                    $oldSection->items()->delete();
                }

                $this->repository->query()
                    ->where('pages_id', $pageId)
                    ->delete();


                $newRecords = [];

                foreach ($sections as $sectionData) {
                    $record = $this->repository->create([
                        'pages_id' => $pageId,
                        'type'     => $sectionData['type'],
                        'order'    => $sectionData['order'],
                        'props'    => json_encode($sectionData['props'], JSON_UNESCAPED_UNICODE),
                    ]);

                    $this->syncItems($record, $sectionData['items'] ?? []);

                    $newRecords[] = $record;
                }

                return $newRecords;
            });

            $createdIds = collect($created)->pluck('id');

            $freshSections = $this->repository->query()
                ->whereIn('id', $createdIds)
                ->with('items')
                ->orderBy('order')
                ->get();

            return $this->successResponse(
                SectionResource::collection($freshSections),
                'Sections saved successfully'
            );
        } catch (\Throwable $e) {
            Log::error("Error in bulkStore Section: " . $e->getMessage());
            return $this->errorResponse("Failed to save sections: " . $e->getMessage(), 500);
        }
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
