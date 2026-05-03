<?php

namespace App\Http\Controllers;

use App\Models\SellText;
use Illuminate\Http\Request;

class SellTextController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
          $sellTexts = SellText::all();

        return response()->json([
            'status' => 'success',
            'data' => $sellTexts
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $request->validate([
            'sell_content' => 'required|string'
        ]);

        $sellText = SellText::create([
            'sell_content' => $request->sell_content
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Sell text created successfully',
            'data' => $sellText
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $sellText = SellText::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $sellText
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SellText $sellText)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
  public function update(Request $request, $id)
    {
        $request->validate([
            'sell_content' => 'required|string'
        ]);

        $sellText = SellText::findOrFail($id);
        $sellText->update([
            'sell_content' => $request->sell_content
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Sell text updated successfully',
            'data' => $sellText
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
     public function destroy($id)
    {
        $sellText = SellText::findOrFail($id);
        $sellText->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Sell text deleted successfully'
        ]);
    }
}
