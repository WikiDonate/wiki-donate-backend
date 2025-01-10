<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\UserResource;
use App\Mail\CongratulationMail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|unique:users',
                'password' => 'required',
                'confirmPassword' => 'required|same:password',
                'email' => 'nullable|email|unique:users',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error',
                    'errors' => $validator->errors()->all(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Create user
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email ?? null,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole('Editor');

            // Send email
            if (!empty($user->email)) {
                Mail::to($user->email)->queue(new CongratulationMail($user));
            }

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => new UserResource($user),
            ], Response::HTTP_CREATED);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exceptions error',
                'errors' => [$e->getMessage()],
            ], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            // Validate input fields
            $validator = Validator::make($request->all(), [
                'newPassword' => 'required|min:8',
                'confirmPassword' => 'required|same:newPassword', // Ensure confirm password matches new password
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error',
                    'errors' => $validator->errors()->all(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Retrieve the authenticated user from the token
            $user = $request->user();

            // Check if the user was successfully retrieved
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Update to new password
            $user->password = Hash::make($request->newPassword);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Change password successfully',
                'data' => new UserResource($user),
            ], Response::HTTP_CREATED);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exceptions error',
                'errors' => [$e->getMessage()],
            ], Response::HTTP_EXPECTATION_FAILED);
        }
    }
}
