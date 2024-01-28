<?php

namespace App\Http\Controllers;

use App\Http\Requests\TagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::all();
        return TagResource::collection($tags);
    }

    public function show($id)
    {
        $tag = Tag::findOrFail($id);

        return new TagResource($tag);
    }

    public function store(TagRequest $request)
    {
        $tag = Tag::create([
            'name' => $request->input('name'),
        ]);

        return new TagResource($tag);
    }

    public function update(TagRequest $request, Tag $tag)
    {
        $tag->update($request->validated());

        return new TagResource($tag);
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return response()->json(['message' => 'Tag deleted successfully']);
    }
}
