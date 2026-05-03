<?php

namespace App\Http\Controllers;

use App\Models\AdText;
use Illuminate\Http\Request;

class AdTextController extends Controller
{
    // GET /ad-texts (Public)
    public function index()
    {
        $adTexts = AdText::all();

        return response()->json([
            'status' => 'success',
            'data' => $adTexts
        ]);
    }

    // POST /ad-texts
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        $adText = AdText::create([
            'content' => $request->content
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Ad text created successfully',
            'data' => $adText
        ], 201);
    }

    // GET /ad-texts/{id}
    public function show($id)
    {
        $adText = AdText::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $adText
        ]);
    }

    // PUT /ad-texts/{id}
    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        $adText = AdText::findOrFail($id);
        $adText->update([
            'content' => $request->content
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Ad text updated successfully',
            'data' => $adText
        ]);
    }

    // DELETE /ad-texts/{id}
    public function destroy($id)
    {
        $adText = AdText::findOrFail($id);
        $adText->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Ad text deleted successfully'
        ]);
    }
}
