<?php

namespace App\Http\Controllers\PrimeEdge\Auth;

use App\Http\Controllers\Controller;
use App\Application\PrimeEdge\UseCases\RegisterClientUseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RegisteredUserController extends Controller
{
    public function __construct(
        private RegisterClientUseCase $registerClientUseCase,
    ) {}

    public function create()
    {
        return Inertia::render('PrimeEdge/Auth/Register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'company' => ['nullable', 'string', 'max:255'],
        ]);

        $clientDTO = $this->registerClientUseCase->execute(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
            phone: $validated['phone'] ?? null,
            companyName: $validated['company'] ?? null,
        );

        Auth::guard('web')->loginUsingId($clientDTO->id);

        return redirect()->route('primeedge.dashboard');
    }
}
