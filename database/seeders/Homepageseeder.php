<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HomePageSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // PAGE
        // ============================================================
        $pageId = DB::table('pages')->insertGetId([
            'title'      => 'الصفحة الرئيسية',
            'slug'       => '/home',
            'status'     => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ============================================================
        // SECTION 1 — Hero / Slider
        // ============================================================
        $heroId = DB::table('sections')->insertGetId([
            'pages_id'   => $pageId,
            'type'       => 'hero',
            'order'      => 1,
            'props'      => json_encode([
                'title'           => 'تعلم، طبق، وخلك مميز',
                'description'     => 'تأهيل كامل لسوق العمل من خلال مسارات تعليمية متكاملة بأساليب حديثة.',
                'height'          => '480px',
                'overlayOpacity'  => 0.55,
                'overlayColor'    => '#000000',
                'titleFontFamily' => 'Cairo',
                'titleFontSize'   => 'text-5xl',
                'titleColor'      => '#ffffff',
                'accentColor'     => '#1DB894',
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $heroSlides = [
            [
                'image'      => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1200&q=80',
                'buttonText' => 'اكتشف برامجنا التعليمية',
                'buttonLink' => '/courses',
            ],
            [
                'image'      => 'https://images.unsplash.com/photo-1531482615713-2afd69097998?w=1200&q=80',
                'buttonText' => 'تصفح الدورات',
                'buttonLink' => '/courses',
            ],
            [
                'image'      => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=1200&q=80',
                'buttonText' => 'ابدأ رحلتك',
                'buttonLink' => '/register',
            ],
        ];

        foreach ($heroSlides as $index => $slide) {
            DB::table('section_items')->insert([
                'section_id' => $heroId,
                'order'      => $index + 1,
                'props'      => json_encode($slide, JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ============================================================
        // SECTION 2 — About Us
        // ============================================================
        $aboutId = DB::table('sections')->insertGetId([
            'pages_id'   => $pageId,
            'type'       => 'about-us',
            'order'      => 2,
            'props'      => json_encode([
                'label'           => 'من نحن',
                'title'           => 'منصة إدراك — رحلتك نحو التميز المهني',
                'description'     => 'نحن منصة تعليمية عربية متخصصة في تقديم محتوى تدريبي عالي الجودة، نجمع بين الخبرة الأكاديمية والتطبيق العملي لنساعدك على بناء مسيرة مهنية ناجحة.',
                'image'           => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=600&q=80',
                'accentColor'     => '#1DB894',
                'backgroundColor' => '#ffffff',
                'titleFontFamily' => 'Cairo',
                'titleColor'      => '#1a1a2e',
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $aboutStats = [
            ['value' => '+100', 'label' => 'مشروع تطبيقي وعملي'],
            ['value' => '+500', 'label' => 'دورة تدريبية'],
            ['value' => '+50',  'label' => 'تخصص ومسار تعليمي'],
            ['value' => '+20K', 'label' => 'متعلم نشط'],
        ];

        foreach ($aboutStats as $index => $stat) {
            DB::table('section_items')->insert([
                'section_id' => $aboutId,
                'order'      => $index + 1,
                'props'      => json_encode($stat, JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ============================================================
        // SECTION 3 — Courses (dynamic)
        // ============================================================
        DB::table('sections')->insert([
            'pages_id'   => $pageId,
            'type'       => 'courses',
            'order'      => 3,
            'props'      => json_encode([
                'title'           => 'أحدث الدورات',
                'subtitle'        => 'اكتشف أبرز الدورات التي تساهم في تعزيز مهاراتك وتطوير مسيرتك المهنية',
                'viewAllText'     => 'تصفح المزيد من الدورات',
                'viewAllLink'     => '/courses',
                'buttonText'      => 'اشترك الآن',
                'accentColor'     => '#1DB894',
                'titleFontFamily' => 'Cairo',
                'gridCols'        => 3,
                'limit'           => 6,
                'filter'          => 'latest',
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ============================================================
        // SECTION 4 — Testimonials (dynamic)
        // ============================================================
        DB::table('sections')->insert([
            'pages_id'   => $pageId,
            'type'       => 'testimonials',
            'order'      => 4,
            'props'      => json_encode([
                'label'           => 'آراء المتعلمين',
                'title'           => 'تأثير حقيقي وتجارب ملهمة',
                'subtitle'        => 'ماذا يقول متعلمونا عن تجربتهم معنا',
                'gridCols'        => 3,
                'accentColor'     => '#1DB894',
                'backgroundColor' => '#f8fafc',
                'titleFontFamily' => 'Cairo',
                'titleColor'      => '#1a1a2e',
                'cardBg'          => '#ffffff',
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ============================================================
        // SECTION 5 — Features
        // ============================================================
        $featuresId = DB::table('sections')->insertGetId([
            'pages_id'   => $pageId,
            'type'       => 'features',
            'order'      => 5,
            'props'      => json_encode([
                'label'           => 'لماذا إدراك؟',
                'title'           => 'كل ما تحتاجه في مكان واحد',
                'subtitle'        => 'منصة متكاملة صُممت خصيصاً لمساعدتك على التعلم والنمو',
                'gridCols'        => 3,
                'accentColor'     => '#1DB894',
                'backgroundColor' => '#ffffff',
                'titleFontFamily' => 'Cairo',
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $features = [
            ['icon' => 'BookOpen',   'title' => 'محتوى عالي الجودة',       'description' => 'دورات مُعدّة باحترافية من قِبَل خبراء في مجالاتهم مع تحديث مستمر للمحتوى.',  'iconColor' => '#1DB894'],
            ['icon' => 'Award',      'title' => 'شهادات معتمدة',           'description' => 'احصل على شهادات إتمام معتمدة تُعزز مسيرتك المهنية وتُثبت كفاءتك.',           'iconColor' => '#f59e0b'],
            ['icon' => 'Users',      'title' => 'مجتمع تعليمي',            'description' => 'انضم لمجتمع من آلاف المتعلمين وتواصل مع المدربين مباشرة.',                    'iconColor' => '#6366f1'],
            ['icon' => 'Clock',      'title' => 'تعلم في أي وقت',          'description' => 'وصول غير محدود للمحتوى على مدار الساعة من أي جهاز.',                         'iconColor' => '#ec4899'],
            ['icon' => 'Layers',     'title' => 'مسارات تعليمية متكاملة',  'description' => 'مسارات مُنظَّمة خطوة بخطوة تأخذك من المبتدئ حتى الاحتراف.',                 'iconColor' => '#14b8a6'],
            ['icon' => 'Smartphone', 'title' => 'تعلم من أي مكان',         'description' => 'تطبيق موبايل متكامل يُتيح لك التعلم في أي مكان وفي أي وقت.',                'iconColor' => '#f97316'],
        ];

        foreach ($features as $index => $feature) {
            DB::table('section_items')->insert([
                'section_id' => $featuresId,
                'order'      => $index + 1,
                'props'      => json_encode($feature, JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ============================================================
        // SECTION 6 — FAQ
        // ============================================================
        $faqId = DB::table('sections')->insertGetId([
            'pages_id'   => $pageId,
            'type'       => 'faq',
            'order'      => 6,
            'props'      => json_encode([
                'label'           => 'الأسئلة الشائعة',
                'title'           => 'هل لديك أسئلة؟',
                'subtitle'        => 'إليك إجابات على الأسئلة الأكثر شيوعاً',
                'accentColor'     => '#1DB894',
                'backgroundColor' => '#f8fafc',
                'titleFontFamily' => 'Cairo',
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $faqs = [
            [
                'question' => 'هل الشهادات معتمدة؟',
                'answer'   => 'نعم، جميع شهاداتنا معتمدة ويمكن مشاركتها على LinkedIn وإرفاقها مع السيرة الذاتية.',
            ],
            [
                'question' => 'كيف يمكنني الوصول للمحتوى بعد الاشتراك؟',
                'answer'   => 'بمجرد الاشتراك ستحصل على وصول فوري وغير محدود لجميع محتويات الدورة من خلال حسابك.',
            ],
            [
                'question' => 'هل يمكنني التعلم بالسرعة التي تناسبني؟',
                'answer'   => 'بالتأكيد، المحتوى متاح 24/7 وأنت من يحدد وتيرة تعلمك دون أي قيود زمنية.',
            ],
            [
                'question' => 'ما هي طرق الدفع المتاحة؟',
                'answer'   => 'نقبل جميع بطاقات الائتمان، مدى، Apple Pay وكذلك التحويل البنكي.',
            ],
            [
                'question' => 'هل يمكنني استرداد المبلغ إذا لم يعجبني المحتوى؟',
                'answer'   => 'نعم، نوفر ضمان استرداد كامل خلال 7 أيام من الاشتراك بدون أي أسئلة.',
            ],
        ];

        foreach ($faqs as $index => $faq) {
            DB::table('section_items')->insert([
                'section_id' => $faqId,
                'order'      => $index + 1,
                'props'      => json_encode($faq, JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
