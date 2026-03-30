<?php

namespace App\Http\Controllers\BaseController;

use \App\QueryFilters\ColumnFilter;
use \App\QueryFilters\Search;
use \App\QueryFilters\SelectFields;
use \App\QueryFilters\SortBy;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

abstract class BaseController extends Controller
{
  use ApiResponseTrait;

  protected $repository;
  protected string $storeRequestClass;
  protected string $updateRequestClass;
  protected string $resourceClass;
  protected ?string $collectionName = null;
  protected array $fileFields = [];
  protected string $uploadDisk = 'public';
  protected array $withRelationships = [];

  public function __construct() {}

  protected function initService($repository, string $collectionName, array $fileFields = [], string $uploadDisk = 'public'): void
  {
    $this->repository = $repository;
    $this->collectionName = $collectionName;
    $this->fileFields = $fileFields;
    $this->uploadDisk = $uploadDisk;
  }

  // public function index(Request $request): JsonResponse
  // {
  //   try {
  //     $query = $this->repository->query()->with($this->withRelationships);

  //     if ($search = $request->input('search')) {
  //       $query->where(function ($q) use ($search) {
  //         $table = $q->getModel()->getTable();
  //         $stringColumns = Schema::getColumnListing($table);
  //         $stringColumns = array_filter($stringColumns, function ($col) {
  //           return !in_array($col, ['id', 'created_at', 'updated_at', 'deleted_at']);
  //         });
  //         foreach ($stringColumns as $column) {
  //           $q->orWhere($column, 'like', "%{$search}%");
  //         }
  //       });
  //     }

  //     $excluded = ['search', 'page', 'per_page'];
  //     foreach ($request->except($excluded) as $key => $value) {
  //       if ($value === null || $value === '') continue;
  //       if (Schema::hasColumn($query->getModel()->getTable(), $key)) {
  //         $query->where($key, $value);
  //       }
  //     }

  //     $perPage = $request->input('per_page', 10);
  //     $data = $query->latest()->paginate($perPage);

  //     if (class_exists($this->resourceClass)) {
  //       $data = $this->resourceClass::collection($data);
  //     }

  //     return $this->successResponsePaginate($data, "{$this->collectionName} list retrieved successfully");
  //   } catch (\Throwable $e) {
  //     Log::error("Error in {$this->collectionName} index: " . $e->getMessage());
  //     return $this->errorResponse("Failed to fetch data", 500, $e->getMessage());
  //   }
  // }
  // App\Http\Controllers\BaseController\BaseController.php

  public function index(Request $request): JsonResponse
  {
    try {
      $query = $this->repository->query()->with($this->withRelationships);
      $query = $this->applyScoping($query);

      $data = app(Pipeline::class)
        ->send($query)
        ->through([
          Search::class,
          ColumnFilter::class,
          SelectFields::class,
          SortBy::class,
        ])
        ->thenReturn()
        ->latest()
        ->paginate($request->input('per_page', 10));

      if (class_exists($this->resourceClass)) {
        $data = $this->resourceClass::collection($data);
      }

      return $this->successResponsePaginate($data, "Data retrieved via Pipeline");
    } catch (\Throwable $e) {
      Log::error("Pipeline Error: " . $e->getMessage());
      return $this->errorResponse("Failed to fetch data", 500);
    }
  }


  protected function applyScoping($query)
  {
    return $query;
  }

  public function show(int $id): JsonResponse
  {
    $record = $this->repository->query()->with($this->withRelationships)->find($id);
    if (!$record) {
      return $this->errorResponse("Record not found", 404);
    }

    return $this->successResponse(new $this->resourceClass($record), 'Record retrieved successfully');
  }



  public function store(Request $request): JsonResponse
  {
    $validated = app($this->storeRequestClass)->validated();

    try {
      DB::beginTransaction();

      $validated = $this->beforeStore($validated, $request);

      $validated = $this->handleFileUploads($request, $validated);
      $record = $this->repository->create($validated);

      $this->afterStore($record, $request);

      DB::commit();
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error("Error creating {$this->collectionName}: " . $e->getMessage());
      return $this->errorResponse("Failed to create {$this->collectionName}: " . $e->getMessage(), 500);
    }

    return $this->successResponse(new $this->resourceClass($record), 'Record created successfully', 201);
  }

