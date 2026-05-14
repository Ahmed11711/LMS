<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Plan extends Model
{


    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(PlanRule::class);
    }

    public function subscribers(): HasMany
    {
        return $this->hasMany(UserPlan::class);
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_type === 'App\Models\User' && $this->owner_id === $user->id;
    }
}
