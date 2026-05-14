<?php

namespace App\Http\Controllers\BaseController;

use App\QueryFilters\ColumnFilter;
use App\QueryFilters\Search;
use App\QueryFilters\SelectFields;
use App\QueryFilters\SortBy;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class BaseController extends Controller
{
  use ApiResponseTrait;

  protected $repository;
  protected string $storeRequestClass;
  protected string $updateRequestClass;
  protected string $resourceClass;
  protected ?string $showResourceClass = null;
  protected ?string $collectionName = null;
  protected array $fileFields = [];
  protected string $uploadDisk = 'public';
  protected array $withRelationships = [];

  protected bool $hasGallery = false;
  protected bool $isUserBound = false;

  public function __construct() {}

  protected function initService($repository, string $collectionName, array $fileFields = [], string $uploadDisk = 'public'): void
  {
    $this->repository = $repository;
    $this->collectionName = $collectionName;
    $this->fileFields = $fileFields;
    $this->uploadDisk = $uploadDisk;
  }

  /**
   * Display a listing of the resource via Pipeline.
   */
  public function index(Request $request): JsonResponse
  {
    try {
      $query = $this->repository->query()->with($this->getIndexRelationships());
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
    if ($this->isUserBound) {
      if (
        request()->isMethod('post') || request()->isMethod('put') ||
        request()->isMethod('patch') || request()->isMethod('delete')
      ) {
        return $query->where('user_id', auth('api')->id());
      }
    }

    return $query;
  }

  /**
   * Display the specified resource.
   */
  public function show($id): JsonResponse
  {
    $query = $this->repository->query()
      ->with($this->getShowRelationships());

    $query = $this->applyScoping($query);

    $record = $query->where($this->lookupColumn(), $id)->first();

    if (!$record) {
      return $this->errorResponse("Record not found", 404);
    }

    $resourceToShow = $this->showResourceClass ?? $this->resourceClass;
    return $this->successResponse(new $resourceToShow($record), 'Record retrieved successfully');
  }



  public function store(Request $request): JsonResponse
  {
    $validated = app($this->storeRequestClass)->validated();

    try {
      DB::beginTransaction();

      $validated = $this->beforeStore($validated, $request);
      $validated = $this->handleFileUploads($request, $validated);

      $record = $this->repository->create($validated);

      if ($this->hasGallery) {
        $this->uploadGalleryFiles($request, $record);
      }

      $this->afterStore($record, $request);

      DB::commit();

      $record->load($this->withRelationships);

      return $this->successResponse(new $this->resourceClass($record), 'Record created successfully', 201);
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error("Error creating {$this->collectionName}: " . $e->getMessage());
      return $this->errorResponse("Failed to create {$this->collectionName}: " . $e->getMessage(), 500);
    }
  }

  /**
   * Update the specified resource in storage.
   */
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

      if ($this->hasGallery) {
        $this->uploadGalleryFiles($request, $record);
      }

      $this->afterUpdate($record, $record, $request);

      DB::commit();

      $record->load($this->withRelationships);

      return $this->successResponse(new $this->resourceClass($record), 'Record updated successfully');
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
      DB::rollBack();
      return $this->errorResponse($e->getMessage(), $e->getStatusCode());
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error("Error updating: " . $e->getMessage());
      return $this->errorResponse("Failed to update record", 500);
    }
  }

  /**
   * Remove the specified resource from storage.
   */
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
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
      DB::rollBack();
      return $this->errorResponse($e->getMessage(), $e->getStatusCode());
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error("Error deleting: " . $e->getMessage());
      return $this->errorResponse("Failed to delete record", 500);
    }

    return $this->successResponse(null, "Record deleted successfully");
  }

  protected function getIndexRelationships(): array
  {
    return $this->withRelationships;
  }

  protected function getShowRelationships(): array
  {
    return $this->withRelationships;
  }

  /**
   */
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
              ->delete(str_replace('/storage/', '', $existingRecord->$field));
          }

          $validated[$field] = "/storage/" . $path;
        } catch (\Throwable $e) {
          Log::error("File upload failed for field [{$field}] in {$this->collectionName}: " . $e->getMessage());
        }
      }
    }

    return $validated;
  }
  protected function lookupColumn(): string
  {
    return 'id';
  }
  /**
   */
  protected function uploadGalleryFiles(Request $request, $record): void
  {
    if ($request->hasFile('gallery') && method_exists($record, 'gallery')) {
      foreach ($request->file('gallery') as $file) {
        try {
          $filename = time() . '_' . Str::random(8) . '_' . $file->getClientOriginalName();
          $path = $file->storeAs("uploads/{$this->collectionName}/gallery", $filename, $this->uploadDisk);

          $record->gallery()->create([
            'image' => "/storage/app/public/" . $path
          ]);
        } catch (\Throwable $e) {
          Log::error("Gallery upload failed for {$this->collectionName}: " . $e->getMessage());
        }
      }
    }
  }

  // Hooks
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
