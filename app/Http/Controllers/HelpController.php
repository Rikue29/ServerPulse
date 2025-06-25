<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelpController extends Controller
{
    /**
     * Show the help and support page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('help.index');
    }
}
