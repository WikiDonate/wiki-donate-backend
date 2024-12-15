<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\NotificationResource;
use App\Models\Notification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    public function show(Request $request)
    {
        try {
            $notifications = Notification::where('user_id', $request->user()->id)->first();
            if (! $notifications) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification data is not found',
                    'data' => [],
                ], Response::HTTP_OK);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification data found successfully',
                'data' => new NotificationResource($notifications),
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exception Error',
                'errors' => [$e->getMessage()],
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request)
    {
        try {
            // Validate the input fields
            $validator = Validator::make($request->all(), [
                'editTalkPage' => 'required|in:0,1',
                'editUserPage' => 'required|in:0,1',
                'pageReview' => 'required|in:0,1',
                'emailFromOther' => 'required|in:0,1',
                'successfulMention' => 'required|in:0,1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()->all(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $notifications = Notification::updateOrCreate(
                ['user_id' => $request->user()->id],
                [
                    'edit_talk_page' => $request->editTalkPage,
                    'edit_user_page' => $request->editUserPage,
                    'page_review' => $request->pageReview,
                    'email_from_other' => $request->emailFromOther,
                    'successful_mention' => $request->successfulMention,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Notification settings update successfully',
                'data' => new NotificationResource($notifications),
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exception Error',
                'errors' => [$e->getMessage()],
            ], Response::HTTP_EXPECTATION_FAILED);
        }
    }
}
