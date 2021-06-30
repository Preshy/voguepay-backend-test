<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;

class UtilsController extends Controller
{
    /**
     * Generate Account ID function
     * 
     * @return void
     */
    public static function generateAccountID() {
        return mt_rand(1000000000, 9999999999);
    }

    /**
     * Generate random strings
     *
     * @return string
     */
    public static function generateUUID() {
        return Str::random(10);
    }

    /**
     * errorResponse function
     * Responds with an error
     * @param array $data
     * @return void
     */
    public static function errorResponse($data = []) {
        return response()->json(['code' => 400, 'status' => 'error', 'message' => 'An error ocurred', 'data' => $data], 400);
    }

     /**
     * successResponse function
     * Responds with an error
     * @param array $data
     * @return void
     */
    public static function successResponse($data = []) {
        return response()->json(['code' => 200, 'status' => 'ok', 'message' => 'Data Retrieved', 'data' => $data], 200);
    }
}
