<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

trait QueryableTrait
{
    public function queryIndex(Request $request, $repository, $resourceClass = null, $perPageDefault = 10)
    {
        $query = $repository->query();

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $table = $q->getModel()->getTable();
                $columns = Schema::getColumnListing($table);
                $excluded = ['id', 'created_at', 'updated_at', 'deleted_at'];
                $columns = array_filter($columns, fn($col) => !in_array($col, $excluded));

                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        // Filters — الحل هنا
        $excluded = ['search', 'page', 'per_page'];
        $table = $query->getModel()->getTable();
        $tableColumns = Schema::getColumnListing($table); // بيشتغل على PostgreSQL

        foreach ($request->except($excluded) as $key => $value) {
            if ($value === null || $value === '') continue;
            if (in_array($key, $tableColumns)) { // بدل Schema::hasColumn
                $query->where($key, $value);
            }
        }

        // Pagination
        $perPage = $request->input('per_page', $perPageDefault);
        $data = $query->latest()->paginate($perPage);

        // Apply Resource
        if ($resourceClass && class_exists($resourceClass)) {
            $data = $resourceClass::collection($data);
        }

        return $data;
    }
}
