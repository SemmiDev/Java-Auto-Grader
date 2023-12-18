<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ErrorController extends Controller
{
    public function index(Request $request)
    {
        $statusCode = $request->session()->get("statusCode");
        $message = $request->session()->get("message");

        return view("error", [
            "statusCode" => $statusCode,
            "message" => $message
        ]);
    }
}
