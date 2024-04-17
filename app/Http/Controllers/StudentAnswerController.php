<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseQuestion;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;

class StudentAnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Course $course, $question)
    {
        $questionDetails = CourseQuestion::where('id', $question)->first();

        $validated = $request->validate([
            'answer_id' => 'required|exists:course_answers,id',
        ]);

        DB::beginTransaction();

        try {
            $selectedAnswer = CourseAnswer::find($validated['answer_id']);

            if ($selectedAnswer->course_question_id != $question) {
                $error = ValidationException::withMessage([
                    'system_error' => ['Jawaban tidak tersedia pada pertanyaan'],
                ]);

                throw $error;

            }

            $existingAnswer = StudentAnswer::where('user_id', Auth::id())->where('course_question_id', $question)->first();

            if ($existingAnswer) {
                $error = ValidationException::withMessage([
                    'system_error' => ['Kamu telah menjawab pertanyaan ini sebelumnya'],
                ]);

                throw $error;
            }

            $answerValue = $selectedAnswer->is_correct ? 'correct' : 'wrong';

            StudentAnswer::create([
                'user_id' => Auth::id(),
                'course_question_id' => $question,
                'answer' => $answerValue,
            ]);

            DB::commit();

            $nextQuestion = CourseQuestion::where('course_id', $course->id)->where('id','>',$question)->orderBy('id', 'ASC')->first();

            if($nextQuestion){
                return redirect()->route('dashboard.learning.course', ['course'=>$course->id, 'question'=>$nextQuestion->id]);
            } else{
                return redirect()->route('dashboard.learning.finished.course', $course->id);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $error = ValidationException::withMessage([
                'system_error' => ['System Error!' . $e->getMessage()],
            ]);

            throw $error;
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(StudentAnswer $studentAnswer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StudentAnswer $studentAnswer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StudentAnswer $studentAnswer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StudentAnswer $studentAnswer)
    {
        //
    }
}
