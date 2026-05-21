<?php

namespace App\Repositories\UserSubscribe;

use App\Repositories\UserSubscribe\UserSubscribeRepositoryInterface;
use App\Repositories\BaseRepository\BaseRepository;
use App\Models\UserSubscribe;

class UserSubscribeRepository extends BaseRepository implements UserSubscribeRepositoryInterface
{
    public function __construct(UserSubscribe $model)
    {
        parent::__construct($model);
    }
    public function mycourses($userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->with('course')
            ->get();
    }
    public function isAlreadySubscribed(int $userId, int $courseId): bool
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('status', 'active')->exists();
    }

    public function findByTransactionId(string $transactionId)
    {
        return $this->model
            ->where('transaction_id', $transactionId)
            ->first();
    }

    public function updateOrCreate(array $conditions, array $data)
    {
        return $this->model->updateOrCreate($conditions, $data);
    }
    public function hasCourse(int $userId, int $courseId): bool
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('status', 'active')
            ->exists();
    }
    public function getCourseById(int $userId, int $courseId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('status', 'active')
            ->with('course.chapters.lessons.progresses')
            ->first();
    }
    public function getUserSubscribes(int $userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->with('course')
            ->get();
    }
}
