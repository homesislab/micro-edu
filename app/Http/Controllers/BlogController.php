<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::where('expert_id', auth()->id())->latest()->get();
        return Inertia::render('Expert/BlogList', [
            'blogs' => $blogs
        ]);
    }

    public function create()
    {
        return Inertia::render('Expert/BlogEditor');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string',
            'status' => 'required|in:draft,published',
        ]);

        Blog::create([
            'expert_id' => auth()->id(),
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->content,
            'category' => $request->category,
            'status' => $request->status,
            'published_at' => $request->status === 'published' ? now() : null,
        ]);

        return redirect()->route('expert.blogs.index');
    }

    public function edit(Blog $blog)
    {
        if ($blog->expert_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Expert/BlogEditor', [
            'blog' => $blog
        ]);
    }

    public function update(Request $request, Blog $blog)
    {
        if ($blog->expert_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string',
            'status' => 'required|in:draft,published',
        ]);

        $blog->update([
            'title' => $request->title,
            'content' => $request->content,
            'category' => $request->category,
            'status' => $request->status,
            'published_at' => ($request->status === 'published' && !$blog->published_at) ? now() : $blog->published_at,
        ]);

        return redirect()->route('expert.blogs.index');
    }

    public function destroy(Blog $blog)
    {
        if ($blog->expert_id !== auth()->id()) {
            abort(403);
        }

        $blog->delete();
        return back();
    }
}
