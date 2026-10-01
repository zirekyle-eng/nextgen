<?php

namespace Database\Seeders;

use App\Models\IslamicMaterial;
use Illuminate\Database\Seeder;

class IslamicMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Example primary stage lessons
        IslamicMaterial::create([
            'title' => 'سورة الفاتحة - التلاوة والتجويد',
            'description' => 'شرح سورة الفاتحة وكيفية قراءتها بشكل صحيح مع تطبيق أحكام التجويد',
            'content' => '<h2>مقدمة</h2><p>سورة الفاتحة هي أم القرآن الكريم، وهي أول سورة في المصحف الشريف...</p>',
            'type' => 'lesson',
            'subject' => 'Quran',
            'level' => 'beginner',
            'stage' => 'primary',
            'my_class_id' => null,
            'is_active' => true,
            'published_at' => now(),
        ]);

        IslamicMaterial::create([
            'title' => 'دعاء الاستيقاظ والنوم',
            'description' => 'تعلم الأدعية المأثورة عند الاستيقاظ والنوم',
            'content' => '<h2>دعاء الاستيقاظ</h2><p>الحمد لله الذي أحيانا بعد ما أماتنا وإليه النشور</p>',
            'type' => 'dua',
            'subject' => 'Islamic Ethics',
            'level' => 'beginner',
            'stage' => 'primary',
            'my_class_id' => null,
            'is_active' => true,
            'published_at' => now(),
        ]);

        // Example middle stage activities
        IslamicMaterial::create([
            'title' => 'السيرة النبوية - من المدينة إلى بدر',
            'description' => 'نشاط تفاعلي عن مراحل الهجرة والمعارك الأولى',
            'content' => '<h2>الهجرة النبوية</h2><p>الهجرة هي من أهم أحداث التاريخ الإسلامي...</p>',
            'type' => 'activity',
            'subject' => 'Seerah (Prophet\'s Biography)',
            'level' => 'intermediate',
            'stage' => 'middle',
            'my_class_id' => null,
            'is_active' => true,
            'published_at' => now(),
        ]);

        // Example secondary stage lesson
        IslamicMaterial::create([
            'title' => 'الإعجاز العلمي في القرآن الكريم',
            'description' => 'دراسة متقدمة عن كيفية توافق القرآن مع الاكتشافات العلمية الحديثة',
            'content' => '<h2>الإعجاز العلمي</h2><p>القرآن الكريم يتضمن إشارات علمية دقيقة...</p>',
            'type' => 'lesson',
            'subject' => 'Islamic Science',
            'level' => 'advanced',
            'stage' => 'secondary',
            'my_class_id' => null,
            'is_active' => true,
            'published_at' => now(),
        ]);

        // Example story for all stages
        IslamicMaterial::create([
            'title' => 'قصة أصحاب الكهف',
            'description' => 'قصة أصحاب الكهف من سورة الكهف وما تحتويه من دروس وعبر',
            'content' => '<h2>قصة الفتية</h2><p>ذكر الله قصة الفتية الذين آمنوا برب الكهف...</p>',
            'type' => 'story',
            'subject' => 'Quran',
            'level' => 'intermediate',
            'stage' => null, // For all stages
            'my_class_id' => null,
            'is_active' => true,
            'published_at' => now(),
        ]);
    }
}
