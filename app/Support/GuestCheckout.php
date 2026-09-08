<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GuestCheckout
{
    /**
     * Crée un compte participant minimal ou refuse si l'e-mail existe déjà.
     *
     * @throws ValidationException
     */
    public static function resolveUser(Request $request): User
    {
        $email = strtolower($request->string('guest_email')->toString());

        if (User::where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'guest_email' => __('Guest checkout existing account'),
            ]);
        }

        return User::create([
            'name' => trim($request->string('guest_name')->toString()),
            'email' => $email,
            'phone' => $request->string('guest_phone')->toString(),
            'password' => Hash::make(Str::password(32)),
            'role' => 'participant',
        ]);
    }
}
