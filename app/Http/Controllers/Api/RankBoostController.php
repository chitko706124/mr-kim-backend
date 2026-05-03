<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RankBoost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RankBoostController extends Controller
{
    public function index()
    {
        $rankBoosts = RankBoost::orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $rankBoosts
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'price' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $rankBoost = RankBoost::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'title' => $request->title,
            'price' => $request->price
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Rank boost created successfully',
            'data' => $rankBoost
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $rankBoost = RankBoost::find($id);

        if (!$rankBoost) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rank boost not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $rankBoost->update($request->only(['title', 'price']));

        return response()->json([
            'status' => 'success',
            'message' => 'Rank boost updated successfully',
            'data' => $rankBoost
        ]);
    }

    public function destroy($id)
    {
        $rankBoost = RankBoost::find($id);

        if (!$rankBoost) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rank boost not found'
            ], 404);
        }

        $rankBoost->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Rank boost deleted successfully'
        ]);
    }
}
