<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Donate;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class DonateController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'cardNumber' => 'required|digits:16',
            'expiryMonth' => 'required|digits:2',
            'expiryYear' => 'required|digits:4',
            'cvv' => 'required|digits_between:3,4',
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error',
                'errors' => $validator->errors()->all(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            Donate::create([
                'name' => $request->name,
                'email' => $request->email,
                'card_number' => $request->cardNumber,
                'expiry_month' => $request->expiryMonth,
                'expiry_year' => $request->expiryYear,
                'cvv' => $request->cvv,
                'amount' => $request->amount,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Donate details saved successfully.',
                'data' => [],
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

