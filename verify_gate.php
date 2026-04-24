<?php

use App\Models\Course;
use App\Models\Module;
use App\Models\CurriculumItem;

$course = Course::first();

if (!$course) {
    echo "No course found to test.\n";
    exit;
}

echo "Testing Course: " . $course->title . "\n";

// Create a test module
$module = $course->modules()->create(['title' => 'Test Module', 'order' => 1]);
echo "Created Module: " . $module->title . "\n";

// Create items
$module->curriculumItems()->create(['title' => 'Literal 1', 'type' => 'literal', 'order' => 1]);
$module->curriculumItems()->create(['title' => 'Exercise 1', 'type' => 'exercise', 'order' => 2]);
$module->curriculumItems()->create(['title' => 'Exercise 2', 'type' => 'exercise', 'order' => 3]);

echo "Total Required Exercises: " . $course->total_required_exercises . "\n";

if ($course->total_required_exercises === 2) {
    echo "SUCCESS: Accessor works correctly.\n";
} else {
    echo "FAILURE: Expected 2, got " . $course->total_required_exercises . "\n";
}

// Cleanup if needed or leave for manual check
//$module->delete();
