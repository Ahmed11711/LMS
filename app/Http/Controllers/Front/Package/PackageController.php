<?php

namespace App\Http\Controllers\Front\Package;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Package\PackageResource;
use App\Repositories\Package\PackageRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    use ApiResponseTrait;

    public function __construct(public PackageRepositoryInterface $packageRepository) {}

    public function activePackage()
    {
        $packages = $this->packageRepository->allRelationsActive(['packageFeatures.feature'], 'is_active');

        return $this->successResponse(
            PackageResource::collection($packages),
            'All Packages'
        );
    }
}
