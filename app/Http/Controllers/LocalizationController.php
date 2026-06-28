<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LocalizationController extends Controller
{
    public function switch($locale)
    {
        // Check if the language is supported
        if (in_array($locale, ['en', 'ta'])) {
            // Store the user's choice in their session
            Session::put('locale', $locale);
        }
        // Go back to the page the user was on
        return redirect()->back();
    }
}