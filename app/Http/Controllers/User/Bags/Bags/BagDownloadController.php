<?php

namespace App\Http\Controllers\User\Bags\Bags;

use App\Http\Controllers\Controller;
use App\Models\Bag;
use App\Models\UserBagDownload;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BagDownloadController extends Controller
{
    use ApiResponseTrait;

    public function download(Request $request, int $bagId): JsonResponse
    {
        $request->validate([
            'bag_item_id' => ['nullable', 'integer'],
        ]);

        $userId = auth('api')->id();

        $bag = Bag::find($bagId);
        if (!$bag) {
            return $this->errorResponse('الحقيبة غير موجودة', 404);
        }

        // ===== تأكد إن اليوزر اشترى الباج فعلاً وتم قبوله =====
        $purchase = $bag->purchases()
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->latest('id')
            ->first();

        if (!$purchase) {
            return $this->errorResponse('لازم تشتري الحقيبة الأول عشان تقدر تحمّل', 403);
        }

        // ===== تحقق من صلاحية مدة التحميل =====
        if ($bag->download_validity_days) {
            $expiresAt = $purchase->created_at->copy()->addDays($bag->download_validity_days);

            if (now()->greaterThan($expiresAt)) {
                return $this->errorResponse('انتهت صلاحية تحميل هذه الحقيبة', 403);
            }
        }

        // ===== تحقق من الحد الأقصى للتحميلات (لو limited) =====
        if ($bag->download_type === 'limited' && $bag->download_limit !== null) {
            $usedDownloads = UserBagDownload::where('user_id', $userId)
                ->where('bag_id', $bagId)
                ->count();

            if ($usedDownloads >= $bag->download_limit) {
                return $this->errorResponse('لقد استنفذت عدد مرات التحميل المسموح بها لهذه الحقيبة', 403);
            }

            $remaining = $bag->download_limit - $usedDownloads - 1;
        } else {
            $remaining = null; // unlimited
        }

        UserBagDownload::create([
            'user_id'     => $userId,
            'bag_id'      => $bagId,
            'bag_item_id' => $request->input('bag_item_id'),
        ]);

        return $this->successResponse([
            'remaining_downloads' => $remaining,
        ], 'تم تسجيل عملية التحميل بنجاح');
    }
}
