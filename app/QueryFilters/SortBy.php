<?php

namespace App\QueryFilters;

use Closure;

class SortBy
{
    public function handle($query, Closure $next)
    {
        $request = request();
        $builder = $next($query);
        $model = $builder->getModel();

        $sortable = property_exists($model, 'sortable') ? $model->sortable : ['id', 'created_at'];

        $sortBy = $request->input('sort_by');
        $sortOrder = strtolower($request->input('sort_order')) === 'asc' ? 'asc' : 'desc';

        if ($sortBy && in_array($sortBy, $sortable)) {
            return $builder->orderBy($sortBy, $sortOrder);
        }

        return $builder->latest();
    }
}
