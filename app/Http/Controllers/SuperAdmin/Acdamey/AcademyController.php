<?php

namespace App\Http\Controllers\SuperAdmin\Acdamey;

use App\Models\Central\User;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Resources\SuperAdmin\AcademyResource;

class AcademyController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        // بنعمل object بسيط بس عنده method اسمها query()
        // عشان يتوافق مع الطريقة اللي BaseController متعامل بيها
        $this->repository = new class {
            public function query()
            {
                return User::query();
            }
        };

        $this->collectionName = 'Academy';
        $this->resourceClass = AcademyResource::class;
        $this->withRelationships = ['tenant.domains'];
    }

    protected function applyScoping($query)
    {
        $query = parent::applyScoping($query);

        return $query->where('role', 'academy');
    }
}
