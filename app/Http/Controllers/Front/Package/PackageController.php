<?php

namespace App\Http\Controllers\Front\Package;

use App\Http\Controllers\Controller;
use App\Repositories\Package\PackageRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    use ApiResponseTrait;
    public function __construct(public PackageRepositoryInterface $packageRepository) {}
    public function activePackage()
    {
        $package = $this->packageRepository->allRelationsActive(['packageFeatures'], 'is_active');
        return $this->successResponse($package, 'All Packages');
    }
}
