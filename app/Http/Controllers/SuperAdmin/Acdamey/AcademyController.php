<?php

namespace App\Http\Controllers\SuperAdmin\Acdamey;

use App\Models\Central\User;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Resources\SuperAdmin\AcademyResource;
use App\Http\Requests\SuperAdmin\Academy\AcademyUpdateRequest;

class AcademyController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->repository = new class {
            public function query()
            {
                return User::query();
            }
        };

        $this->collectionName = 'Academy';
        $this->resourceClass = AcademyResource::class;
        $this->withRelationships = ['tenant'];

        $this->updateRequestClass = AcademyUpdateRequest::class;
    }

    protected function applyScoping($query)
    {
        $query = parent::applyScoping($query);

        return $query->where('role', 'academy');
    }
}
