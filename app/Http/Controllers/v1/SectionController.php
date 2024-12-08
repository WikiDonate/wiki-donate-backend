<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\SectionResource;
use App\Models\Section;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class SectionController extends Controller
{
    public function show($uuid)
    {
        try {
            $section = Section::where('uuid', $uuid)->first();
            if (empty($section)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Section is not found',
                    'errors' => ['Section is not found'],
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'message' => 'Section found successfully',
                'data' => new SectionResource($section),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exceptions error',
                'errors' => [$e->getMessage()],
            ], Response::HTTP_EXPECTATION_FAILED);
        }

    }

    public function update(Request $request, $uuid)
    {
        $validator = Validator::make($request->all(), [
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
            // Parse HTML sections
            $htmlSections = parseHtmlSection($request->content);
            if (empty($htmlSections)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Error',
                    'errors' => ['No sections found'],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Check if the section exists
            $section = Section::where('uuid', $uuid)->first();
            if (empty($section)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Section is not found',
                    'errors' => ['Section is not found'],
                ], Response::HTTP_NOT_FOUND);
            }

            // Create a new section version
            $latestVersionNumber = $section->versions()->max('version_number') ?? 0;
            $htmlSection = collect($htmlSections)->first();

            $section->versions()->updateOrCreate(
                [
                    'title' => $htmlSection['title'],
                    'content' => $htmlSection['content'],

                ],
                [
                    'version_number' => $latestVersionNumber + 1,
                    'updated_by' => auth()->id(),
                ]
            );

            // Update the section content
            $section->update([
                'title' => $htmlSection['title'],
                'content' => $htmlSection['content'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Section updated successfully',
                'data' => new SectionResource($section),
            ], Response::HTTP_OK);

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
