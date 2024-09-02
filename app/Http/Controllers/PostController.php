<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Like;
use App\Models\Follower;
use Yajra\DataTables\Facades\DataTables;
use Auth;
class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
       
        if ($request->ajax()) {
            $posts = Post::with('user', 'likes')->select(['id', 'title', 'description', 'user_id', 'created_at'])->latest();
            return DataTables::of($posts)
                ->addColumn('user.name', function($post) {
                    return $post->user->name;
                })
                ->addColumn('likes_count', function($post) {
                    return $post->likes()->count();
                })
                ->addColumn('is_liked', function($post) {
                    return $post->isLikedBy(Auth::user()) ? 'true' : 'false';
                })
                ->addColumn('action', function($post){
                    $likeButton = $post->isLikedBy(Auth::user()) 
                        ? '<button class="btn btn-danger btn-sm unlike-button" data-post-id="' . $post->id . '">Unlike</button>'
                        : '<button class="btn btn-primary btn-sm like-button" data-post-id="' . $post->id . '">Like</button>';

                    return $likeButton;
                })
                ->make(true);
        }
        if(Auth::check())
        {
            return view('posts.index');
        }
        
        return redirect()->route('login')
            ->withErrors([
            'email' => 'Please login to access the dashboard.',
        ])->onlyInput('email');
      
    }

        public function like($id)
    {
        $post = Post::findOrFail($id);

        if ($post->isLikedBy(Auth::user())) {
            return response()->json(['message' => 'You already liked this post.'], 400);
        }

        Like::create([
            'user_id' => Auth::id(),
            'post_id' => $post->id,
        ]);

        return response()->json(['message' => 'Post liked successfully.']);
    }

    public function unlike($id)
    {
        $post = Post::findOrFail($id);

        $like = $post->likes()->where('user_id', Auth::id())->first();

        if (!$like) {
            return response()->json(['message' => 'You have not liked this post.'], 400);
        }

        $like->delete();

        return response()->json(['message' => 'Post unliked successfully.']);
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
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
          
        ]);

        $post = Post::create([
            'title' => $request->title,
            'description' => $request->description,          
            'user_id' => auth()->id(), // Assuming the user is authenticated
        ]);
        if ($request->ajax()) {
            return response()->json(['status'=>true, 'message' => 'Profile updated successfully!']);
            return response()->json(['post' => $post,'status'=>true, 'message' => 'Profile updated successfully!'], 200);
        }

        return redirect()->route('posts.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
