<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pageId = DB::table('pages')->insertGetId([
            'title'      => 'الصفحة الرئيسية',
            'slug'       => '/home',
            'status'     => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
