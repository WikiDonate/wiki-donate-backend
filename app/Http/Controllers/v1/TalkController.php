<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ArticleResource;
use App\Http\Resources\v1\RevisionResource;
use App\Http\Resources\v1\TalkResource;
use App\Models\Article;
use App\Models\Talk;
use App\Models\TalkRevision;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TalkController extends Controller
{
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

            $talk = Talk::where('article_id', $article->id)->first();
            if (empty($talk)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Talk is not found',
                    'errors' => ['Talk is not found'],
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'message' => 'Talk found successfully',
                'data' => new TalkResource($talk),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exceptions error',
                'errors' => [$e->getMessage()],
            ], Response::HTTP_EXPECTATION_FAILED);
        }

    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'articleUuid' => 'required|exists:articles,uuid',
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

        try {
            // Parse HTML sections
            $sections = parseHtmlSection($request->content);
            if (empty($sections)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error',
                    'errors' => ['No sections found'],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Check duplicate slug
            $duplicateSlug = Talk::where('slug', Str::slug($request->title))->first();
            if ($duplicateSlug) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate slug found',
                    'errors' => ['Duplicate slug found'],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Create talk
            $talk = Talk::create([
                'user_id' => $request->user()->id,
                'article_id' => Article::where('uuid', $request->articleUuid)->first()->id,
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'sections' => json_encode($sections),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'The talk has been created successfully',
                'data' => new TalkResource($talk),
            ], Response::HTTP_CREATED);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exceptions error',
                'errors' => [$e->getMessage()],
            ], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function update(Request $request, $slug)
    {
        $validator = Validator::make($request->all(), [
            'uuid' => 'required|exists:talks,uuid',
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

        try {
            // Check existing talk
            $existsTalk = Talk::where('uuid', $request->uuid)->first();
            if (! $existsTalk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Talk not found',
                    'errors' => ['Talk not found'],
                ], Response::HTTP_NOT_FOUND);
            }

            $oldSections = json_decode($existsTalk->sections, true);

            // Parse HTML sections
            $sections = parseHtmlSection($request->content);
            if (empty($sections)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error',
                    'errors' => ['No sections found'],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Check if the section changed or not
            if (json_encode($oldSections) === json_encode($sections)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error',
                    'errors' => ['There is no change in article'],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Update talk
            $existsTalk->update([
                'title' => $request->title,
                'sections' => json_encode($sections),
            ]);

            $latestVersionNumber = TalkRevision::where('talk_id', $existsTalk->id)->max('version') ?? 0;

            // Create versions
            TalkRevision::create([
                'talk_id' => $existsTalk->id,
                'user_id' => $request->user()->id,
                'version' => $latestVersionNumber + 1,
                'old_content' => json_encode($oldSections),
                'new_content' => json_encode($sections),

            ]);

            return response()->json([
                'success' => true,
                'message' => 'The talk updated successfully',
                'data' => new TalkResource(Talk::find($existsTalk->id)),
            ], Response::HTTP_OK);

        } catch (Exception $e) {
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

    public function history($slug)
    {
        try {
            $talk = Talk::where('slug', $slug)->first();
            if (empty($talk)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Talk not found',
                    'errors' => ['Talk not found'],
                ], Response::HTTP_NOT_FOUND);
            }

            $versions = TalkRevision::where('talk_id', $talk->id)->orderBy('version', 'desc')->get();
            if ($versions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'History not found',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'message' => 'History found successfully',
                'data' => RevisionResource::collection($versions),
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exceptions error',
                'errors' => [$e->getMessage()],
            ], Response::HTTP_EXPECTATION_FAILED);
        }

    }
}
