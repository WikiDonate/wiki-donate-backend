<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ArticleResource;
use App\Models\Article;
use DOMDocument;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends Controller
{
    private function _parseHtmlSection($htmlText)
    {
        if (empty($htmlText)) {
            return [];
        }

        $dom = new DOMDocument;
        @$dom->loadHTML($htmlText, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        // Get all <h2> elements
        $headings = $dom->getElementsByTagName('h2');
        $data = [];

        foreach ($headings as $heading) {
            // Extract the title text, including any child elements like <u>
            $title = trim($dom->saveHTML($heading));
            // Get the next sibling of the <h2> tag
            $nextElement = $heading->nextSibling;
            $content = '';

            while ($nextElement) {
                if ($nextElement->nodeName === 'h2') {
                    // Stop when the next <h2> is encountered
                    break;
                }

                if ($nextElement->nodeType === XML_ELEMENT_NODE) {
                    // Append the content if it's an HTML element
                    $content .= $dom->saveHTML($nextElement);
                }

                $nextElement = $nextElement->nextSibling;
            }

            // Clean up any extra <br> tags or empty content
            $content = preg_replace('/<p>\s*<br>\s*<\/p>/', '', trim($content));

            // Add the title and content to the result
            $data[] = [
                'title' => $title,
                'content' => $content,
            ];
        }

        return $data;
    }

    private function _extractTitlesAndGenerateSlug($htmlText)
    {
        if (empty($htmlText)) {
            return [];
        }

        // Parse the HTML and extract titles
        $sections = $this->_parseHtmlSection($htmlText);

        // Generate a slug from the extracted titles
        $slug = Str::slug(
            implode(
                '-',
                array_slice(
                    array_map(function ($section) {
                        // Remove HTML tags from the title
                        return strip_tags($section['title']);
                    }, $sections),
                    0,
                    min(5, count($sections)) // Limit to first 5 titles
                )
            )
        );

        return $slug;
    }

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
            $sections = $this->_parseHtmlSection($request->content);
            if (empty($sections)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error',
                    'errors' => ['No sections found'],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Create article
            $article = Article::create([
                'title' => $request->title,
                'slug' => $this->_extractTitlesAndGenerateSlug($request->content),
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
}
