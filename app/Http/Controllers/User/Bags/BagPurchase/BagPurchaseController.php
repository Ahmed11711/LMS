<?php

namespace App\Http\Controllers\User\Bags\BagPurchase;


use App\Http\Controllers\Controller;
use App\Http\Requests\User\BagPurchase\BagPurchaseStoreRequest;
use App\Http\Resources\User\BagPurchase\BagPurchaseResource;
use App\Models\Bag;
use App\Models\BagPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BagPurchaseController extends Controller
{
    protected string $uploadDisk = 'public';

    public function index(Request $request)
    {
        $purchases = BagPurchase::query()
            ->where('user_id', auth('api')->id())
            ->with('bag')
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return BagPurchaseResource::collection($purchases);
    }

    public function show(BagPurchase $bagPurchase)
    {
        abort_if($bagPurchase->user_id !== auth('api')->id(), 403, 'غير مصرح لك بعرض هذه العملية.');

        return new BagPurchaseResource($bagPurchase->load('bag'));
    }

    public function store(BagPurchaseStoreRequest $request)
    {
        $userId = auth('api')->id();
        $bag = Bag::findOrFail($request->input('bag_id'));

        // منع الاشتراك المتكرر لو عنده طلب pending أو approved بالفعل
        $alreadyPurchased = BagPurchase::where('bag_id', $bag->id)
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($alreadyPurchased) {
            return response()->json([
                'success' => false,
                'message' => 'لديك بالفعل طلب اشتراك في هذا المنتج قيد المراجعة أو تمت الموافقة عليه.',
            ], 422);
        }

        try {
            $file = $request->file('receipt');

            $originalName = preg_replace('/\s+/', '_', trim($file->getClientOriginalName()));
            $filename = time() . '_' . $userId . '_' . $originalName;

            $path = $file->storeAs('uploads/BagPurchase/receipts', $filename, $this->uploadDisk);

            $purchase = DB::transaction(function () use ($bag, $userId, $request, $path) {
                return BagPurchase::create([
                    'bag_id' => $bag->id,
                    'user_id' => $userId,
                    'payment_info_id' => $request->input('payment_info_id'),
                    'receipt' => '/storage/' . $path,
                    'amount' => $bag->discount_price ?? $bag->price,
                    'status' => 'pending',
                ]);
            });

            return (new BagPurchaseResource($purchase))
                ->additional(['success' => true, 'message' => 'تم إرسال طلب الاشتراك بنجاح، في انتظار المراجعة.']);
        } catch (\Throwable $e) {
            Log::error('Bag purchase failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حصل خطأ أثناء إرسال طلب الاشتراك، حاول تاني.',
            ], 500);
        }
    }
}
