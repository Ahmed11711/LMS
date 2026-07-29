<?php

namespace App\Repositories\Term;

use App\Repositories\Term\TermRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\Term;

class TermRepository extends BaseRepository implements TermRepositoryInterface
{
    public function __construct(Term $model)
    {
        parent::__construct($model);
    }
}
