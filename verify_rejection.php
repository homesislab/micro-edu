<?php

use App\Models\User;
use App\Models\EvaluationL3Assignment;
use App\Models\Enrollment;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function testRubricRejection($assignmentId, $expertId) {
    $assignment = EvaluationL3Assignment::find($assignmentId);
    if (!$assignment) {
        echo "Assignment not found!\n";
        return;
    }

    echo "Testing rejection (Request Revision) for Assignment ID: $assignmentId\n";
    
    $data = [
        'scores' => [
            'Ketepatan' => 2,
            'Kreativitas' => 2,
            'Dokumentasi' => 1
        ],
        'status' => 'rejected',
        'expert_notes' => 'Please fix the documentation and re-submit.'
    ];

    $expert = User::find($expertId);
    auth()->login($expert);

    $controller = new \App\Http\Controllers\RubricController();
    $request = \Illuminate\Http\Request::create("/expert/rubric/$assignmentId", 'POST', $data);
    
    try {
        $response = $controller->store($request, $assignment);
        echo "Rejection successful! Response status: " . $response->getStatusCode() . "\n";
        
        $assignment->refresh();
        echo "New Assignment Status: " . $assignment->status . "\n";
        echo "Rubric Score: " . ($assignment->rubricScore->total_score ?? 'N/A') . "\n";
        
        // Check if enrollment status is still l3_submitted or l1_done if we implemented rollback logic in the controller (let's check RubricController again)
        // Wait, my RubricController did NOT change enrollment status on rejection.
        // Usually, rejection means the student needs to re-submit L3.
        
        if ($assignment->status === 'rejected') {
            echo "VERIFICATION SUCCESS: Rejection logic works.\n";
        } else {
            echo "VERIFICATION FAILED: Status mismatch.\n";
        }
    } catch (\Exception $e) {
        echo "Error during rejection: " . $e->getMessage() . "\n";
    }
}

// Find another pending assignment or reset the first one
$assignment = EvaluationL3Assignment::first();
$expert = User::where('role', 'admin')->orWhere('role', 'expert')->first();

if ($assignment && $expert) {
    $assignment->update(['status' => 'pending']); // Reset to pending for test
    testRubricRejection($assignment->id, $expert->id);
} else {
    echo "No assignments or experts found to test.\n";
}
