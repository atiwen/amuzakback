<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\reg_req;
use App\Models\UserQuestionResult;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Chapter;
use App\Models\User;
use App\Models\CourseUser;
use App\Models\LessonAnswer;
use App\Models\LessonQuestion;
use App\Models\Section;
use App\Models\Question;





class jwt extends Controller
{


    public function dashboard(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['status' => 'expired']);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['status' => 'invalid']);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['status' => $e->getMessage()]);
        }

        // دریافت دوره‌هایی با پایه تحصیلی مشابه کاربر
        $courses = Course::where('grade', $user->grade)
            ->take(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'courses' => $courses,
            'user' => $user
        ]);
    }

    public function submit_user_info(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['status' => 'expired']);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['status' => 'invalid']);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['status' => $e->getMessage()]);
        }

        $request->validate([
            'grade' => 'required|integer|between:1,12',
            'fav_lessons' => 'required|array|min:1'
        ]);

        DB::table('users')->where('id', $user->id)->update([
            'grade' => $request->grade,
            'favorite_subjects' => json_encode($request->fav_lessons),
            'is_completed'  => true
        ]);
        $user = DB::table('users')->where('id', $user->id)->get();
        return response()->json(['status' => 'success', 'user' => $user]);
    }





    public function show_course(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['status' => 'expired']);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['status' => 'invalid']);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }

        $request->validate([
            'id' => 'required|exists:courses,id',
        ]);

        $id = $request->id;

        $course = Course::with(['chapters.lessons'])->find($id);
        if (!$course) {
            return response()->json(['status' => 'error', 'message' => 'Course not found'], 404);
        }

        // وضعیت شروع دوره
        $pivot = $user->courses()->where('course_id', $id)->first()?->pivot;
        $is_started = $pivot ? true : false;
        $is_completed = $pivot && $pivot->completed_at !== null;


        $courseId = $request->id;
        $courseUser = CourseUser::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $courseId],
            ['enrolled_at' => now()]
        );

        $chapters = Chapter::where('course_id', $courseId)
            ->orderBy('id')
            ->with(['lessons' => function ($q) {
                $q->orderBy('id');
            }])
            ->get()
            ->values(); // make sure it's re-indexed

        $chapterIndex = $courseUser->current_chapter_index ?? 0;
        $lessonIndex = $courseUser->current_lesson_index ?? 0;

        // مسیر فعلی کاربر
        $currentChapter = $chapters[$chapterIndex] ?? null;
        $currentLesson = $currentChapter?->lessons[$lessonIndex] ?? null;

        if (!$currentChapter || !$currentLesson) {
            return response()->json(['status' => 'error', 'message' => 'جایگاه کاربر نامعتبر است'], 400);
        }

        // تولید مسیر کامل با وضعیت رد شده
        $chapters = $chapters->map(function ($chapter, $chIndex) use ($chapterIndex, $lessonIndex) {
            return [
                'id' => $chapter->id,
                'title' => $chapter->title,
                'lessons' => $chapter->lessons->map(function ($lesson, $leIndex) use ($chIndex, $chapterIndex, $lessonIndex) {
                    $isPassed = $chIndex < $chapterIndex || ($chIndex === $chapterIndex && $leIndex < $lessonIndex);
                    return [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'passed' => $isPassed,
                    ];
                }),
            ];
        });


        return response()->json([
            'status' => 'success',
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'imgurl' => $course->imgurl,
                'description' => $course->description,
                'is_pro' => $course->is_pro,
                'intro_contents' => $course->intro_contents,
                'is_started' => $is_started,
                'user_subscription' => $user->subscription_type,
                'is_completed' => $is_completed,
                'is_restarted' => $courseUser->is_restarted,
                'chapters' => $chapters,
            ]
        ]);
    }
    public function startCourse(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['status' => 'expired']);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['status' => 'invalid']);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }

        $request->validate([
            'id' => 'required|exists:courses,id',
        ]);

        $courseId = $request->id;
        $course = Course::with(['chapters.lessons.sections'])->find($courseId);

        // چک اشتراک ویژه
        if ($course->is_pro && $user->subscription_type !== 'pro') {
            return response()->json([
                'status' => 'forbidden',
                'message' => 'این دوره فقط برای اعضای ویژه قابل دسترسی است'
            ], 403);
        }

        $courseUser = CourseUser::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $courseId],
            ['enrolled_at' => now()]
        );

        // اگر آزمون فصل در حالت pending است
        if ($courseUser->pending_exam_chapter_id) {
            $chapter = Chapter::find($courseUser->pending_exam_chapter_id);
            if ($chapter) {
                $existingQuestions = DB::table('questions')->where('reference_id', $chapter->id)->get();

                if ($existingQuestions->isEmpty()) {
                    foreach ($chapter->lessons as $lesson) {
                        DB::table('questions')->insert([
                            'reference_id' => $chapter->id,
                            'question' => 'سؤال مربوط به درس: ' . $lesson->title,
                            'option1' => 'گزینهٔ ۱',
                            'option2' => 'گزینهٔ ۲',
                            'option3' => 'گزینهٔ ۳',
                            'option4' => 'گزینهٔ ۴',
                            'correct_option' => 'option1',
                        ]);
                    }
                    $existingQuestions = DB::table('questions')->where('reference_id', $chapter->id)->get();
                }

                $questionsForClient = $existingQuestions->map(function ($q) {
                    return [
                        'id' => $q->id,
                        'question' => $q->question,
                        'options' => [
                            'option1' => $q->option1,
                            'option2' => $q->option2,
                            'option3' => $q->option3,
                            'option4' => $q->option4,
                        ],
                    ];
                });

                return response()->json([
                    'status' => 'exam_pending',
                    'type' => 'chapter_exam',
                    'chapter_id' => $chapter->id,
                    'chapter_title' => $chapter->title,
                    'questions' => $questionsForClient,
                    'message' => 'آزمون فصل آماده است — لطفاً پاسخ‌ها را ارسال کنید',
                ]);
            }
        }

        // بقیه منطق قبلی برای شروع درس
        $chapters = $course->chapters->sortBy('id')->values();
        if ($chapters->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Course has no chapters'], 400);
        }

        $chapterIndex = $courseUser->current_chapter_index ?? 0;
        if (!isset($chapters[$chapterIndex])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid chapter index'], 400);
        }

        $currentChapter = $chapters[$chapterIndex];
        $lessons = $currentChapter->lessons->sortBy('id')->values();

        if ($lessons->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Chapter has no lessons'], 400);
        }

        $lessonIndex = $courseUser->current_lesson_index ?? 0;
        if (!isset($lessons[$lessonIndex])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid lesson index'], 400);
        }

        $currentLesson = $lessons[$lessonIndex];
        $sections = $currentLesson->sections->sortBy('id')->values();

        return response()->json([
            'status' => 'success',
            'course_id' => $course->id,
            'current_lesson' => [
                'id' => $currentLesson->id,
                'title' => $currentLesson->title,
                'sections' => $sections->map(function ($section) {
                    return [
                        'id' => $section->id,
                        'title' => $section->title,
                        'contents' => $section->contents
                    ];
                }),
            ],
        ]);
    }

    public function restartCourse(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['status' => 'expired']);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['status' => 'invalid']);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }

        $request->validate([
            'id' => 'required|exists:courses,id',
        ]);

        $courseId = $request->id;
        $courseUser = CourseUser::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();
        $courseUser->update([
            'completed_at' => null,
            'current_chapter_index' => 0,
            'current_lesson_index' => 0,
            'pending_exam_chapter_id' => null,
            'chapter_exam_score' => null,
            'final_exam_score' => null,
            'last_failed_final_exam_at' => null,
            'is_restarted' => true,
        ]);
        $courseId = $request->id;
        $course = Course::with(['chapters.lessons.sections'])->find($courseId);
        // چک اشتراک ویژه
        if ($course->is_pro && $user->subscription_type !== 'pro') {
            return response()->json([
                'status' => 'forbidden',
                'message' => 'این دوره فقط برای اعضای ویژه قابل دسترسی است'
            ], 403);
        }
        $courseUser = CourseUser::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $courseId],
            ['enrolled_at' => now()]
        );
        // اگر آزمون فصل در حالت pending است
        if ($courseUser->pending_exam_chapter_id) {
            $chapter = Chapter::find($courseUser->pending_exam_chapter_id);
            if ($chapter) {
                $existingQuestions = DB::table('questions')->where('reference_id', $chapter->id)->get();

                if ($existingQuestions->isEmpty()) {
                    foreach ($chapter->lessons as $lesson) {
                        DB::table('questions')->insert([
                            'reference_id' => $chapter->id,
                            'question' => 'سؤال مربوط به درس: ' . $lesson->title,
                            'option1' => 'گزینهٔ ۱',
                            'option2' => 'گزینهٔ ۲',
                            'option3' => 'گزینهٔ ۳',
                            'option4' => 'گزینهٔ ۴',
                            'correct_option' => 'option1',
                        ]);
                    }
                    $existingQuestions = DB::table('questions')->where('reference_id', $chapter->id)->get();
                }

                $questionsForClient = $existingQuestions->map(function ($q) {
                    return [
                        'id' => $q->id,
                        'question' => $q->question,
                        'options' => [
                            'option1' => $q->option1,
                            'option2' => $q->option2,
                            'option3' => $q->option3,
                            'option4' => $q->option4,
                        ],
                    ];
                });

                return response()->json([
                    'status' => 'exam_pending',
                    'type' => 'chapter_exam',
                    'chapter_id' => $chapter->id,
                    'chapter_title' => $chapter->title,
                    'questions' => $questionsForClient,
                    'message' => 'آزمون فصل آماده است — لطفاً پاسخ‌ها را ارسال کنید',
                ]);
            }
        }
        // بقیه منطق قبلی برای شروع درس
        $chapters = $course->chapters->sortBy('id')->values();
        if ($chapters->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Course has no chapters'], 400);
        }
        $chapterIndex = $courseUser->current_chapter_index ?? 0;
        if (!isset($chapters[$chapterIndex])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid chapter index'], 400);
        }
        $currentChapter = $chapters[$chapterIndex];
        $lessons = $currentChapter->lessons->sortBy('id')->values();
        if ($lessons->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Chapter has no lessons'], 400);
        }
        $lessonIndex = $courseUser->current_lesson_index ?? 0;
        if (!isset($lessons[$lessonIndex])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid lesson index'], 400);
        }
        $currentLesson = $lessons[$lessonIndex];
        $sections = $currentLesson->sections->sortBy('id')->values();
        return response()->json([
            'status' => 'success',
            'course_id' => $course->id,
            'current_lesson' => [
                'id' => $currentLesson->id,
                'title' => $currentLesson->title,
                'sections' => $sections->map(function ($section) {
                    return [
                        'id' => $section->id,
                        'title' => $section->title,
                        'contents' => $section->contents
                    ];
                }),
            ],
        ]);
    }
  
    // --------- 1) تغییر در finish_lesson (فقط بخش‌های مرتبط) ---------
    public function finish_lesson(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['status' => 'expired']);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['status' => 'invalid']);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }

        $request->validate([
            'id' => 'required|exists:lessons,id',
        ]);

        $lessonId = $request->id;
        $lesson = Lesson::find($lessonId);
        $chapter = $lesson->chapter;
        $courseId = $chapter->course_id;

        $courseUser = CourseUser::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();

        $chapters = Chapter::where('course_id', $courseId)
            ->orderBy('id')
            ->with(['lessons' => function ($q) {
                $q->orderBy('id');
            }])->get()->values();

        $chapterIndex = $courseUser->current_chapter_index ?? 0;
        $lessonIndex = $courseUser->current_lesson_index ?? 0;

        if (!isset($chapters[$chapterIndex])) {
            return response()->json(['status' => 'error', 'message' => 'فصل یافت نشد'], 400);
        }

        $lessons = $chapters[$chapterIndex]->lessons->values();
        if (!isset($lessons[$lessonIndex])) {
            return response()->json(['status' => 'error', 'message' => 'درس یافت نشد'], 400);
        }

        // اگر درس بعدی در همین فصل وجود دارد
        if (isset($lessons[$lessonIndex + 1])) {
            $courseUser->update([
                'current_lesson_index' => $lessonIndex + 1,
            ]);
        }
        // اگر درس بعدی نیست ولی فصل بعدی هست → آزمون فصل فعلی
        else if (isset($chapters[$chapterIndex + 1])) {

            // ذخیره وضعیت pending exam
            $courseUser->update([
                'pending_exam_chapter_id' => $chapters[$chapterIndex]->id,
            ]);


            $finishedChapter = $chapters[$chapterIndex];
            $existingQuestions = DB::table('questions')->where('reference_id', $finishedChapter->id)->get();
            if (!$existingQuestions->isEmpty()) {
                $questionsForClient = $existingQuestions->map(function ($q) {
                    return [
                        'id' => $q->id,
                        'question' => $q->question,
                        'options' => [
                            'option1' => $q->option1,
                            'option2' => $q->option2,
                            'option3' => $q->option3,
                            'option4' => $q->option4,
                        ],
                    ];
                });
                return response()->json([
                    'status' => 'exam_pending',
                    'type' => 'chapter_exam',
                    'chapter_id' => $finishedChapter->id,
                    'chapter_title' => $finishedChapter->title,
                    'questions' => $questionsForClient,
                    'message' => 'آزمون فصل آماده است — لطفاً پاسخ‌ها را ارسال کنید',
                ]);
            } else {
                $courseUser->update([
                    'pending_exam_chapter_id' => null,
                ]);
                $path = $chapters->map(function ($chapter, $chIndex) use ($chapterIndex, $lessonIndex) {
                    return [
                        'id' => $chapter->id,
                        'title' => $chapter->title,
                        'lessons' => $chapter->lessons->map(function ($lesson, $leIndex) use ($chIndex, $chapterIndex, $lessonIndex) {
                            $isPassed = $chIndex < $chapterIndex || ($chIndex === $chapterIndex && $leIndex < $lessonIndex);
                            return [
                                'id' => $lesson->id,
                                'title' => $lesson->title,
                                'passed' => $isPassed,
                            ];
                        }),
                    ];
                });

                return response()->json([
                    'status' => 'success',
                    'type' => 'next',
                    'path' => $path,
                    'message' => 'درس با موفقیت به پایان رسید و به مرحله بعد منتقل شدید',
                ]);
            }
        }
        // پایان دوره → بدون آزمون نهایی
        else {
            $finishedChapter = $chapters[$chapterIndex];
            $existingQuestions = DB::table('questions')->where('reference_id', $finishedChapter->id)->get();

            if ($existingQuestions->isEmpty()) {
                $courseUser->update([
                    'completed_at' => now(),
                    'pending_exam_chapter_id' => null,
                ]);
                return response()->json([
                    'status' => 'completed',
                    'type' => 'end',
                    'message' => 'دوره با موفقیت به پایان رسید',
                ]);
            } else {
                $finishedChapter = $chapters[$chapterIndex];
                $existingQuestions = DB::table('questions')->where('reference_id', $finishedChapter->id)->get();
                $questionsForClient = $existingQuestions->map(function ($q) {
                    return [
                        'id' => $q->id,
                        'question' => $q->question,
                        'options' => [
                            'option1' => $q->option1,
                            'option2' => $q->option2,
                            'option3' => $q->option3,
                            'option4' => $q->option4,
                        ],
                    ];
                });
                return response()->json([
                    'status' => 'exam_pending',
                    'type' => 'chapter_exam',
                    'chapter_id' => $finishedChapter->id,
                    'chapter_title' => $finishedChapter->title,
                    'questions' => $questionsForClient,
                    'message' => 'آزمون فصل آماده است — لطفاً پاسخ‌ها را ارسال کنید',
                ]);
            }
        }

        // بروزرسانی مسیر
        $courseUser = CourseUser::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();

        $chapterIndex = $courseUser->current_chapter_index ?? 0;
        $lessonIndex = $courseUser->current_lesson_index ?? 0;

        $path = $chapters->map(function ($chapter, $chIndex) use ($chapterIndex, $lessonIndex) {
            return [
                'id' => $chapter->id,
                'title' => $chapter->title,
                'lessons' => $chapter->lessons->map(function ($lesson, $leIndex) use ($chIndex, $chapterIndex, $lessonIndex) {
                    $isPassed = $chIndex < $chapterIndex || ($chIndex === $chapterIndex && $leIndex < $lessonIndex);
                    return [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'passed' => $isPassed,
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => 'success',
            'type' => 'next',
            'path' => $path,
            'message' => 'درس با موفقیت به پایان رسید و به مرحله بعد منتقل شدید',
        ]);
    }
    
    
    
    
    // --------- 2) متد جدید: submit_exam (اضافه کن به کنترلر همانجا که finish_lesson هست) ---------
    public function submit_exam(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['status' => 'expired']);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['status' => 'invalid']);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }

        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'chapter_id' => 'required|exists:chapters,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer|distinct',
            'answers.*.selected_option' => 'required|string',
        ]);

        $courseId = $request->course_id;
        $chapterId = $request->chapter_id;
        $answers = $request->answers;

        $courseUser = CourseUser::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();

        $questions = DB::table('questions')->where('reference_id', $chapterId)->get()->keyBy('id');

        if ($questions->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'سؤالی برای این فصل پیدا نشد'], 400);
        }

        $total = $questions->count();
        $correct = 0;
        foreach ($answers as $ans) {
            $q = $questions->get($ans['question_id']);
            if (!$q) continue;
            if ($q->correct_option === $ans['selected_option']) {
                $correct++;
            }
        }

        $scorePercent = $total > 0 ? round(($correct / $total) * 100, 2) : 0;


        $chapters = Chapter::where('course_id', $courseId)->get();
        if (count($chapters) > $courseUser->current_chapter_index + 1) {
            $cci = $courseUser->current_chapter_index + 1;
            $status = 'passed';
            $endti = null;
        } else {
            $endti = now();
            $status = 'completed';
            $cci = 0;
        }
        
            // فقط آزمون فصل
            $courseUser->update([
            'completed_at' => $endti,
            'chapter_exam_score' => $scorePercent,
            'pending_exam_chapter_id' => null,
            'current_chapter_index' => $cci,
            'current_lesson_index' => 0,
        ]);

        return response()->json([
            'status' => $status,
            'score' => $scorePercent,
            'message' => 'آزمون فصل با موفقیت گذرانده شد و به فصل بعد منتقل شدید',
        ]);
    }

