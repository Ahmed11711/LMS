<?php

namespace Database\Seeders;

use App\Models\paymentInfo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            ['title' => 'حساب بنكي'],
            ['title' => 'محفظة إلكترونية'],
            ['title' => 'حساب دولي'],
        ];

        foreach ($methods as $method) {
            paymentInfo::updateOrCreate(
                ['title' => $method['title']],
                ['is_active' => true]
            );
        }
    }
}
