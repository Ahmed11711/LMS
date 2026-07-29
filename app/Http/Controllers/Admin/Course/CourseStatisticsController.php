<?php

namespace App\Http\Controllers\Admin\Course;

use App\Http\Controllers\Controller;
use App\Models\UserSubscribe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseStatisticsController extends Controller
{
    /**
     * كروت النظرة العامة (Overview Cards)
     */
    public function overview(Request $request)
    {
        $query = UserSubscribe::query();

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $totalSubscribers = (clone $query)->distinct('user_id')->count('user_id');

        $totalSales = (clone $query)
            ->where('status', 'active') // أو أي status بتعتبره "مدفوع"
            ->sum('price');

        // TODO: لسه مفيش جدول لتتبع تقدم اليوزر جوه الكورس (lessons completed)
        // لو عندك جدول زي user_lesson_progress هنربطه هنا
        $averageCompletion = null;

        // TODO: لسه مفيش جدول لتتبع زيارات الصفحة
        $pageVisits = null;
        $conversionRate = null; // = (totalSubscribers / pageVisits) * 100 لما يبقى عندنا الجدول

        return response()->json([
            'average_completion' => $averageCompletion,
            'conversion_rate'    => $conversionRate,
            'page_visits'        => $pageVisits,
            'total_sales'        => (float) $totalSales,
            'total_subscribers'  => $totalSubscribers,
        ]);
    }

    /**
     * جدول المشتركين مع البحث والتصفية والترقيم
     */
    public function subscribers(Request $request)
    {
        $query = UserSubscribe::with(['user:id,name,email,phone', 'course:id,name'])
            ->select('user_subscribes.*');

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $subscribers = $query
            ->latest('starts_at')
            ->paginate($request->get('per_page', 10));

        $subscribers->getCollection()->transform(function ($subscribe) {
            return [
                'id'               => $subscribe->id,
                'name'             => $subscribe->user->name ?? null,
                'email'            => $subscribe->user->email ?? null,
                'phone'            => $subscribe->user->phone ?? null,
                'course'           => $subscribe->course->name ?? null,
                'subscribed_at'    => $subscribe->starts_at,
                'price'            => $subscribe->price,
                'status'           => $subscribe->status,
                // TODO: هيتحسب لما يبقى عندنا جدول تقدم اليوزر
                'progress_percent' => null,
                // TODO: هيتحسب لما يبقى عندنا last_login_at في جدول users
                'last_active'      => null,
            ];
        });

        return response()->json($subscribers);
    }
}
