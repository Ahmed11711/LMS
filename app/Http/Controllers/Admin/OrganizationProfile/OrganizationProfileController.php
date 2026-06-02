<?php

namespace App\Http\Controllers\Admin\OrganizationProfile;

use App\Models\OrganizationProfile;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;


class OrganizationProfileController extends Controller
{
    use ApiResponseTrait;

    protected string $uploadDisk = 'public';
    protected string $collectionName = 'organizations';

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    /**
     * حقول الصور: أي مفتاح ينتهي بـ _image
     */
    private function isImageField(string $key): bool
    {
        return Str::endsWith($key, '_image');
    }

    /**
     * بناء الـ URL الكامل لصورة مخزنة باسمها فقط
     */
    private function buildImageUrl(?string $filename): ?string
    {
        if (!$filename) return null;

        return Storage::disk($this->uploadDisk)->url(
            "uploads/{$this->collectionName}/{$filename}"
        );
    }

    /**
     */
    private function uploadImage($file): string
    {
        $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->storeAs("uploads/{$this->collectionName}", $filename, $this->uploadDisk);
        return $filename;
    }

    /**
     * حذف صورة قديمة من الـ storage
     */
    private function deleteImage(?string $filename): void
    {
        if (!$filename) return;

        Storage::disk($this->uploadDisk)
            ->delete("uploads/{$this->collectionName}/{$filename}");
    }

    /**
     * معالجة الـ data payload:
     * - أي حقل ينتهي بـ _image وفيه فايل → ارفعه واحفظ اسمه
     * - باقي الحقول تتخزن كما هي
     *
     * @param Request $request
     * @param array   $inputData   البيانات القادمة من الفرونت (بعد استثناء name/role)
     * @param array   $existingData الـ data الموجودة مسبقاً (للـ update)
     */
    private function processDataPayload(Request $request, array $inputData, array $existingData = []): array
    {
        $processed = $existingData; // نبدأ من الموجود ونفوق عليه

        foreach ($inputData as $key => $value) {
            if ($this->isImageField($key) && $request->hasFile($key)) {
                // حذف الصورة القديمة لو موجودة
                $this->deleteImage($existingData[$key] ?? null);

                // رفع الصورة الجديدة وحفظ الاسم فقط
                $processed[$key] = $this->uploadImage($request->file($key));
            } else {
                $processed[$key] = $value;
            }
        }

        return $processed;
    }

    /**
     * تحويل الـ data للـ response: استبدال أسماء الصور بـ URLs كاملة
     */
    private function formatDataForResponse(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($this->isImageField($key)) {
                $data[$key] = $this->buildImageUrl($value);
            }
        }

        return $data;
    }

    /**
     * تحويل الـ Organization لـ array جاهز للـ response
     */
    private function formatOrganization(OrganizationProfile $org): array
    {
        return [
            'id'         => $org->id,
            'user_id'    => $org->user_id,
            'role'       => $org->role,
            'name'       => $org->name,
            'data'       => $this->formatDataForResponse($org->data ?? []),
            'created_at' => $org->created_at,
            'updated_at' => $org->updated_at,
        ];
    }

    // ------------------------------------------------------------------ //
    //  CRUD
    // ------------------------------------------------------------------ //

    /**
     * GET /organizations
     * قائمة المنظمات الخاصة بالمستخدم الحالي
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $orgs = OrganizationProfile::where('user_id', auth()->id())
                ->latest()
                ->paginate($request->input('per_page', 10));

            $orgs->getCollection()->transform(
                fn($org) => $this->formatOrganization($org)
            );

            return $this->successResponsePaginate($orgs, 'Organizations retrieved successfully');
        } catch (\Throwable $e) {
            Log::error('Organization index error: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch organizations', 500);
        }
    }

    /**
     * GET /organizations/{id}
     */
    public function show(int $id): JsonResponse
    {
        $org = OrganizationProfile::where('user_id', auth()->id())->find($id);

        if (!$org) {
            return $this->errorResponse('Organization not found', 404);
        }

        return $this->successResponse($this->formatOrganization($org), 'Organization retrieved successfully');
    }

    /**
     * POST /organizations
     *
     * Expected payload (multipart/form-data):
     * {
     *   "name": "Hayes Waters",
     *   "role": "academy",          // optional, default = 'academy'
     *   "title": "...",
     *   "avatar_image": <file>,     // أي حقل ينتهي بـ _image يُعامل كصورة
     *   "cover_image": <file>,
     *   "bio_paragraph_1": "...",
     *   ...                         // باقي حقول الـ data
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'role' => 'sometimes|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        try {
            DB::beginTransaction();

            // فصل الـ top-level fields عن الـ data
            $topLevel = $request->only(['name', 'role']);
            $rawData  = $request->except(['name', 'role', '_method', '_token']);

            $processedData = $this->processDataPayload($request, $rawData);

            $org = OrganizationProfile::create([
                'user_id' => auth()->id(),
                'role'    => $topLevel['role'] ?? 'academy',
                'name'    => $topLevel['name'],
                'data'    => $processedData,
            ]);

            DB::commit();

            return $this->successResponse($this->formatOrganization($org), 'Organization created successfully', 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Organization store error: ' . $e->getMessage());
            return $this->errorResponse('Failed to create organization: ' . $e->getMessage(), 500);
        }
    }

    /**
     * PUT/PATCH /organizations/{id}
     *
     * الفرونت يبعت بس الحقول اللي اتغيرت.
     * الحقول الموجودة في الـ data ومش بعتها الفرونت → تفضل كما هي.
     * لو بعت حقل _image جديد → الصورة القديمة بتتحذف وبتتحل محلها الجديدة.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $org = OrganizationProfile::where('user_id', auth()->id())->find($id);

        if (!$org) {
            return $this->errorResponse('Organization not found or unauthorized', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'role' => 'sometimes|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        try {
            DB::beginTransaction();

            $updatePayload = [];

            if ($request->has('name')) {
                $updatePayload['name'] = $request->input('name');
            }

            if ($request->has('role')) {
                $updatePayload['role'] = $request->input('role');
            }

            // معالجة الـ data: نأخذ الموجود ونفوق بالجديد
            $rawData = $request->except(['name', 'role', '_method', '_token']);

            if (!empty($rawData) || $this->hasImageFiles($request)) {
                $updatePayload['data'] = $this->processDataPayload(
                    $request,
                    $rawData,
                    $org->data ?? []
                );
            }

            $org->update($updatePayload);

            DB::commit();

            return $this->successResponse($this->formatOrganization($org->fresh()), 'Organization updated successfully');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Organization update error: ' . $e->getMessage());
            return $this->errorResponse('Failed to update organization', 500);
        }
    }

    /**
     * DELETE /organizations/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $org = OrganizationProfile::where('user_id', auth()->id())->find($id);

        if (!$org) {
            return $this->errorResponse('Organization not found or unauthorized', 404);
        }

        try {
            DB::beginTransaction();

            // حذف كل صور الـ organization
            foreach (($org->data ?? []) as $key => $value) {
                if ($this->isImageField($key)) {
                    $this->deleteImage($value);
                }
            }

            $org->delete();

            DB::commit();

            return $this->successResponse(null, 'Organization deleted successfully');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Organization destroy error: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete organization', 500);
        }
    }

    // ------------------------------------------------------------------ //
    //  Private Utilities
    // ------------------------------------------------------------------ //

    /**
     * هل في أي ملف صورة في الـ request؟
     */
    private function hasImageFiles(Request $request): bool
    {
        foreach ($request->allFiles() as $key => $_) {
            if ($this->isImageField($key)) return true;
        }
        return false;
    }
}
