<?php

namespace App\Repositories\Template;

use App\Repositories\Template\TemplateRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\Template;

class TemplateRepository extends BaseRepository implements TemplateRepositoryInterface
{
    public function __construct(Template $model)
    {
        parent::__construct($model);
    }
}
