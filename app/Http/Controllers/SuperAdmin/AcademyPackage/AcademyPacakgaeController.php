<?php

namespace App\Http\Controllers\SuperAdmin\AcademyPackage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserPackage\UserPackageUpdateRequest;
use App\Models\Central\UserPackage;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class AcademyPacakgaeController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);

        $packages = UserPackage::query()
            ->latest()
            ->paginate($perPage);

        return $this->successResponsePaginate($packages, 'Packages fetched successfully');
    }

    public function update(UserPackageUpdateRequest $request, UserPackage $academyPackage)
    {
        $academyPackage->update($request->validated());

        return $this->successResponse($academyPackage, 'Package updated successfully');
    }
}
