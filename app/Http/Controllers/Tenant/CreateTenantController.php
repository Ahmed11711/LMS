<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CreateTenantRequest;
use App\Services\TenantService\TenantService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class CreateTenantController extends Controller
{
    use ApiResponseTrait;
    public function __construct(public TenantService $service)
    {
        $this->service = $service;
    }

    public function store(CreateTenantRequest $request)
    {
        $data = $request->validated();

        $tenant = $this->service->createTenant($data);
        return $this->successResponse($tenant, 'Tenant created successfully');
    }
}
