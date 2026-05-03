<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AdController extends Controller
{
    public function index()
    {
        $ads = Ad::orderBy('order_index')->get();


        return response()->json([
            'status' => 'success',
            'data' => $ads
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'link' => 'nullable|url',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'order_index' => 'integer|min:0|nullable',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Upload image
        $path = $request->file('image')->store('ads', 'public');
        $imageUrl = config('app.url') . Storage::url($path);

        $ad = Ad::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'title' => $request->title,
            'link' => $request->link,
            'image_url' => $imageUrl,
            'order_index' => $request->order_index ?? 0,
            'is_active' => $request->is_active ?? true
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Ad created successfully',
            'data' => $ad
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $ad = Ad::find($id);

        if (!$ad) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ad not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'link' => 'nullable|url',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:5120',
            'order_index' => 'integer|min:0',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['title', 'link', 'order_index', 'is_active']);

        if ($request->hasFile('image')) {
            // Delete old image
            $oldImagePath = str_replace('/storage/', '', $ad->image_url);
            Storage::disk('public')->delete($oldImagePath);

            // Upload new image
            $path = $request->file('image')->store('ads', 'public');
            $data['image_url'] =  config('app.url') . Storage::url($path);
        }

        $ad->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Ad updated successfully',
            'data' => $ad
        ]);
    }

    public function destroy($id)
    {
        $ad = Ad::find($id);

        if (!$ad) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ad not found'
            ], 404);
        }

        // Delete image from storage
        $imagePath = str_replace('/storage/', '', $ad->image_url);
        Storage::disk('public')->delete($imagePath);

        $ad->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Ad deleted successfully'
        ]);
    }

    public function updateOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ads' => 'required|array',
            'ads.*.id' => 'required|string',
            'ads.*.order_index' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->ads as $adData) {
            Ad::where('id', $adData['id'])->update(['order_index' => $adData['order_index']]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Order updated successfully'
        ]);
    }
}
