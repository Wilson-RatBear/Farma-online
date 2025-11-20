<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function testAuth()
    {
        try {
            $user = auth()->user();
            return response()->json([
                'success' => true,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}