<?php

namespace App\Models;

use App\Models\Course;
use App\Models\CourseAnswer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseQuestion extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function answers()
    {
        return $this->hasMany(CourseAnswer::class, 'course_question_id', 'id');
    }
}
