<?php

namespace App\Http\Controllers\Admin\Plan;

use App\Repositories\Plan\PlanRepositoryInterface;
use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\Admin\Plan\PlanStoreRequest;
use App\Http\Requests\Admin\Plan\PlanUpdateRequest;
use App\Http\Resources\Admin\Plan\PlanResource;
use App\Models\PlanRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlanController extends BaseController
{
    public function __construct(PlanRepositoryInterface $repository)
    {
        parent::__construct();
        $this->initService(
            repository: $repository,
            collectionName: 'Plan'
        );
        $this->storeRequestClass  = PlanStoreRequest::class;
        $this->updateRequestClass = PlanUpdateRequest::class;
        $this->resourceClass      = PlanResource::class;
        $this->withRelationships  = ['rules'];
    }

    protected function beforeStore(array $data, Request $request): array
    {
        $data['owner_id']   =  auth()->id();
        unset($data['rules']);
        unset($data['scope']);

        return $data;
    }

    protected function beforeUpdate(array $data, $existingRecord, Request $request): array
    {
        unset($data['rules']);
        unset($data['scope']);

        return $data;
    }

    protected function afterStore($record, Request $request): void
    {
        $this->syncRules($record, $request);
    }

    protected function afterUpdate($record, $old, Request $request): void
    {
        $this->syncRules($record, $request);
    }

    private function syncRules($plan, Request $request): void
    {
        if (!$request->has('rules')) return;

        $plan->rules()->delete();

        $rules = collect($request->rules)->map(fn($rule) => [
            'plan_id'      => $plan->id,
            'type'         => $rule['type'],
            'reference_id' => $rule['reference_id'] ?? null,
        ]);

        PlanRule::insert($rules->toArray());
    }
}
