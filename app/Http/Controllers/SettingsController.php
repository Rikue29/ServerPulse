<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display the application settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('settings');
    }
}
