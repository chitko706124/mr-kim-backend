<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // public function login(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'email' => 'required|email',
    //         'password' => 'required|string|min:6',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Validation failed',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     $user = AdminUser::where('email', $request->email)->first();

    //     if (!$user || !Hash::check($request->password, $user->password_hash)) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Invalid credentials'
    //         ], 401);
    //     }

    //     $token = $user->createToken('admin-token')->plainTextToken;

    //     return response()->json([
    //         'status' => 'success',
    //         'token' => $token,
    //         'user' => [
    //             'id' => $user->id,
    //             'email' => $user->email
    //         ]
    //     ]);
    // }
    public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|string|min:6',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    // Find user by email
    $user = AdminUser::where('email', $request->email)->first();

    // Check password
    if (!$user || !Hash::check($request->password, $user->password_hash)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid credentials'
        ], 401);
    }

    // Create token
    // $token = $user->createToken('admin-token', ['*'])->plainTextToken;
    $token = $user->createToken('admin-token')->plainTextToken;
    // $token = "testing";

    return response()->json([
        'status' => 'success',
        'token' => $token,
        'token_type' => 'Bearer',
        'user' => [
            'id' => $user->id,
            'email' => $user->email
        ]
    ]);
}

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password_hash)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $user->password_hash = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully'
        ]);
    }

    public function checkAuth(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'authenticated' => $request->user() !== null,
            'user' => $request->user()
        ]);
    }
}
