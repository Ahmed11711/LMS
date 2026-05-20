<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\BaseModel\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends TenantModel  implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    public $searchable = ['name'];
    public $filterable = ['id', 'role'];
    public array $allowedFields = ['id', 'name', 'email', 'created_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',

        ];
    }

    public function enrollments()
    {
        return $this->hasMany(UserSubscribe::class)->where('status', 'active');
    }
    public function lessonComments()
    {
        return $this->hasMany(LessonComment::class);
    }

    public function lessonNotes()
    {
        return $this->hasMany(LessonNote::class);
    }

    public function lessonProgresses()
    {
        return $this->hasMany(LessonProgress::class);
    }
    public function balance()
    {
        return $this->hasOne(UserBalance::class);
    }
    // User.php
    public function subscribes(): HasMany
    {
        return $this->hasMany(UserSubscribe::class);
    }

    public function plans(): HasMany
    {
        return $this->hasMany(UserPlan::class);
    }

    public function activePlan(): ?UserPlan
    {
        return $this->plans()
            ->where('status', 'active')
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->with('plan.rules')
            ->latest()
            ->first();
    }
}
