<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LanguageController extends Controller
{
    public function index(): View
    {
        return view('admin.language.index', [
            'availableLocales' => [
                'en' => __('English'),
                'nl' => __('Dutch'),
            ],
            'currentLocale' => app()->getLocale(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'locale' => ['required', 'in:en,nl'],
        ]);

        session(['locale' => $data['locale']]);

        return back()->with('success', __('Language updated successfully.'));
    }
}