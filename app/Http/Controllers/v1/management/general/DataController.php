<?php

namespace App\Http\Controllers\v1\management\general;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataController extends Controller
{
    public function generalStatus(): JsonResponse
    {
        $status = [
            [
                'id' => 0,
                'name' => 'Inactivo'
            ],
            [
                'id' => 1,
                'name' => 'Activo'
            ],
        ];

        return response()->json(['response' => $status]);
    }
}
