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
use Illuminate\Support\Facades\Storage;

class SettingController extends BaseController
{
    // الـ keys اللي المفروض تتعامل معاها كملفات (صور) مش نصوص
    protected array $imageKeys = ['logo', 'avatar_url', 'logo_url'];

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

    public function store(Request $request): JsonResponse
    {
        $keys   = $request->input('key', []);   // array of strings
        $values = $request->input('value', []); // array of strings (non-file values only)

        try {
            DB::beginTransaction();

            foreach ($keys as $index => $key) {
                if (!$key) continue;

                $value = $values[$index] ?? null;

                // لو فيه ملف اترفع على نفس الـ index ده في حقل value
                if (in_array($key, $this->imageKeys) && $request->hasFile("value.{$index}")) {
                    $file = $request->file("value.{$index}");

                    $originalName = $file->getClientOriginalName();
                    $decodedName  = urldecode($originalName);
                    $cleanName    = preg_replace('/\s+/', '_', trim($decodedName));

                    $filename = time() . '_' . $cleanName;
                    $path     = $file->storeAs("uploads/settings", $filename, 'public');

                    // احذف الصورة القديمة لو موجودة
                    $old = $this->repository->query()->where('key', $key)->first();
                    if ($old && !empty($old->value) && !str_starts_with($old->value, 'http')) {
                        Storage::disk('public')->delete($old->value);
                    }

                    $value = $path; // خزن الـ path النسبي بس
                }

                $this->repository->query()->updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }

            DB::commit();

            $settings = $this->repository->query()->get();

            $formatted = $settings->map(function ($setting) {
                $isImage = in_array($setting->key, $this->imageKeys);

                return [
                    'key'   => $setting->key,
                    'value' => $isImage && $setting->value
                        ? $this->resolveFullUrl($setting->value)
                        : $setting->value,
                ];
            });

            return $this->successResponse($formatted, 'Settings updated successfully');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error updating settings: " . $e->getMessage());
            return $this->errorResponse("Failed to update settings", 500);
        }
    }

    protected function resolveFullUrl(string $value): string
    {
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return Storage::disk('public')->url($value);
    }
}
