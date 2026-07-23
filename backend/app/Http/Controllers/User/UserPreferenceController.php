<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserPreferenceUpdateRequest;
use Illuminate\Http\RedirectResponse;

class UserPreferenceController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UserPreferenceUpdateRequest $request): RedirectResponse
    {
        $request->user()->setMeta('currency', strtoupper($request->string('currency')->toString()));

        return back()->with('success', 'Đã cập nhật đơn vị tiền tệ.');
    }
}
