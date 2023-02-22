<?php

if (!function_exists('errorLogs')) {
    function errorLogs($method_name, $line_no, $error)
    {
        return response()->json([
            "method_name" => $method_name,
            "line_no" => $line_no,
            "error" => $error,
        ], 500);
    }
}
