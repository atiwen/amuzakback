<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Models\Photo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;


class admin extends Controller
{
    public function login(Request $request)
    {
        // Don't require authentication for the login page itself
        return Jetstream::inertia()->render($request, 'Admin/Login', []);
        // $user = DB::table('users')->where('id', $request->user()->id)->get();
        // if ($user[0]->verify > 1) {
        // } else {
        //     return 'access denied';
        // };
    }
    public function adm_status(Request $request)
    {
        // Check if user is authenticated and has admin role
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $notifs = DB::table('notifs')->where('is_readed', false)->where('is_user', 0)->orderBy('created', 'desc')->limit(7)->get();
        $deposits = DB::table('verify_req')->where('type', 0)->where('status', 0)->get();
        $debits = DB::table('verify_req')->where('type', 1)->where('status', 0)->get();
        $buys = DB::table('verify_req')->where('type', 2)->where('status', 0)->get();
        $sells = DB::table('verify_req')->where('type', 3)->where('status', 0)->get();
        $dep_t = DB::table('verify_req')->where('type', 4)->where('status', 0)->get();
        $deb_t = DB::table('verify_req')->where('type', 5)->where('status', 0)->get();
        $hesabs = DB::table('bank_info')->orderBy('time', 'desc')->where('verify', 0)->get();
        $acc = DB::table('users')->orderBy('created_at', 'desc')->where('verify', 0)->get();
        $counts = array(
            "dep" => count($deposits),
            "dept" => count($dep_t),
            "deb" => count($debits),
            "debt" => count($deb_t),
            "buy" => count($buys),
            "sell" => count($sells),
            "hesab" => count($hesabs),
            "acc" => count($acc),
        );
        $all = array(
            $notifs,
            $counts
        );
        return $all;
    }
    public function adm_noticed(Request $request, $id)
    {
        // Check if user is authenticated and has admin role
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        try {
            DB::table('notifs')->where('id', $id)->update([
                'is_noticed' => 1
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
        return response()->json(['message' => 'success']);
    }
    public function adm_notif_item(Request $request, $id)
    {
        // Check if user is authenticated and has admin role
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        try {
            DB::table('notifs')->where('id', $id)->update([
                'is_noticed' => 1
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
        return response()->json(['message' => 'success']);
    }
    public function admin(Request $request)
    {
        // Check if user is authenticated and has admin role
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('login');
        }
        
        $users = DB::table('users')->orderBy('created_at', 'desc')->take(10)->get();
        $courses = DB::table('courses')->orderBy('created_at', 'asc')->get();
        return Jetstream::inertia()->render($request, 'Admin/Panel', [
            'users' => $users,
            'courses' => $courses,
            'vaheds' => [],

        ]);
    }

    public function upmg(Request $request)
    {
        // Check if user is authenticated and has admin role
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $request->validate([
            'id' => ['required', 'integer'],
            'img' => 'image|mimes:png,jpg,jpeg|max:20048', // Fixed the typo in 'j/peg'
            'type' => ['required', 'string']
        ]);
        
        $user = $request->user();
        $path = $request->file('img')->store('photos', 'private');
        $photo = Photo::create([
            'path' => $path,
            'user_id' => $user->id,
        ]);
        $image_path = $photo->id;

        if ($request->type == "section_img") {
        } else if ($request->type == "course_img") {
            $course = DB::table('courses')->where('id', $request->id)->first(); // Use first() instead of get()
            if ($course && $request->hasFile('img')) {
                DB::table('courses')->where('id', $request->id)->update([
                    'imgurl' => $image_path
                ]);
            }
        }

        return redirect()->route('admin.edit_course', $request->id);
    }
    public function delmg(Request $request)
    {
        // Check if user is authenticated and has admin role
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $request->validate([
            'id' => ['required', 'integer'],
            'type' => ['required', 'string']
        ]);
        $course = DB::table('courses')->where('id', $request->id)->first();

        if ($course) {
            DB::table('courses')->where('id', $request->id)->update([
                'imgurl' => ""
            ]);
        }
        return redirect()->route('admin.edit_course', $request->id);
    }


    public function users(Request $request)
    {
        // Check if user is authenticated and has admin role
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('login');
        }
        
        $users = DB::table('users')->orderBy('created_at', 'desc')->get();

        return Jetstream::inertia()->render($request, 'Admin/Users', [
            'users' => $users,
        ]);
    }

    public function user(Request $request, $id)
    {
        // Check if user is authenticated and has admin role
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('login');
        }
        
        $acc = DB::table('users')->where('id', $id)->first();
        
        if (!$acc) {
            abort(404, 'User not found');
        }
        
        return Jetstream::inertia()->render($request, 'Admin/user', [
            'user' => $acc
        ]);
    }

    public function courses(Request $request)
    {
        // Check if user is authenticated and has admin role
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('login');
        }
        
        $courses = DB::table('courses')->orderBy('created_at', 'asc')->get();

        return Jetstream::inertia()->render($request, 'Admin/courses', [
            'courses' => $courses,
        ]);
    }

    public function add_course(Request $request)
    {
        // Check if user is authenticated and has admin role
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('login');
        }
        
        return Jetstream::inertia()->render($request, 'Admin/add_course', []);
    }

    # بک اند (PHP) - کد نهایی

    public function add_course_full(Request $request)
    {
        // Check if user is authenticated and has admin role
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'grade' => 'required|integer',
            'description' => 'nullable|string',
            'is_pro' => 'boolean',
            'chapters' => 'array',
            'chapters.*.title' => 'required|string',
            'chapters.*.lessons' => 'array',
            'chapters.*.lessons.*.title' => 'required|string',
            'chapters.*.lessons.*.sections' => 'array',
            'chapters.*.lessons.*.sections.*.contents' => 'required',
            'chapters.*.exam_questions' => 'array',
            'chapters.*.exam_questions.*.question' => 'required|string',
            'chapters.*.exam_questions.*.option1' => 'required|string',
            'chapters.*.exam_questions.*.option2' => 'required|string',
            'chapters.*.exam_questions.*.option3' => 'required|string',
            'chapters.*.exam_questions.*.option4' => 'required|string',
            'chapters.*.exam_questions.*.correct_option' => 'required|string|in:option1,option2,option3,option4',
        ]);

