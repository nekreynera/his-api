<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
class MssController extends Controller
{
    //
     public function show(Patient $patient)
    {
        return response()->json([
            'patient' => $patient
        ]);
    }
}
