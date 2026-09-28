<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request, string $locale)
    {
        abort_unless(array_key_exists($locale, SetLocale::SUPPORTED), 404);

        $request->session()->put('locale', $locale);

        return back();
    }
}
