<?php

use App\Models\User;
use App\Models\EvaluationL3Assignment;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Helper to simulate a request
function testRubricSubmission($assignmentId, $expertId) {
    $assignment = EvaluationL3Assignment::find($assignmentId);
    if (!$assignment) {
        echo "Assignment not found!\n";
        return;
    }

    echo "Testing submission for Assignment ID: $assignmentId\n";
    
    // Simulate the data sent by the form
    $data = [
        'scores' => [
            'Ketepatan' => 5,
            'Kreativitas' => 4,
            'Dokumentasi' => 5
        ],
        'status' => 'approved',
        'expert_notes' => 'Great work on the implementation!'
    ];

    // Log in the expert
    $expert = User::find($expertId);
    auth()->login($expert);

    // Call the controller method directly or simulate the logic
    $controller = new \App\Http\Controllers\RubricController();
    $request = \Illuminate\Http\Request::create("/expert/rubric/$assignmentId", 'POST', $data);
    
    try {
        $response = $controller->store($request, $assignment);
        echo "Submission successful! Response status: " . $response->getStatusCode() . "\n";
        
        // Verify changes
        $assignment->refresh();
        echo "New Assignment Status: " . $assignment->status . "\n";
        echo "Rubric Score: " . ($assignment->rubricScore->total_score ?? 'N/A') . "\n";
        echo "Enrollment Status: " . $assignment->enrollment->status . "\n";
        
        if ($assignment->status === 'approved' && $assignment->enrollment->status === 'completed') {
            echo "VERIFICATION SUCCESS: Logic works as expected.\n";
        } else {
            echo "VERIFICATION FAILED: Status mismatch.\n";
        }
    } catch (\Exception $e) {
        echo "Error during submission: " . $e->getMessage() . "\n";
    }
}

// Find a pending assignment to test
$assignment = EvaluationL3Assignment::where('status', 'pending')->first();
$expert = User::where('role', 'admin')->orWhere('role', 'expert')->first();

if ($assignment && $expert) {
    testRubricSubmission($assignment->id, $expert->id);
} else {
    echo "No pending assignments or experts found to test.\n";
}
