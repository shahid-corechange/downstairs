<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\Auth\NewPasswordRequestDTO;
use App\DTOs\Auth\NewPasswordResendRequestDTO;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\CreatePasswordNotification;
use Crypt;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use URL;

class NewPasswordController extends Controller
{
    /**
     * Display the create password view.
     */
    public function create(Request $request, string $hash): Response|RedirectResponse
    {
        $payload = $request->query('payload');

        try {
            $email = $payload ? Crypt::decryptString($payload) : '';
        } catch (DecryptException) {
            throw new NotFoundHttpException();
        }

        /** @var \App\Models\User */
        $user = User::where('email', $email)->first();

        if (! $user) {
            throw new NotFoundHttpException();
        }

        if ($user->hasVerifiedEmail()) {
            app()->setLocale($user->info->language ?? config('app.locale'));

            return redirect()->route('login')->with('success', __('account already verified'));
        }

        return Inertia::render('CreatePassword/index', [
            'payload' => $payload,
            'hash' => $hash,
            'isExpired' => ! URL::signatureHasNotExpired($request),
        ]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function store(NewPasswordRequestDTO $request): RedirectResponse
    {
        // Check if the payload can be decrypted
        try {
            $email = Crypt::decryptString($request->payload);
        } catch (DecryptException) {
            return back()->with('error', __('email not found'));
        }

        // Check if the email in the payload matches the email in the request
        if (! hash_equals($email, $request->email)) {
            return back()->with('error', __('email not found'));
        }

        /** @var \App\Models\User|null */
        $user = User::where('email', $email)->first();

        // Check if the hash matches the user identifier
        if (! $user || ! hash_equals($request->hash, $user->generateCreatePasswordHash())) {
            return back()->with('error', __('invalid password creation request'));
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'email_verified_at' => $user->freshTimestamp(),
        ])->save();

        event(new Verified($user));

        return redirect()->route('login')->with('success', __('password created successfully'));
    }

    /**
     * Handle an incoming new password resend request.
     */
    public function resend(NewPasswordResendRequestDTO $request): RedirectResponse
    {
        // Check if the payload can be decrypted
        try {
            $email = Crypt::decryptString($request->payload);
        } catch (DecryptException) {
            return back()->with('error', __('account not found'));
        }

        /** @var \App\Models\User|null */
        $user = User::where('email', $email)->first();

        if (! $user) {
            return back()->with('error', __('account not found'));
        }

        $user->notify(new CreatePasswordNotification($user));

        return back()->with('success', __('password creation email sent'));
    }
}
