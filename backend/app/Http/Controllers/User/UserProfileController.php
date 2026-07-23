<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;

class UserProfileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UserProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('success', 'Đã cập nhật thông tin hồ sơ.');
    }
}
