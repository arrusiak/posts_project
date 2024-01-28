<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Http\Requests\PostTagRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\PostTag;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $posts = Post::all();
        return PostResource::collection($posts);
    }

    public function show(Post $post): PostResource
    {
        return new PostResource($post);
    }

    public function store(PostRequest $request): PostResource
    {
        $post = Post::create($request->validated());

        $this->createPostTags($post, $request->tags);

        return new PostResource($post->fresh());
    }

    public function update(PostRequest $request, Post $post): PostResource
    {
        $post->update($request->validated());

        return new PostResource($post);
    }

    public function destroy(Post $post): JsonResponse
    {
        $post->delete();

        return response()->json(['message' => 'Record deleted successfully']);
    }

    /**
     * @throws \Exception
     */
    public function addPostTags(PostTagRequest $request, Post $post)
    {
        if ($post->tags()->whereIn('tag_id', $request->tags)->exists()) {
            throw new \Exception('Tag is already assigned to post');
        }

        $this->createPostTags($post, $request->tags);

        return response()->json(['message' => 'Tags assigned successfully']);
    }

    public function deletePostTags(PostTagRequest $request, Post $post)
    {
        if (!$post->tags()->whereIn('tag_id', $request->tags)->exists()) {
            throw new \Exception('Tag is not assigned to post');
        }

        PostTag::where('post_id', $post->id)->whereIn('tag_id', $request->tags)->delete();

        return response()->json(['message' => 'Tags deleted successfully']);
    }

    private function createPostTags(Post $post, array $tagIdList): void
    {
        foreach ($tagIdList as $tagId) {
            $tag = Tag::findOrFail($tagId);
            $this->createPostTag($post, $tag);
        }
    }

    private function createPostTag(Post $post, Tag $tag): void
    {
        $postTag = new PostTag();
        $postTag->post()->associate($post);
        $postTag->tag()->associate($tag);
        $postTag->save();
    }
}
