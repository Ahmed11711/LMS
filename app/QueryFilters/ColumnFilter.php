<?php

namespace App\QueryFilters;

use Closure;

class ColumnFilter
{
    public function handle($query, Closure $next)
    {
        $builder = $next($query);
        $model = $builder->getModel();

        $filterable = property_exists($model, 'filterable') ? $model->filterable : [];

        if (empty($filterable)) {
            return $builder;
        }

        $filters = request()->only($filterable);

        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                if (is_array($value)) {
                    $builder->whereIn($key, $value);
                } else {
                    $builder->where($key, $value);
                }
            }
        }

        return $builder;
    }
}
