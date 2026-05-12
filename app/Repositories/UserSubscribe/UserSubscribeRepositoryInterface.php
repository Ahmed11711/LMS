<?php

namespace App\Repositories\UserSubscribe;

use App\Repositories\BaseRepository\BaseRepositoryInterface;

interface UserSubscribeRepositoryInterface extends BaseRepositoryInterface
{
    public function mycourses(int $userId);
    public function isAlreadySubscribed(int $userId, int $courseId);
    public function findByTransactionId(string $transactionId);
    public function updateOrCreate(array $conditions, array $data);
    public function hasCourse(int $userId, int $courseId);
    public function getCourseById(int $userId, int $courseId);
}
