<?php

namespace App\Http\Controllers;

use App\Models\Borrower;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class BorrowerRegistrationController extends Controller
{
    public function create(): View
    {
        return view('register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:borrowers,phone'],
            'email' => ['nullable', 'email', 'max:255', 'unique:borrowers,email'],
            'address' => ['required', 'string', 'max:500'],
            'national_id' => ['required', 'string', 'max:20', 'unique:borrowers,national_id'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        Borrower::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'address' => $data['address'],
            'national_id' => $data['national_id'],
            'password' => Hash::make($data['password']),
            'is_active' => false,
        ]);

        return redirect('/register')->with('registered', true);
    }
}
