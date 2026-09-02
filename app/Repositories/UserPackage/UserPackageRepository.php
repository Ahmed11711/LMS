<?php

namespace App\Repositories\UserPackage;

use App\Repositories\UserPackage\UserPackageRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\Central\UserPackage;

class UserPackageRepository extends BaseRepository implements UserPackageRepositoryInterface
{
    public function __construct(UserPackage $model)
    {
        parent::__construct($model);
    }

    public function MyPackage($userId)
    {
        return $this->model->where('user_id', $userId)->where('active', 1)->first();
    }

    public function expireActivePackage($userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('active', 1)
            ->update([
                'active'     => 0,
                'status'     => 'expired',
                'updated_at' => now(),
            ]);
    }

    public function activateNewPackage(array $data)
    {
        return $this->model->create($data);
    }

    public function createPendingUpgrade(array $data)
    {
        return $this->model->create($data);
    }

    public function findPendingRequest($id)
    {
        return $this->model->where('id', $id)->where('status', 'pending')->first();
    }

    public function hasPendingRequest($userId)
    {
        return $this->model->where('user_id', $userId)->where('status', 'pending')->exists();
    }
}
