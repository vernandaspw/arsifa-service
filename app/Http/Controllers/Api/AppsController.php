<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apps;

class AppsController extends Controller
{
    public function get()
    {
        try {
            $data = Apps::get();

            return response()->json([
                'msg' => 'success',
                'data' => $data,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'msg' => 'error',
                'data' => $e->getMessage(),
            ], 500);
        }
    }
}
