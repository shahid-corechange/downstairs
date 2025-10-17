<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\Auth\ForgetPasswordRequestDTO;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        return Inertia::render('ForgetPassword/index');
    }

    /**
     * Handle an incoming password reset link request.
     *
     *
     * @throws ValidationException
     */
    public function store(ForgetPasswordRequestDTO $request): RedirectResponse
    {
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->toArray()
        );

        return $status == Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withInput($request->toArray())
                ->withErrors(['email' => __($status)]);
    }
}
