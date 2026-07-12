<?php

namespace App\Http\Controllers\PrimeEdge;

use App\Http\Controllers\Controller;
use App\Domain\PrimeEdge\Repositories\ClientRepositoryInterface;
use App\Domain\PrimeEdge\ValueObjects\ClientName;
use App\Domain\PrimeEdge\ValueObjects\PhoneNumber;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\EmailAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository,
    ) {}

    public function edit()
    {
        $clientId = auth()->guard('primeedge')->id();
        $client = $this->clientRepository->findById(ClientId::fromString($clientId));

        return Inertia::render('PrimeEdge/Profile/Edit', [
            'client' => [
                'id' => $clientId,
                'name' => $client->name()->toString(),
                'email' => $client->email()->toString(),
                'phone' => $client->phone()?->toString(),
                'company' => $client->companyName(),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'company' => ['nullable', 'string', 'max:255'],
        ]);

        $clientId = auth()->guard('primeedge')->id();
        $client = $this->clientRepository->findById(ClientId::fromString($clientId));

        $client->updateProfile(
            name: ClientName::fromString($validated['name']),
            phone: $validated['phone'] ? PhoneNumber::fromString($validated['phone']) : null,
            companyName: $validated['company'] ?? null,
        );

        $this->clientRepository->save($client);

        return redirect()->route('primeedge.profile.edit')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password:primeedge'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $clientId = auth()->guard('primeedge')->id();
        $client = $this->clientRepository->findById(ClientId::fromString($clientId));

        $client->changePassword(Hash::make($validated['password']));

        $this->clientRepository->save($client);

        return redirect()->route('primeedge.profile.edit')
            ->with('success', 'Password updated successfully.');
    }
}
