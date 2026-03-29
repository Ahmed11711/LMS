<?php

namespace App\Http\Controllers\Center\Auth;

use App\Enums\Central\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Auth\CreateAcademyRequest;
use App\Http\Requests\Central\Auth\CreateInfoAcademy;
use App\Models\Central\User;
use App\Services\TenantService\CreateAcademyInfoService;
use App\Traits\ApiResponseTrait;



class CreateAccountAcademyController extends Controller
{
    use ApiResponseTrait;
    public function __construct(
        protected CreateAcademyInfoService $tenantService,

    ) {}

    public function create(CreateAcademyRequest $request)
    {
        $user = User::create(array_merge(
            $request->safe()->only(['email', 'phone', 'password']),
            ['role' => UserRole::ACADEMY->value]
        ));
        return $this->successResponse($user, 'Academy account created successfully');
    }
    public function createInfoAcademy(CreateInfoAcademy $request)
    {
        try {
            $user = $this->tenantService->registerAcademyTenant($request->validated());
            return $this->successResponse($user, 'Academy and Tenant Database created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
