<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IcdCode;

class IcdCodeController extends Controller
{
    //
     public function index(Request $request)
    {
        $search = $request->search;

        $search = $request->search;

    return IcdCode::where('code', 'like', "%{$search}%")
        ->orWhere('description', 'like', "%{$search}%")
        ->limit(20)
        ->get();

    }
}
