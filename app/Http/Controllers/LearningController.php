<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseQuestion;
use App\Models\StudentAnswer;
use Illuminate\Support\Facades\Auth;

class LearningController extends Controller
{
    //

    public function index()
    {
        $user = Auth::user();
        $myCourses = $user->courses()->with('category')->orderBy('id', 'DESC')->get();

        foreach ($myCourses as $course) {
            $totalQuestionCount = $course->questions()->count();

            $answeredQuestionCount = StudentAnswer::where('user_id', $user->id)->whereHas('question', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })->distinct()->count('course_question_id');

            if ($answeredQuestionCount < $totalQuestionCount) {
                $firstUnansweredQuestion = CourseQuestion::where('course_id', $course->id)->whereNotIn('id', function ($query) use ($user) {
                    $query->select('course_question_id')->from('student_answers')->where('user_id', $user->id);
                })->orderBy('id', 'ASC')->first();

                $course->nexQuestionId = $firstUnansweredQuestion ? $firstUnansweredQuestion->id : null;
            } else {
                $course->nexQuestionId = null;
            }
        }

        return view('student.courses.index', [
            'myCourses' => $myCourses,
        ]);
    }

    public function learning(Course $course, $question)
    {
        $user = Auth::user();

        $isEnrolled = $course->students()->where('user_id', $user->id)->exists();

        if (!$isEnrolled) {
            abort(404);
        }

        $currentQuestion = CourseQuestion::where('course_id', $course->id)->where('id', $question)->firstOrFail();

        return view('student.courses.learning', [
            'course' => $course,
            'question' => $currentQuestion,
        ]);

    }

    public function learningReport(Course $course)
    {

        $userId = Auth::id();

        $studentAnswer = StudentAnswer::with('question')->whereHas('question', function ($query) use ($course) {
            $questy->where('course_id', $course->id);
        })->where('user_id', $userId)->get();

        $totalQuestions = CourseQuestion::where('course_id', $course->id)->count();
        $correctAnswerCount = $studentAnswer->where('answer', 'correct')->count();
        $passed = $correctAnswersCount == $totalQuestions;

        return view('student.courses.learning_report', [
            'course' => $course,
            'passed' => $passed,
            'studentAnswer' => $studentAnswer,
            'totalQuestions' => $totalQuestions,
            'correctAnswerCount' => $correctAnswerCount,
        ]);
    }

    public function learningFinished(Course $course)
    {
        return view('student.courses.learning_finish', [
            'course' => $course,
        ]);

    }
}
