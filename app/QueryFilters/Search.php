<?php

namespace App\QueryFilters;

use Closure;

class Search
{
    public function handle($query, Closure $next)
    {
        $request = request();
        if (!$request->filled('search')) {
            return $next($query);
        }

        $builder = $next($query);
        $search = $request->input('search');
        $model = $builder->getModel();

        $searchable = method_exists($model, 'getSearchableColumns')
            ? $model->getSearchableColumns()
            : (property_exists($model, 'searchable') ? $model->searchable : []);

        if (empty($searchable)) {
            return $builder;
        }

        return $builder->where(function ($q) use ($search, $searchable, $model) {
            foreach ($searchable as $column) {
                $q->orWhere($column, 'like', "%{$search}%");
            }

            if (method_exists($model, 'getSearchableRelations')) {
                foreach ($model->getSearchableRelations() as $relation => $columns) {
                    $q->orWhereHas($relation, function ($relQuery) use ($search, $columns) {
                        $relQuery->where(function ($inner) use ($search, $columns) {
                            foreach ((array)$columns as $col) {
                                $inner->orWhere($col, 'like', "%{$search}%");
                            }
                        });
                    });
                }
            }
        });
    }
}
