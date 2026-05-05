<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CentralPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $connection = DB::connection('LMS_CENTER');

        // 1. Define System Features
        $features = [
            ['id' => 1, 'title' => 'Courses Count', 'key' => 'max_courses'],
            ['id' => 2, 'title' => 'Students Count', 'key' => 'max_students'],
            ['id' => 3, 'title' => 'Storage Space (GB)', 'key' => 'storage_limit'],
            ['id' => 4, 'title' => 'Technical Support 24/7', 'key' => 'support_24_7'],
            ['id' => 5, 'title' => 'Custom Certificates', 'key' => 'custom_certificates'],
            ['id' => 6, 'title' => 'Custom Domain', 'key' => 'custom_domain'],
            ['id' => 7, 'title' => 'Custom Subdomin', 'key' => 'custom_subdomin'],
        ];

        foreach ($features as $feature) {
            $connection->table('features')->updateOrInsert(
                ['id' => $feature['id']],
                array_merge($feature, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        // 2. Define Subscription Packages
        $packages = [
            [
                'id' => 4,
                'titile' => 'Trial Package', // Kept 'titile' as per your migration typo
                'desc' => 'Start your academy for free and explore all features',
                'price' => 0,
                'duration_months' => 0.25,
                'recomnd' => false,
                'features' => [
                    ['feature_id' => 1, 'value' => '2', 'lable' => '2 Courses Only'],
                    ['feature_id' => 2, 'value' => '2', 'lable' => '2 Students Only'],
                    ['feature_id' => 3, 'value' => '2', 'lable' => '2 GB Storage'],
                    ['feature_id' => 4, 'value' => '0', 'lable' => 'Not Supported'],
                    ['feature_id' => 5, 'value' => '0', 'lable' => 'Not Supported'],
                    ['feature_id' => 6, 'value' => '5', 'key' => 'custom_domain'],
                    ['feature_id' => 7, 'value' => '5', 'key' => 'custom_subdomin'],
                ]
            ],
            [
                'id' => 1,
                'titile' => 'Basic Package',
                'desc' => 'Perfect for individual instructors starting out',
                'price' => 500,
                'duration_months' => 1,
                'recomnd' => false,
                'features' => [
                    ['feature_id' => 1, 'value' => '5', 'lable' => '5 Courses'],
                    ['feature_id' => 2, 'value' => '50', 'lable' => '50 Students'],
                    ['feature_id' => 3, 'value' => '10', 'lable' => '10 GB Storage'],
                    ['feature_id' => 4, 'value' => '0', 'lable' => 'Not Supported'],
                    ['feature_id' => 5, 'value' => '1', 'lable' => 'Available'],
                    ['feature_id' => 6, 'value' => '50', 'key' => 'custom_domain'],
                    ['feature_id' => 7, 'value' => '50', 'key' => 'custom_subdomin'],
                ]
            ],
            [
                'id' => 2,
                'titile' => 'Professional Package',
                'desc' => 'Most popular for medium-sized centers',
                'price' => 1500,
                'duration_months' => 3,
                'recomnd' => true,
                'features' => [
                    ['feature_id' => 1, 'value' => '20', 'lable' => '20 Courses'],
                    ['feature_id' => 2, 'value' => '500', 'lable' => '500 Students'],
                    ['feature_id' => 3, 'value' => '100', 'lable' => '100 GB Storage'],
                    ['feature_id' => 4, 'value' => '1', 'lable' => 'Live Support'],
                    ['feature_id' => 5, 'value' => '1', 'lable' => 'Available'],
                    ['feature_id' => 6, 'value' => '500', 'key' => 'custom_domain'],
                    ['feature_id' => 7, 'value' => '500', 'key' => 'custom_subdomin'],
                ]
            ],
            [
                'id' => 3,
                'titile' => 'Enterprise Package',
                'desc' => 'Full control for large educational institutions',
                'price' => 5000,
                'duration_months' => 12,
                'recomnd' => false,
                'features' => [
                    ['feature_id' => 1, 'value' => '-1', 'lable' => 'Unlimited Courses'],
                    ['feature_id' => 2, 'value' => '-1', 'lable' => 'Unlimited Students'],
                    ['feature_id' => 3, 'value' => '1024', 'lable' => '1 TB Storage'],
                    ['feature_id' => 4, 'value' => '1', 'lable' => 'VIP Support'],
                    ['feature_id' => 5, 'value' => '1', 'lable' => 'Available'],
                    ['feature_id' => 6, 'value' => '5000', 'key' => 'custom_domain'],
                    ['feature_id' => 7, 'value' => '5000', 'key' => 'custom_subdomin'],
                ]
            ],
        ];

        // 3. Sync Data into Tables
        foreach ($packages as $pkgData) {
            $featureSet = $pkgData['features'];
            unset($pkgData['features']);

            $connection->table('packages')->updateOrInsert(
                ['id' => $pkgData['id']],
                array_merge($pkgData, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );

            foreach ($featureSet as $f) {
                $featureKey = $connection->table('features')->where('id', $f['feature_id'])->value('key');

                $connection->table('feature_packages')->updateOrInsert(
                    [
                        'package_id' => $pkgData['id'],
                        'feature_id' => $f['feature_id']
                    ],
                    [
                        'value' => $f['value'],
                        'lable' => $f['lable'],
                        'key_feature' => $featureKey,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
