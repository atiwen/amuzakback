<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;


class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // دوره
        DB::table('courses')->insert([
            [
                'id' => 1,
                'title' => 'ریاضی پایه',
                'description' => 'این دوره آموزش ریاضی پایه با مثال‌های ساده است.',
                'intro_contents' => json_encode([
                    ['type' => 'text', 'content' => 'به دوره ریاضی پایه خوش آمدید! این مقدمه کوتاه است.']
                ]),
                'imgurl' => '/images/math.jpg',
                'is_pro' => false,
                'tags' => json_encode(['ریاضی', 'پایه']),
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);

        // فصل‌ها
        DB::table('chapters')->insert([
            ['id' => 1, 'course_id' => 1, 'title' => 'فصل اول: اعداد', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'course_id' => 1, 'title' => 'فصل دوم: هندسه', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // درس‌ها
        DB::table('lessons')->insert([
            ['id' => 1, 'chapter_id' => 1, 'title' => 'درس اول: اعداد طبیعی', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'chapter_id' => 1, 'title' => 'درس دوم: اعداد صحیح', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'chapter_id' => 2, 'title' => 'درس اول: اشکال هندسی', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // بخش‌ها
        DB::table('sections')->insert([
            [
                'id' => 1,
                'title' => 'بخش 1',
                'lesson_id' => 1,
                'contents' => json_encode([
                    ['type' => 'heading', 'content' => 'اعداد طبیعی شامل اعداد ۱، ۲، ۳ و ... است.'],
                    ['type' => 'text', 'content' => 'مثال: ۵ عدد طبیعی است.'],
                ]),
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'title' => 'بخش 2',
                'lesson_id' => 1,
                'contents' => json_encode([
                    ['type' => 'heading', 'content' => 'جمع اعداد طبیعی به صورت ...'],
                    ['type' => 'text', 'content' => 'اعداد طبیعی شامل اعداد ۱، ۲، ۳ و ... است.'],
                    ['type' => 'text', 'content' => 'مثال: ۵ عدد طبیعی است.'],
                ]),
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'title' => 'بخش 3',
                'lesson_id' => 2,
                'contents' => json_encode([
                    ['type' => 'heading', 'content' => 'جمع اعداد طبیعی به صورت ...'],
                    ['type' => 'text', 'content' => 'اعداد طبیعی شامل اعداد ۱، ۲، ۳ و ... است.'],
                    ['type' => 'text', 'content' => 'مثال: ۵ عدد طبیعی است.'],
                    ['type' => 'text', 'content' => 'اعداد صحیح شامل اعداد مثبت و منفی است.'],
                ]),
                'order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'title' => 'بخش 4',
                'lesson_id' => 3,
                'contents' => json_encode([
                    ['type' => 'heading', 'content' => 'جمع اعداد طبیعی به صورت ...'],
                    ['type' => 'text', 'content' => 'اعداد طبیعی شامل اعداد ۱، ۲، ۳ و ... است.'],
                    ['type' => 'text', 'content' => 'مثال: ۵ عدد طبیعی است.'],
                    ['type' => 'text', 'content' => 'هندسه علم اشکال است.'],
                ]),
                'order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);



        // سوال درس ۱
        DB::table('questions')->insert([
            'type' => 'lesson',
            'reference_id' => 1,
            'question' => 'عدد ۵ جزء کدام دسته از اعداد است؟',
            'option1' => 'اعداد طبیعی',
            'option2' => 'اعداد صحیح',
            'option3' => 'اعداد گویا',
            'option4' => 'اعداد اول',
            'correct_option' => 'option1',
        ]);

        // سوال پایان فصل (مثلاً فصل ۱)
        DB::table('questions')->insert([
            'type' => 'chapter',
            'reference_id' => 1,
            'question' => 'هدف اصلی فصل اول چه بود؟',
            'option1' => 'معرفی اعداد صحیح',
            'option2' => 'آشنایی با عددهای طبیعی',
            'option3' => 'کار با کسرها',
            'option4' => 'حل معادلات',
            'correct_option' => 'option2',
        ]);

        // سوال پایان دوره (مثلاً دوره ۱)
        DB::table('questions')->insert([
            'type' => 'course',
            'reference_id' => 1,
            'question' => 'کدام موضوع در این دوره آموزش داده نشد؟',
            'option1' => 'اعداد طبیعی',
            'option2' => 'اعداد اول',
            'option3' => 'معادله درجه ۲',
            'option4' => 'کسر و اعشار',
            'correct_option' => 'option3',
        ]);



        // ========================== دوره دوم ===============================
        DB::table('courses')->insert([
            [
                'id' => 2,
                'title' => 'علوم پایه',
                'description' => 'دوره‌ای برای آشنایی با مفاهیم اولیه علوم.',
                'intro_contents' => json_encode([
                    ['type' => 'text', 'content' => 'در این دوره مفاهیم ابتدایی علوم را یاد می‌گیریم.']
                ]),
                'imgurl' => '/images/sin.jpg',
                'is_pro' => false,
                'tags' => json_encode(['علوم', 'پایه']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('chapters')->insert([
            ['id' => 3, 'course_id' => 2, 'title' => 'فصل اول: مواد', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'course_id' => 2, 'title' => 'فصل دوم: انرژی', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('lessons')->insert([
            ['id' => 4, 'chapter_id' => 3, 'title' => 'درس اول: حالت‌های ماده', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'chapter_id' => 3, 'title' => 'درس دوم: تغییر حالت‌ها', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'chapter_id' => 4, 'title' => 'درس اول: انرژی گرمایی', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('sections')->insert([
            ['id' => 5, 'title' => 'بخش 1', 'lesson_id' => 4, 'contents' => json_encode([
                ['type' => 'heading', 'content' => 'مواد به سه حالت جامد، مایع و گاز هستند.'],
            ]), 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'title' => 'بخش 2', 'lesson_id' => 5, 'contents' => json_encode([
                ['type' => 'text', 'content' => 'تغییر حالت از جامد به مایع را ذوب می‌نامند.'],
            ]), 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'title' => 'بخش 3', 'lesson_id' => 6, 'contents' => json_encode([
                ['type' => 'text', 'content' => 'گرما نوعی انرژی است.'],
            ]), 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('questions')->insert([
            'type' => 'lesson',
            'reference_id' => 4,
            'question' => 'کدام حالت از ماده شکل و حجم ثابتی ندارد؟',
            'option1' => 'جامد',
            'option2' => 'مایع',
            'option3' => 'گاز',
            'option4' => 'پلاسما',
            'correct_option' => 'option3',
        ]);

        DB::table('questions')->insert([
            'type' => 'chapter',
            'reference_id' => 3,
            'question' => 'حالت‌های مختلف ماده چیست؟',
            'option1' => 'جامد، مایع، گاز',
            'option2' => 'آب، خاک، هوا',
            'option3' => 'ذوب، انجماد، تبخیر',
            'option4' => 'اکسیژن، نیتروژن، کربن',
            'correct_option' => 'option1',
        ]);

        DB::table('questions')->insert([
            'type' => 'course',
            'reference_id' => 2,
            'question' => 'در این دوره کدام موضوع تدریس نشد؟',
            'option1' => 'انرژی گرمایی',
            'option2' => 'حالت‌های ماده',
            'option3' => 'نور و صوت',
            'option4' => 'تغییر حالت',
            'correct_option' => 'option3',
        ]);

        // ========================== دوره سوم ===============================
        DB::table('courses')->insert([
            [
                'id' => 3,
                'title' => 'زبان فارسی',
                'description' => 'آشنایی با اصول خواندن و نوشتن زبان فارسی.',
                'intro_contents' => json_encode([
                    ['type' => 'text', 'content' => 'در این دوره، با زبان فارسی بیشتر آشنا می‌شوید.']
                ]),
                'imgurl' => '/images/course.jpg',
                'is_pro' => false,
                'tags' => json_encode(['زبان', 'فارسی']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('chapters')->insert([
            ['id' => 5, 'course_id' => 3, 'title' => 'فصل اول: الفبا', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'course_id' => 3, 'title' => 'فصل دوم: جمله‌سازی', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('lessons')->insert([
            ['id' => 7, 'chapter_id' => 5, 'title' => 'درس اول: حروف صدادار', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'chapter_id' => 5, 'title' => 'درس دوم: حروف بی‌صدا', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'chapter_id' => 6, 'title' => 'درس اول: ساخت جمله ساده', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('sections')->insert([
            ['id' => 8, 'title' => 'بخش 1', 'lesson_id' => 7, 'contents' => json_encode([
                ['type' => 'text', 'content' => 'حروف صدادار شامل: آ، ا، او، ای هستند.'],
            ]), 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'title' => 'بخش 2', 'lesson_id' => 8, 'contents' => json_encode([
                ['type' => 'text', 'content' => 'حروف بی‌صدا مانند ب، پ، ت، ث هستند.'],
            ]), 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'title' => 'بخش 3', 'lesson_id' => 9, 'contents' => json_encode([
                ['type' => 'text', 'content' => 'مثال جمله ساده: من به مدرسه می‌روم.'],
            ]), 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('questions')->insert([
            'type' => 'lesson',
            'reference_id' => 7,
            'question' => 'حرف "آ" در کدام دسته قرار دارد؟',
            'option1' => 'بی‌صدا',
            'option2' => 'بی‌معنی',
            'option3' => 'صدادار',
            'option4' => 'خارجی',
            'correct_option' => 'option3',
        ]);

        DB::table('questions')->insert([
            'type' => 'chapter',
            'reference_id' => 5,
            'question' => 'در این فصل چه چیزی آموزش داده می‌شود؟',
            'option1' => 'ساخت جمله',
            'option2' => 'آشنایی با حروف الفبا',
            'option3' => 'آموزش خواندن متون',
            'option4' => 'نگارش انشا',
            'correct_option' => 'option2',
        ]);

        DB::table('questions')->insert([
            'type' => 'course',
            'reference_id' => 3,
            'question' => 'در این دوره چه چیزی آموزش داده نشد؟',
            'option1' => 'جمله‌سازی',
            'option2' => 'حروف صدادار',
            'option3' => 'گرامر پیشرفته',
            'option4' => 'حروف بی‌صدا',
            'correct_option' => 'option3',
        ]);
    }
}
