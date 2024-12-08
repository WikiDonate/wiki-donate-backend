<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\SectionResource;
use App\Models\Section;
use Exception;
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
}
