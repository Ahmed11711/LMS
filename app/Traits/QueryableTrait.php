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
    $stringColumns = Schema::getColumnListing($table);
    $stringColumns = array_filter($stringColumns, function ($col) {
     return !in_array($col, ['id', 'created_at', 'updated_at', 'deleted_at']);
    });
    foreach ($stringColumns as $column) {
     $q->orWhere($column, 'like', "%{$search}%");
    }
   });
  }

  // Filters
  $excluded = ['search', 'page', 'per_page'];
  foreach ($request->except($excluded) as $key => $value) {
   if ($value === null || $value === '') continue;
   if (Schema::hasColumn($query->getModel()->getTable(), $key)) {
    $query->where($key, $value);
   }
  }

  // Pagination
  $perPage = $request->input('per_page', $perPageDefault);
  $data = $query->latest()->paginate($perPage);

  // Apply Resource if exists
  if ($resourceClass && class_exists($resourceClass)) {
   $data = $resourceClass::collection($data);
  }

  return $data;
 }
}
