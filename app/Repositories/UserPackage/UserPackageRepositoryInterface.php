<?php

namespace App\Repositories\UserPackage;

use App\Repositories\BaseRepository\BaseRepositoryInterface;

interface UserPackageRepositoryInterface extends BaseRepositoryInterface
{
    public function MyPackage($userId);
    public function expireActivePackage($userId);
    public function activateNewPackage(array $data);
    public function createPendingUpgrade(array $data);
    public function findPendingRequest($id);
    public function hasPendingRequest($userId);
}
