<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Thread;
use App\Models\Comment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CommunityFeedController extends Controller
{
    /**
     * Display the community feed for a course.
     */
    public function index(Course $course)
    {
        $threads = $course->threads()
            ->with(['user', 'comments.user'])
            ->withCount('comments')
            ->latest()
            ->paginate(15);

        return Inertia::render('Social/CommunityFeed', [
            'course' => $course,
            'threads' => $threads,
        ]);
    }

    /**
     * Store a new discussion thread.
     */
    public function storeThread(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $course->threads()->create([
            'user_id' => auth()->id(),
            'academy_id' => $course->academy_id,
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return back()->with('success', 'Discussion started!');
    }

    /**
     * Store a comment or reply.
     */
    public function storeComment(Request $request, Thread $thread)
    {
        $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $thread->comments()->create([
            'user_id' => auth()->id(),
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);

        return back()->with('success', 'Reply posted!');
    }
}
