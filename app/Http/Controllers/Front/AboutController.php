<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function index(): View
    {
        $settings = Setting::getAll();
        return view('front.about', compact('settings'));
    }
}