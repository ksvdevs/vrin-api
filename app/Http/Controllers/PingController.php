<?php

namespace App\Http\Controllers;

use App\Models\Facultad;
use Illuminate\Http\JsonResponse;

class PingController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'db' => Facultad::count(),
        ]);
    }
}
