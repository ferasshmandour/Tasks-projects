<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $posts = Post::with('user')->latest()->get();

        return response()->json($posts);
    }

    /**
     * Store a newly created resource.
     * Uses currentUserId() helper to assign user_id automatically.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $post = Post::create([
            ...$validated,
            'user_id' => currentUserId(),
        ]);

        return response()->json($post->load('user'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post): JsonResponse
    {
        return response()->json($post->load('user'));
    }

    /**
     * Update the specified resource.
     * Uses PostPolicy via Gate::allows - user can update only if they own it.
     */
    public function update(Request $request, Post $post): JsonResponse
    {
        if (! Gate::allows('update', $post)) {
            throw new AuthorizationException('You do not own this post.');
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
        ]);

        $post->update($validated);

        return response()->json($post->fresh()->load('user'));
    }

    /**
     * Remove the specified resource.
     * Uses PostPolicy via Gate::authorize - user can delete only if they own it.
     */
    public function destroy(Post $post): JsonResponse
    {
        Gate::authorize('delete', $post);

        $post->delete();

        return response()->json(null, 204);
    }
}
