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
        $payload = $request->all(); // array of {key, value}

        try {
            DB::beginTransaction();

            foreach ($payload as $index => $item) {
                $key   = $item['key'] ?? null;
                $value = $item['value'] ?? null;

                if (!$key) continue;

                // لو الـ key ده معروف إنه صورة، دور على الملف بنفس المسار في الـ request
                if (in_array($key, $this->imageKeys) && $request->hasFile("{$index}.value")) {
                    $file = $request->file("{$index}.value");

                    $originalName = $file->getClientOriginalName();
                    $decodedName  = urldecode($originalName);
                    $cleanName    = preg_replace('/\s+/', '_', trim($decodedName));

                    $filename = time() . '_' . $cleanName;
                    $path     = $file->storeAs("uploads/settings", $filename, 'public');

                    // احذف الصورة القديمة لو موجودة
                    $old = $this->repository->query()->where('key', $key)->first();
                    if ($old && !empty($old->value)) {
                        $oldPath = str_replace(Storage::disk('public')->url(''), '', $old->value);
                        Storage::disk('public')->delete($oldPath);
                    }

                    // خزن الـ path النسبي في الداتابيز (مش الرابط الكامل)
                    $value = $path;
                }

                $this->repository->query()->updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }

            DB::commit();

            $settings = $this->repository->query()->get();

            // رجّع الـ response بالـ full URL للصور
            $formatted = $settings->map(function ($setting) {
                $isImage = in_array($setting->key, $this->imageKeys);

                return [
                    'key'   => $setting->key,
                    'value' => $isImage && $setting->value
                        ? Storage::disk('public')->url($setting->value)
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
}
