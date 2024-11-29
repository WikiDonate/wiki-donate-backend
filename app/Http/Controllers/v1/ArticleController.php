<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ArticleResource;
use App\Models\Article;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends Controller
{
    public function index()
    {
        try {
            $articles = Article::with('sections')->get();
            if ($articles->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No articles found',
                    'errors' => ['No articles found'],
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => ArticleResource::collection($articles),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exceptions error',
                'errors' => [$e->getMessage()],
            ], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string',
            'content' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error',
                'errors' => $validator->errors()->all(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::beginTransaction();
        try {
            $article = Article::create([
                'title' => $request->title,
                'slug' => 'test-slug',
                'content' => $request->content,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'The article has been created successfully',
                'data' => new ArticleResource($article),
            ], Response::HTTP_CREATED);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Exceptions error',
                'errors' => [$e->getMessage()],
            ], Response::HTTP_EXPECTATION_FAILED);
        }
    }
}
