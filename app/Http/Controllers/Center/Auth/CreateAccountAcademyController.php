<?php

namespace App\Http\Controllers\Center\Auth;

use App\Enums\Central\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Auth\CreateAcademyRequest;
use App\Http\Requests\Central\Auth\CreateInfoAcademy;
use App\Http\Resources\Auth\LoginResource;
use App\Http\Service\Payment\KashierPaymentService;
use App\Models\Central\User;
use App\Services\SmsService\SMSMISR\SmsMisrService;
use App\Services\TenantService\TenantService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;


class CreateAccountAcademyController extends Controller
{
    use ApiResponseTrait;
    public function __construct(public TenantService $service)
    {
        $this->service = $service;
    }

    public function create(CreateAcademyRequest $request)
    {
        $data = $request->validated();
        if (!empty($data['email'])) {
            unset($data['phone']);
        } else {
            unset($data['email']);
        }
        $data['role'] = UserRole::ACADEMY->value;
        $user = User::create($data);
        return $this->successResponse($user, 'Academy account created successfully');
    }

    // public function createInfoAcademy(CreateInfoAcademy $request)
    // {
    //     $data = $request->validated();

    //     $user = User::where(function ($query) use ($data) {
    //         if (!empty($data['email'])) $query->where('email', $data['email']);
    //         if (!empty($data['phone'])) $query->orWhere('phone', $data['phone']);
    //     })->first();

    //     if (!$user) {
    //         return $this->errorResponse('User not found', 404);
    //     }

    //     $user->update([
    //         'username'      => $data['username'],
    //         'phone_academy' => $data['phone_academy'],
    //         'country_code'  => $data['country_code'],
    //         'specialties'   => $data['specialties'],
    //     ]);

    //     $tenantData = array_merge($data, [
    //         'name'     => $data['username'],
    //         'domain'   => $data['link_academy'],
    //         'password' => $user->password,
    //         'user_name' => $user->username,
    //         'phone_academy' => $user->phone_academy,
    //         'country_code' => $user->country_code,
    //         'specialties' => $user->specialties,
    //         'user_id' => $user->id,
    //     ]);

    //     $tenant = $this->service->createTenant($tenantData);

    //     return $this->successResponse($user, 'Academy and Tenant Database created successfully');
    // }

    public function createInfoAcademy(CreateInfoAcademy $request)
    {
        $data = $request->validated();

        $user = User::where(function ($query) use ($data) {
            if (!empty($data['email'])) $query->where('email', $data['email']);
            if (!empty($data['phone'])) $query->orWhere('phone', $data['phone']);
        })->first();

        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        $user->update([
            'username'      => $data['username'],
            'phone_academy' => $data['phone_academy'],
            'country_code'  => $data['country_code'],
            'specialties'   => $data['specialties'],
        ]);

        $tenantData = array_merge($data, [
            'name'          => $data['username'],
            'domain'        => $data['link_academy'],
            'password'      => $user->password,
            'user_name'     => $user->username,
            'phone_academy' => $user->phone_academy,
            'country_code'  => $user->country_code,
            'specialties'   => $user->specialties,
            'user_id'       => $user->id,
        ]);

        // إنشاء الـ Tenant
        $tenant = $this->service->createTenant($tenantData);

        $token = JWTAuth::fromUser($user);
        $token = JWTAuth::claims([
            'tenant_id' => app('tenant')->id
        ])->fromUser($user);
        Log::info("User {$user->id} created tenant with ID: " . app('tenant')->id);

        return (new LoginResource($user))->additional([
            'message' => 'Academy and Tenant Database created successfully',
            'meta' => [
                'access_token' => $token,
                'token_type'   => 'bearer',
            ]
        ]);
    }

    public function sms()
    {
        $smsService = new SmsMisrService();
        $response = $smsService->sendSms(201094321637, 'Hello from SMS Misr!');
        return response()->json($response);
    }
}
