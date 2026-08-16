<?php

namespace App\MCF\Modules\Shared\Layout\Backend;

use App\MCF\Base\MfcController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LayoutController extends MfcController
{

    public function switchLanguage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'in:ar,en'],
        ]);

        Session::put('locale', $validated['locale']);

        return back();
    }

}
