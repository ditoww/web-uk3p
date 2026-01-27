<?php

if (!function_exists('success_response')) {
    function success_response($data)
    {
        return response()->json([
            'error' => false,
            'message' => 'Berhasil',
            'data' => $data
        ], 200);
    }
}

if (!function_exists('failed_response')) {
    function failed_response($data)
    {
        return response()->json([
            'error' => true,
            'message' => 'Gagal',
            'data' => $data
        ], 400);
    }
}
