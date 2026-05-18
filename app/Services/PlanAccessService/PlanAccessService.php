<?php

namespace App\Services\PlanAccessService;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Collection;
use App\Models\PlanRule;

// app/Services/PlanAccessService.php
class PlanAccessService
{
    public function canAccess(User $user, Course $course): bool
    {
        $directBuy = $user->subscribes()
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->exists();

        if ($directBuy) return true;

        $userPlan = $user->activePlan();
        if (!$userPlan) return false;

        return $userPlan->plan->rules->contains(
            fn($rule) => $this->ruleMatchesCourse($rule, $course)
        );
    }

    private function ruleMatchesCourse(PlanRule $rule, Course $course): bool
    {
        return match ($rule->type) {
            'all'        => true,
            'instructor' => $course->instructor_id === $rule->reference_id,
            'category'   => $course->category_id   === $rule->reference_id,
            'course'     => $course->id             === $rule->reference_id,
            default      => false,
        };
    }

    public function getAccessibleCourses(User $user): Collection
    {
        $fromPlan = collect();
        $userPlan = $user->activePlan();

        if ($userPlan) {
            $fromPlan = $userPlan->plan->rules
                ->flatMap(fn($rule) => $this->resolveRule($rule));
        }

        $fromDirect = Course::published()
            ->whereHas(
                'subscribes',
                fn($q) =>
                $q->where('user_id', $user->id)
                    ->where('status', 'active')
            )->get();

        return $fromPlan->merge($fromDirect)->unique('id');
    }

    private function resolveRule(PlanRule $rule): Collection
    {
        return match ($rule->type) {
            'all'        => Course::published()->get(),
            'instructor' => Course::published()->where('user_id', $rule->reference_id)->get(),
            'category'   => Course::published()->where('category_id', $rule->reference_id)->get(),
            'course'     => Course::published()->where('id',            $rule->reference_id)->get(),
            default      => collect(),
        };
    }
}
