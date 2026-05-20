<?php

namespace App\Http\Resources\Admin\UserPlan;

use App\Http\Resources\Admin\Plan\PlanResource;
use App\Http\Resources\Admin\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPlanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'user_id'        => $this->user_id,
            'plan_id'        => $this->plan_id,
            'status'         => $this->status,
            'amount_paid'    => $this->amount_paid,
            'transaction_id' => $this->transaction_id,
            'receipt'        => $this->receipt ? asset('storage/' . $this->receipt) : null,
            'created_by'     => $this->created_by,
            'starts_at'      => $this->starts_at,
            'ends_at'        => $this->ends_at,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'user'           => $this->whenLoaded('user', fn() => new UserResource($this->user)),
            'plan'           => $this->whenLoaded('plan', fn() => new PlanResource($this->plan)),
        ];
    }
}
