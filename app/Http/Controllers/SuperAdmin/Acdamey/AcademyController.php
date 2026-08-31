<?php

namespace App\Http\Controllers\SuperAdmin\Acdamey;

use App\Repositories\User\UserRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Resources\SuperAdmin\AcademyResource;

class AcademyController extends BaseController
{
    public function __construct(UserRepositoryInterface $repository)
    {
        parent::__construct();

        $this->initService(
            repository: $repository,
            collectionName: 'Academy'
        );

        $this->resourceClass = AcademyResource::class;

        $this->withRelationships = ['tenant.domains'];
    }

    protected function applyScoping($query)
    {
        $query = parent::applyScoping($query);

        return $query->where('role', 'academy');
    }
}
