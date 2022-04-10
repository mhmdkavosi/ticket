<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Jobs\RegisterUserJob;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @author  mhmdkavosi.dev@gmaill.com
     */
    public function sign_in(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users,email',
            'password' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'VALIDATION_ERROR',
                'messages' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->input('email'))
            ->first();

        if (!Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Wrong password!'
            ], 400);
        }

        $token_name = strtolower($user->role) . '_token';
        $access_token = $user->createToken($token_name)->plainTextToken;

        return response()->json([
            'status' => 'OK',
            'data' => [
                'access_token' => $access_token
            ]
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @author  mhmdkavosi.dev@gmaill.com
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'VALIDATION_ERROR',
                'messages' => $validator->errors()
            ], 422);
        }

        // Register user (default role user)
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password'))
        ]);

        // Send register mail to user from queue
        RegisterUserJob::dispatch($user->email, $user->name)->delay(10);

        return response()->json([
            'status' => 'OK'
        ]);
    }
}
