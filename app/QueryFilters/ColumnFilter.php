<?php

namespace App\QueryFilters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class ColumnFilter
{
    public function handle($query, Closure $next)
    {
        $model = $query->getModel();

        $filterable = property_exists($model, 'filterable') ? $model->filterable : [];

        if (!empty($filterable)) {
            $filters = request()->only($filterable);

            foreach ($filters as $key => $value) {
                if ($value !== null && $value !== '') {
                    if (is_array($value)) {
                        $query->whereIn($key, $value);
                    } else {
                        $query->where($key, $value);
                    }
                }
            }
        }

        return $next($query);
    }
}
