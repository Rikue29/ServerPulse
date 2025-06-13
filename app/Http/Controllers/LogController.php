<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Server;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        return view('logs'); 
    }
}