  public function update(Request $request, int $id): JsonResponse
  {
    $validated = app($this->updateRequestClass)->validated();

    $record = $this->applyScoping($this->repository->query())->find($id);

    if (!$record) {
      return $this->errorResponse("Record not found or unauthorized", 404);
    }

    try {
      DB::beginTransaction();
      $validated = $this->beforeUpdate($validated, $record, $request);
      $validated = $this->handleFileUploads($request, $validated, $record);

      $record->update($validated);

      $this->afterUpdate($record, $record, $request);
      DB::commit();
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error("Error updating: " . $e->getMessage());
      return $this->errorResponse("Failed to update record", 500);
    }

    return $this->successResponse(new $this->resourceClass($record), 'Record updated successfully');
  }

  public function destroy($id): JsonResponse
  {
    $record = $this->applyScoping($this->repository->query())->find($id);

    if (!$record) {
      return $this->errorResponse("Record not found or unauthorized", 404);
    }

    try {
      DB::beginTransaction();
      $this->beforeDestroy($record);

      $record->delete();

      $this->afterDestroy($record);
      DB::commit();
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error("Error deleting: " . $e->getMessage());
      return $this->errorResponse("Failed to delete record", 500);
    }

    return $this->successResponse(null, "Record deleted successfully");
  }

  // public function update(Request $request, int $id): JsonResponse
  // {
  //   $validated = app($this->updateRequestClass)->validated();

  //   $record = $this->repository->find($id);
  //   if (!$record) {
  //     return $this->errorResponse("Record not found", 404);
  //   }

  //   try {
  //     DB::beginTransaction();

  //     $validated = $this->beforeUpdate($validated, $record, $request);

  //     $validated = $this->handleFileUploads($request, $validated, $record);
  //     $updatedRecord = $this->repository->update($id, $validated);

  //     $this->afterUpdate($updatedRecord, $record, $request);

  //     DB::commit();
  //   } catch (\Throwable $e) {
  //     DB::rollBack();
  //     Log::error("Error updating {$this->collectionName}: " . $e->getMessage());
  //     return $this->errorResponse("Failed to update record", 500);
  //   }

  //   return $this->successResponse(new $this->resourceClass($updatedRecord), 'Record updated successfully');
  // }

  // public function destroy($id): JsonResponse
  // {
  //   $record = $this->repository->find($id);
  //   if (!$record) {
  //     return $this->errorResponse("Record not found", 404);
  //   }

  //   try {
  //     DB::beginTransaction();

  //     $this->beforeDestroy($record);

  //     $deletedCount = $this->repository->delete($id);

  //     $this->afterDestroy($record);

  //     DB::commit();
  //   } catch (\Throwable $e) {
  //     DB::rollBack();
  //     Log::error("Error deleting {$this->collectionName}: " . $e->getMessage());
  //     return $this->errorResponse($e->getMessage() ?: "Failed to delete record", 500);
  //   }

  //   return $this->successResponse(null, "Record deleted successfully");
  // }

  protected function handleFileUploads(Request $request, array $validated, $existingRecord = null): array
  {
    if (empty($this->fileFields)) return $validated;

    foreach ($this->fileFields as $field) {
      if ($request->hasFile($field)) {
        try {
          $file = $request->file($field);
          $filename = time() . '_' . $file->getClientOriginalName();
          $path = $file->storeAs("uploads/{$this->collectionName}", $filename, $this->uploadDisk);

          if ($existingRecord && !empty($existingRecord->$field)) {
            Storage::disk($this->uploadDisk)
              ->delete('uploads/' . $this->collectionName . '/' . basename($existingRecord->$field));
          }

          /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
          // $disk = Storage::disk($this->uploadDisk);
          // $validated[$field] = "https://astar.zayamrock.com/storage/app/public/" . $path;
          $validated[$field] = config('app.url') . "/storage/app/public/" . $path;
        } catch (\Throwable $e) {
          Log::error("File upload failed for field [{$field}] in {$this->collectionName}: " . $e->getMessage());
        }
      }
    }

    return $validated;
  }

  protected function beforeStore(array $data, Request $request): array
  {
    return $data;
  }
  protected function afterStore($record, Request $request): void {}
  protected function beforeUpdate(array $data, $existingRecord, Request $request): array
  {
    return $data;
  }
  protected function afterUpdate($updatedRecord, $oldRecord, Request $request): void {}
  protected function beforeDestroy($record): void {}
  protected function afterDestroy($record): void {}
}