public function get_progress_data(Request $request)
{
    try {
        $user = JWTAuth::parseToken()->authenticate();
    } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        return response()->json(['status' => 'expired']);
    } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        return response()->json(['status' => 'invalid']);
    } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
    }

    // دریافت تمام دوره‌های کاربر
    $courses = Course::with(['chapters.lessons'])->get();

    $activeCourses = [];
    $completedCourses = [];

    foreach ($courses as $course) {
        $courseUser = CourseUser::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$courseUser) {
            continue;
        }

        // بررسی وضعیت دوره
        $isCompleted = $courseUser->completed_at !== null;
        $isStarted = $courseUser->enrolled_at !== null;

        // اگر دوره تمام شده
        if ($isCompleted) {
            $completedCourses[] = [
                'id' => $course->id,
                'title' => $course->title,
                'imgurl' => $course->imgurl,
                'grade' => $course->grade,
                'is_pro' => $course->is_pro,
                'completed_at' => $courseUser->completed_at,
                'final_exam_score' => $courseUser->final_exam_score ?? null,
                'chapter_exam_scores' => $courseUser->chapter_exam_score ?? null,
                'progress' => 100, // دوره تمام شده
            ];
        }
        // اگر دوره شروع شده اما تمام نشده
        elseif ($isStarted) {
            $lastChapterIndex = $courseUser->current_chapter_index ?? 0;
            $lastLessonIndex = $courseUser->current_lesson_index ?? 0;

            $chapters = Chapter::where('course_id', $course->id)
                ->orderBy('id')
                ->with(['lessons' => function ($q) {
                    $q->orderBy('id');
                }])->get()->values();

            $totalLessons = 0;
            $completedLessons = 0;

            foreach ($chapters as $chIndex => $chapter) {
                foreach ($chapter->lessons as $leIndex => $lesson) {
                    $totalLessons++;

                    // اگر فصل قبلی گذشته شده یا فصل فعلی و درس قبل یا فعلی گذشته شده
                    if ($chIndex < $lastChapterIndex || ($chIndex === $lastChapterIndex && $leIndex < $lastLessonIndex)) {
                        $completedLessons++;
                    }
                }
            }

            $progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0;

            $activeCourses[] = [
                'id' => $course->id,
                'title' => $course->title,
                'imgurl' => $course->imgurl,
                'grade' => $course->grade,
                'is_pro' => $course->is_pro,
                'progress' => $progress,
                'current_chapter_index' => $lastChapterIndex,
                'current_lesson_index' => $lastLessonIndex,
                'completed_at' => null,
                'final_exam_score' => null,
                'chapter_exam_scores' => null,
            ];
        }
    }

    // مرتب‌سازی بر اساس تاریخ شروع
    usort($activeCourses, function ($a, $b) {
        return $a['progress'] < $b['progress'] ? 1 : -1;
    });

    // مرتب‌سازی بر اساس تاریخ تمام شدن
    usort($completedCourses, function ($a, $b) {
        return $a['completed_at'] < $b['completed_at'] ? 1 : -1;
    });

    return response()->json([
        'status' => 'success',
        'data' => [
            'active_courses' => $activeCourses,
            'completed_courses' => $completedCourses,
        ]
    ]);
}


   public function courses(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['status' => 'expired']);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['status' => 'invalid']);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }

        $allCourses = Course::select('id', 'title','imgurl' , 'description', 'is_pro', 'grade')->get();

        $groupedCourses = [];

        foreach ($allCourses as $course) {
            $grade = $course->grade;
            $isPaid = $course->is_pro;

            $courseData = [
                'id' => $course->id,
                'title' => $course->title,
                'imgurl' => $course->imgurl,
                'description' => $course->description,
                'isPaid' => $isPaid
            ];

            if (!isset($groupedCourses[$grade])) {
                $groupedCourses[$grade] = [];
            }

            $groupedCourses[$grade][] = $courseData;
        }

        // مرتب‌سازی کلیدهای گرید
        ksort($groupedCourses);

        return response()->json([
            'status' => 'success',
            'data' => $groupedCourses
        ]);
    }











  public function show_photo(Request $request, $id)
    {
        header("Content-Type: image/jpg");
        $phot = DB::table('photos')->where('id', $id)->get();
        if (count($phot)) {
            // $url = './storage/app/private/' . $phot[0]->path;
            // return readfile($url);
            $path = storage_path('app/private/' . $phot[0]->path);
            if (file_exists($path)) {
                return response()->file($path);
            }
        } else { return 'no access';}
    }




    public function retoken(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required', // دریافت refresh_token از سمت فرانت
        ]);

        // جستجوی رفرش توکن در دیتابیس
        $userToken = UserToken::where('refresh_token', $request->refresh_token)->where('user_agent', $request->header('User-Agent'))->first();

        // بررسی اعتبار رفرش توکن
        if (!$userToken) {
            return response()->json(['status' => 'expired']);
        }

        $us = User::find($userToken->user_id);

        // تولید توکن جدید
        $newAccessToken = JWTAuth::fromUser($us);

        // به‌روز‌رسانی زمان آخرین ورود
        $userToken->last_online_at = now();
        $userToken->save();

        return response()->json([
            'status' => 'succes',
            'token' => $newAccessToken,
        ]);
    }
    public function edit_setings(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],

        ]);
        $user = JWTAuth::parseToken()->authenticate();
        $phone = $request->phone;
        if ($phone && DB::table('users')->where('phone', $phone)->where('id', '!=', $user->id)->exists()) {
            return response()->json(['status' => 'errorph']);
        } else {
            DB::table('users')->where('id', $user->id)->update(['phone' => $request->phone]);
        }
        DB::table('users')->where('id', $user->id)->update(['name' => $request->name]);
        return response()->json(['status' => 'succes']);
    }

    public function verify_phone(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $error = '';
        $status = "nothing";
        $code = rand(10000, 99999);
        $respons = send_ksms($user->phone, $code, 'code');
        $sms_res = json_decode($respons);

        if ($sms_res->return->status == 200) {
            try {
                reg_req::updateOrCreate(
                    ['phone' => $request->phone],
                    ['code' => $code]
                );
                $status = "success";
            } catch (\Throwable $e) {
                $error = $e->getMessage();
                $status = "failed1";
            }
        } else {
            $error = $sms_res;
            $status = "failed";
        }

        return response()->json([
            'status' => $status,
            'res'  => $sms_res->return->status,
            'error' => $error,
        ]);
    }
    public function verify_phone_confrim(Request $request)
    {
        $request->validate([
            'code' => ['required', 'integer'],
        ]);
        $user = JWTAuth::parseToken()->authenticate();
        $status = 'wrong';
        $key = '';
        $code_req = DB::table('reg_req')->where('phone', $user->phone)->where('code', $request->code)->get();

        if (count($code_req)) {
            DB::table('reg_req')->where('phone', $user->phone)->where('code', $request->code)->update([
                'status' => 1,
            ]);
            DB::table('users')->where('id', $user->id)->update([
                'phone_verify' => 1,
            ]);
            $status = 'success';
        }
        return response()->json(['status' => $status, 'key' => $key]);
    }
}
