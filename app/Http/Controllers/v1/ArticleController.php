<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ArticleResource;
use App\Models\Article;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends Controller
{
    public function index()
    {
        try {
            $articles = Article::get();
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

    public function show($slug)
    {
        try {
            $article = Article::where('slug', $slug)->first();
            if (empty($article)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article not found',
                    'errors' => ['Article not found'],
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'message' => 'Article found successfully',
                'data' => new ArticleResource($article),
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
            'title' => 'required|string',
            'content' => 'required|string',
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
            $sections = parseHtmlSection($request->content);
            if (empty($sections)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error',
                    'errors' => ['No sections found'],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Check duplicate slug from articles table
            $duplicateSlug = Article::where('slug', Str::slug($request->title))->first();
            if ($duplicateSlug) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate slug found',
                    'errors' => ['Duplicate slug found'],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Create article
            $article = Article::create([
                'user_id' => $request->user()->id,
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'content' => $request->content,
            ]);

            // Create sections
            foreach ($sections as $order => $section) {
                $article->sections()->create([
                    'article_id' => $article->id,
                    'title' => $section['title'],
                    'content' => $section['content'],
                    'order' => $order,
                ]);
            }

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

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error',
                'errors' => $validator->errors()->all(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Get the query parameter
        $query = $request->input('query');

        // Search for articles by title
        $articles = Article::where('title', 'LIKE', '%'.$query.'%')
            ->orderBy('title')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Articles found successfully',
            'data' => ArticleResource::collection($articles),
        ], Response::HTTP_OK);
    }

    public function getVersions($slug)
    {
        return false;
    }
}