        DB::beginTransaction();

        try {
            // فقط فیلدهای مجاز courses
            $courseId = DB::table('courses')->insertGetId([
                'title' => $request->title,
                'grade' => $request->grade,
                'description' => $request->description ?? '',
                'is_pro' => $request->is_pro ?? false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($request->chapters as $index => $chapter) {
                $chapterId = DB::table('chapters')->insertGetId([
                    'course_id' => $courseId,
                    'title' => $chapter['title'],
                    'order' => $index, // اضافه شده
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($chapter['lessons'] as $lessonIndex => $lesson) {
                    $lessonId = DB::table('lessons')->insertGetId([
                        'chapter_id' => $chapterId,
                        'title' => $lesson['title'],
                        'order' => $lessonIndex, // اضافه شده
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    foreach ($lesson['sections'] as $sectionIndex => $section) {
                        DB::table('sections')->insert([
                            'lesson_id' => $lessonId,
                            'order' => $sectionIndex,
                            'title' => $section['title'],
                            'contents' => json_encode($section['contents']),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // اضافه کردن سوالات آزمون فصل
                if (isset($chapter['exam_questions']) && is_array($chapter['exam_questions'])) {
                    foreach ($chapter['exam_questions'] as $questionIndex => $question) {
                        DB::table('questions')->insert([
                            'reference_id' => $chapterId,
                            'type' => 'chapter',
                            'question' => $question['question'],
                            'option1' => $question['option1'],
                            'option2' => $question['option2'],
                            'option3' => $question['option3'],
                            'option4' => $question['option4'],
                            'correct_option' => $question['correct_option'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.edit_course', $courseId);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'خطا در ایجاد دوره: ' . $e->getMessage()]);
        }
    }



    public function edit_course(Request $request, $id)
    {
        // Check if user is authenticated and has admin role
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('login');
        }

        // 1. دریافت دوره
        $course = DB::table('courses')->where('id', $id)->first();

        if (!$course) {
            abort(404, 'Course not found');
        }

        // 2. تبدیل JSON intro_contents
        $introContents = json_decode($course->intro_contents, true);
        $course->intro_contents = is_array($introContents) ? $introContents : [];

        // 3. دریافت فصل‌ها به ترتیب
        $chapters = DB::table('chapters')
            ->where('course_id', $id)
            ->orderBy('order', 'asc')
            ->get()
            ->map(function ($chapter) {
                // 4. دریافت سوالات آزمون فصل
                $exam_questions = DB::table('questions')
                    ->where('reference_id', $chapter->id)
                    ->where('type', 'chapter')
                    ->select('id', 'question', 'option1', 'option2', 'option3', 'option4', 'correct_option')
                    ->get()
                    ->map(function ($question) {
                        return [
                            'id' => $question->id,
                            'question' => $question->question,
                            'option1' => $question->option1,
                            'option2' => $question->option2,
                            'option3' => $question->option3,
                            'option4' => $question->option4,
                            'correct_option' => $question->correct_option,
                        ];
                    });

                // 5. دریافت درس‌ها به ترتیب
                $lessons = DB::table('lessons')
                    ->where('chapter_id', $chapter->id)
                    ->orderBy('order', 'asc')
                    ->get()
                    ->map(function ($lesson) {
                        // 6. دریافت بخش‌ها به ترتیب
                        $sections = DB::table('sections')
                            ->where('lesson_id', $lesson->id)
                            ->orderBy('order', 'asc')
                            ->get()
                            ->map(function ($section) {
                                // 7. تبدیل JSON contents
                                $contents = json_decode($section->contents, true);
                                return [
                                    'id' => $section->id,
                                    'order' => $section->order,
                                    'title' => $section->title,
                                    'contents' => is_array($contents) ? $contents : [],
                                ];
                            });

                        return [
                            'id' => $lesson->id,
                            'title' => $lesson->title,
                            'sections' => $sections,
                        ];
                    });

                return [
                    'id' => $chapter->id,
                    'title' => $chapter->title,
                    'lessons' => $lessons,
                    'exam_questions' => $exam_questions,
                ];
            });

        
            return Inertia::render('Admin/edit_course', [
            'course' => $course,
            'chapters' => $chapters,
        ]);
    }

        public function update_course(Request $request, $id)
    {
        // Check if user is authenticated and has admin role
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'grade' => 'required|integer',
            'is_pro' => 'required|boolean',
            'intro_contents' => 'required|array',
            'intro_contents.*.content' => 'required|string',
            'chapters' => 'required|array',
            'chapters.*.title' => 'required|string|max:255',
            'chapters.*.lessons' => 'required|array',
            'chapters.*.lessons.*.title' => 'required|string|max:255',
            'chapters.*.lessons.*.sections' => 'required|array',
            'chapters.*.lessons.*.sections.*.title' => 'required|string',
            'chapters.*.lessons.*.sections.*.contents' => 'required|array',
            'chapters.*.exam_questions' => 'array',
            'chapters.*.exam_questions.*.question' => 'required|string',
            'chapters.*.exam_questions.*.option1' => 'required|string',
            'chapters.*.exam_questions.*.option2' => 'required|string',
            'chapters.*.exam_questions.*.correct_option' => 'required|string|in:option1,option2,option3,option4',
        ]);

        // 🔍 بررسی وجود دوره قبل از هرگونه تغییر
        $courseExists = DB::table('courses')->where('id', $id)->exists();
        if (!$courseExists) {
            return back()->withErrors(['error' => 'دوره مورد نظر یافت نشد.']);
        }

        DB::beginTransaction();

        try {
            // بروزرسانی اطلاعات اصلی دوره
            DB::table('courses')->where('id', $id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'grade' => $request->grade,
                'is_pro' => $request->is_pro,
                'intro_contents' => json_encode($request->intro_contents),
                'updated_at' => now(),
            ]);

            // حذف تمام داده‌های مرتبط (در صورت وجود)
            $chapters = DB::table('chapters')->where('course_id', $id)->get();
            foreach ($chapters as $chapter) {
                $lessons = DB::table('lessons')->where('chapter_id', $chapter->id)->get();
                foreach ($lessons as $lesson) {
                    DB::table('sections')->where('lesson_id', $lesson->id)->delete();
                }
                DB::table('lessons')->where('chapter_id', $chapter->id)->delete();
                DB::table('questions')->where('reference_id', $chapter->id)->where('type', 'chapter')->delete();
            }
            DB::table('chapters')->where('course_id', $id)->delete();

            // ایجاد مجدد ساختار جدید
            foreach ($request->chapters as $chapterIndex => $chapter) {
                $chapterId = DB::table('chapters')->insertGetId([
                    'course_id' => $id,
                    'title' => $chapter['title'],
                    'order' => $chapterIndex,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($chapter['lessons'] as $lessonIndex => $lesson) {
                    $lessonId = DB::table('lessons')->insertGetId([
                        'chapter_id' => $chapterId,
                        'title' => $lesson['title'],
                        'order' => $lessonIndex,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    foreach ($lesson['sections'] as $sectionIndex => $section) {
                        DB::table('sections')->insert([
                            'lesson_id' => $lessonId,
                            'order' => $sectionIndex,
                            'title' => $section['title'],
                            'contents' => json_encode($section['contents']),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                if (isset($chapter['exam_questions']) && is_array($chapter['exam_questions'])) {
                    foreach ($chapter['exam_questions'] as $question) {
                        DB::table('questions')->insert([
                            'reference_id' => $chapterId,
                            'type' => 'chapter',
                            'question' => $question['question'],
                            'option1' => $question['option1'],
                            'option2' => $question['option2'],
                            'option3' => $question['option3'],
                            'option4' => $question['option4'],
                            'correct_option' => $question['correct_option'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.edit_course', $id);
        } catch (\Exception $e) {
            DB::rollBack();
            // ❗ در production، جزئیات خطا نمایش داده نشود (برای امنیت)
            \Log::error('Course update error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'خطایی در بروزرسانی دوره رخ داد. لطفاً دوباره تلاش کنید.']);
        }
    }

    public function photos(Request $request)
    {
        // Check if user is authenticated and has admin role
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('login');
        }

        $photos = DB::table('photos')->get();
        return Jetstream::inertia()->render($request, 'Admin/photos', [
            'photos' => $photos,

        ]);
    }
}
