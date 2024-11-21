<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\NotificationResource;
use App\Models\Notification;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

            // Create a notification setting for the user
            $notification = Notification::create([
                'user_id' => $request->user()->id,
                'edit_talk_page' => $request->editTalkPage,
                'edit_user_page' => $request->editUserPage,
                'page_review' => $request->pageReview,
                'email_from_other' => $request->emailFromOther,
                'successful_mention' => $request->successfulMention,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification settings saved successfully',
                'data' => new NotificationResource($notification),
            ], Response::HTTP_CREATED);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exception Error',
                'errors' => [$e->getMessage()],
            ], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        try {
            $notification = Notification::where('user_id', $request->user()->id)->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Notification data found successfully',
                'data' => new NotificationResource($notification),
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exception Error',
                'errors' => [$e->getMessage()],
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Notification $notification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {

            // dd($request->all());
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

            $notification = Notification::where('user_id', $request->user()->id)->firstOrFail();

            // dd($notification);

            $notification->update([
                'edit_talk_page' => $request->editTalkPage,
                'edit_user_page' => $request->editUserPage,
                'page_review' => $request->pageReview,
                'email_from_other' => $request->emailFromOther,
                'successful_mention' => $request->successfulMention,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification settings update successfully',
                'data' => new NotificationResource($notification),
            ], Response::HTTP_CREATED);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exception Error',
                'errors' => [$e->getMessage()],
            ], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $notification)
    {
        //
    }
}
