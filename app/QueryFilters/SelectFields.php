<?php

namespace App\QueryFilters;

use Closure;

class SelectFields
{
    public function handle($query, Closure $next)
    {
        $request = request();
        $builder = $next($query);
        $model = $builder->getModel();

        $allowed = property_exists($model, 'allowedFields') ? $model->allowedFields : [];

        if ($request->has('fields') && !empty($allowed)) {
            $requestedFields = explode(',', $request->input('fields'));
            $safeFields = array_intersect($requestedFields, $allowed);

            if (!in_array('id', $safeFields)) {
                $safeFields[] = 'id';
            }

            return $builder->select($safeFields);
        }

        return $builder;
    }
}
