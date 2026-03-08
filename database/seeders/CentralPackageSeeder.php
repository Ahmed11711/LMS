<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CentralPackageSeeder extends Seeder
{
    public function run(): void
    {
        // استخدام اتصال القاعدة المركزية
        $connection = DB::connection('LMS_CENTER');

        // 1. إضافة المميزات الأساسية (Features)
        $features = [
            ['id' => 1, 'title' => 'عدد الكورسات', 'key' => 'max_courses'],
            ['id' => 2, 'title' => 'عدد الطلاب', 'key' => 'max_students'],
            ['id' => 3, 'title' => 'مساحة التخزين (GB)', 'key' => 'storage_limit'],
            ['id' => 4, 'title' => 'دعم فني 24/7', 'key' => 'support_24_7'],
            ['id' => 5, 'title' => 'شهادات مخصصة', 'key' => 'custom_certificates'],
        ];

        foreach ($features as $feature) {
            $connection->table('features')->updateOrInsert(
                ['id' => $feature['id']],
                array_merge($feature, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // 2. إضافة الباقات (Packages)
        $packages = [
            [
                'id' => 1,
                'titile' => 'الباقة الأساسية', // ملاحظة: مكتوبة titile كما في الميجريشن عندك
                'desc' => 'مثالية للمدربين المبتدئين',
                'price' => 500,
                'duration_months' => 1,
                'recomnd' => false,
                'features' => [
                    ['feature_id' => 1, 'value' => '5', 'lable' => '5 كورسات'],
                    ['feature_id' => 2, 'value' => '50', 'lable' => '50 طالب'],
                    ['feature_id' => 3, 'value' => '10', 'lable' => '10 جيجا'],
                    ['feature_id' => 4, 'value' => '0', 'lable' => 'غير مدعوم'],
                    ['feature_id' => 5, 'value' => '1', 'lable' => 'متاح'],
                ]
            ],
            [
                'id' => 2,
                'titile' => 'الباقة الاحترافية',
                'desc' => 'الباقة الأكثر شيوعاً للمراكز المتوسطة',
                'price' => 1500,
                'duration_months' => 3,
                'recomnd' => true, // Recommended
                'features' => [
                    ['feature_id' => 1, 'value' => '20', 'lable' => '20 كورس'],
                    ['feature_id' => 2, 'value' => '500', 'lable' => '500 طالب'],
                    ['feature_id' => 3, 'value' => '100', 'lable' => '100 جيجا'],
                    ['feature_id' => 4, 'value' => '1', 'lable' => 'دعم مباشر'],
                    ['feature_id' => 5, 'value' => '1', 'lable' => 'متاح'],
                ]
            ],
            [
                'id' => 3,
                'titile' => 'باقة المؤسسات',
                'desc' => 'كل ما تحتاجه لإدارة منظومة تعليمية كاملة',
                'price' => 5000,
                'duration_months' => 12,
                'recomnd' => false,
                'features' => [
                    ['feature_id' => 1, 'value' => '-1', 'lable' => 'كورسات غير محدودة'],
                    ['feature_id' => 2, 'value' => '-1', 'lable' => 'طلاب غير محدودين'],
                    ['feature_id' => 3, 'value' => '1024', 'lable' => '1 تيرا بايت'],
                    ['feature_id' => 4, 'value' => '1', 'lable' => 'دعم VIP'],
                    ['feature_id' => 5, 'value' => '1', 'lable' => 'متاح'],
                ]
            ],
        ];

        foreach ($packages as $pkgData) {
            // فصل المميزات عن بيانات الباقة الأساسية
            $featureSet = $pkgData['features'];
            unset($pkgData['features']);

            // إدخال الباقة
            $connection->table('packages')->updateOrInsert(
                ['id' => $pkgData['id']],
                array_merge($pkgData, ['created_at' => now(), 'updated_at' => now()])
            );

            // 3. ربط المميزات بالباقة في جدول feature_packages
            foreach ($featureSet as $f) {
                // جلب الـ key الخاص بالميزة عشان نحطه في عمود key_feature
                $featureKey = $connection->table('features')->where('id', $f['feature_id'])->value('key');

                $connection->table('feature_packages')->updateOrInsert(
                    ['package_id' => $pkgData['id'], 'feature_id' => $f['feature_id']],
                    [
                        'value' => $f['value'],
                        'lable' => $f['lable'], // مكتوبة lable كما في الميجريشن
                        'key_feature' => $featureKey,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
