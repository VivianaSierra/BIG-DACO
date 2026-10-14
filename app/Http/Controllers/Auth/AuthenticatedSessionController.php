<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['id_token' => ['required', 'string']]);

        $response = Http::asJson()->post(
            'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key='.config('services.firebase.api_key'),
            ['idToken' => $request->input('id_token')]
        );

        $account = $response->json('users.0');

        if (! $response->successful() || ! $account || ! empty($account['disabled'])) {
            throw ValidationException::withMessages([
                'id_token' => 'No se pudo verificar la sesion con Firebase.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('firebase_user', [
            'id' => $account['localId'],
            'name' => $account['displayName'] ?? $account['email'] ?? 'Usuario',
            'email' => $account['email'] ?? '',
        ]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
