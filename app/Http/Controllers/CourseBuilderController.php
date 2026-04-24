<?php

namespace App\Http\Controllers;

use App\Models\CourseRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseBuilderController extends Controller
{
    public function show(CourseRecord $courseRecord)
    {
        // Eager load all nested relationships
        $courseRecord->load(['instructors', 'modules.curriculumItems']);

        return Inertia::render('CourseBuilder/Show', [
            'courseRecord' => $courseRecord
        ]);
    }
}
