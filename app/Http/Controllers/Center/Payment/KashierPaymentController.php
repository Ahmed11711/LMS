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

        try {
            $link = $this->createLinkKashierPaymentService->createSession($data);

            return $this->successResponse($link, 'Payment link created successfully');
        } catch (\RuntimeException $e) {

            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Throwable $e) {

            Log::error('Payment link creation failed', [
                'message' => $e->getMessage(),
            ]);

            return $this->errorResponse('Something went wrong while creating the payment link.', 500);
        }
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
            'http://darab.academy/'
        );
        return response()->json([
            'message' => 'Payment Failed (Redirect)',
            'data' => $data
        ]);
    }

    // select from userpackage
    // from userid select to database tenent 
    // update old package to active false 
    // insert new data 

    public function successPayment(Request $request)
    {
        $data = $request->all();
        $transactionId = $data['merchantOrderId'] ?? null;

        if ($data['paymentStatus'] == "FAILED") {
            return null;
        }


        if ($transactionId) {
            $this->createLinkKashierPaymentService->updateSubscriptionStatus($transactionId, $data['paymentStatus']);
        }
    }
}
