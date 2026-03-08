<?php

namespace App\Http\Controllers\Center\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\CreatePaymentRequest;

use App\Services\Payment\createLinkKashierPaymentService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class KashierPaymentController extends Controller
{
    use ApiResponseTrait;
    public function __construct(public createLinkKashierPaymentService $createLinkKashierPaymentService) {}


    public function createLink(CreatePaymentRequest $request)
    {
        $data = $request->validated();
        $link = $this->createLinkKashierPaymentService->createSession($data);
        return $this->successResponse($link, 'Payment link created successfully');
    }


    public function success(Request $request)
    {
        $data = $request->all();
        $transactionId = $data['merchantOrderId'] ?? null;

        if ($data['paymentStatus'] == "FAILED") {
            return null;
        }

        if ($transactionId) {
            $this->createLinkKashierPaymentService->updateSubscriptionStatus($transactionId, $data['paymentStatus']);
        }

        return redirect()->away(
            'http://72.62.27.82/next-site'
        );
    }

    public function failure(Request $request)
    {
        $data = $request->all();
        Log::info('Kashier Payment Failure Redirect:', $data);
        return redirect()->away(
            'https://astar.click/auth/login'
        );
        return response()->json([
            'message' => 'Payment Failed (Redirect)',
            'data' => $data
        ]);
    }
    // public function updateAllMyPackage($studentPackage)
    // {
    //     $studentId = $studentPackage->student_id;
    //     $packageId = $studentPackage->package_id;
    //     StudentPackage::where('student_id', $studentId)
    //         ->where('active', true)
    //         ->update(['active' => false]);
    //     UserPackageFeature::where('user_id', $studentId)
    //         ->update(['active' => false]);

    //     $package = Packages::select('id', 'price', 'duration_days')
    //         ->find($packageId);
    //     $this->featureService->createFeaturesForUser($studentId, $package);
    // }
}
