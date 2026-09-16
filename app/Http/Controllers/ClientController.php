<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(): Response
    {
        $clients = Client::query()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (Client $client) => [
                'id' => $client->id,
                'client_code' => $client->client_code,
                'client_type' => $client->client_type,
                'display_name' => $client->display_name,
                'rtn' => $client->rtn,
                'identity_number' => $client->identity_number,
                'phone' => $client->phone,
                'email' => $client->email,
                'city' => $client->city,
                'active' => $client->active,
            ]);

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Clients/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_type' => [
                'required',
                Rule::in([
                    'natural',
                    'business',
                    'institution',
                    'occasional',
                    'final_consumer',
                ]),
            ],

            'first_name' => [
                Rule::requiredIf(
                    fn () => in_array(
                        $request->client_type,
                        ['natural', 'occasional']
                    )
                ),
                'nullable',
                'string',
                'max:100',
            ],

            'middle_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'last_name' => [
                Rule::requiredIf(
                    fn () => in_array(
                        $request->client_type,
                        ['natural', 'occasional']
                    )
                ),
                'nullable',
                'string',
                'max:100',
            ],

            'second_last_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'business_name' => [
                Rule::requiredIf(
                    fn () => in_array(
                        $request->client_type,
                        ['business', 'institution']
                    )
                ),
                'nullable',
                'string',
                'max:180',
            ],

            'identity_number' => [
                'nullable',
                'string',
                'max:30',
                'unique:clients,identity_number',
            ],

            'rtn' => [
                'nullable',
                'string',
                'max:30',
                'unique:clients,rtn',
            ],

            'contact_person' => [
                'nullable',
                'string',
                'max:150',
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'alternate_phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'active' => [
                'boolean',
            ],
        ]);

        $nextNumber = (Client::withTrashed()->max('id') ?? 0) + 1;

        $clientCode = 'CLI-' . str_pad(
            (string) $nextNumber,
            4,
            '0',
            STR_PAD_LEFT
        );

        Client::create([
            ...$validated,
            'client_code' => $clientCode,
            'active' => $validated['active'] ?? true,
        ]);

        return redirect()
            ->route('clients.index')
            ->with('success', 'Cliente registrado correctamente.');
    }

    public function edit(Client $client): Response
    {
        return Inertia::render('Clients/Edit', [
            'client' => [
                'id' => $client->id,
                'client_code' => $client->client_code,
                'client_type' => $client->client_type,
                'first_name' => $client->first_name,
                'middle_name' => $client->middle_name,
                'last_name' => $client->last_name,
                'second_last_name' => $client->second_last_name,
                'business_name' => $client->business_name,
                'identity_number' => $client->identity_number,
                'rtn' => $client->rtn,
                'contact_person' => $client->contact_person,
                'email' => $client->email,
                'phone' => $client->phone,
                'alternate_phone' => $client->alternate_phone,
                'address' => $client->address,
                'city' => $client->city,
                'notes' => $client->notes,
                'active' => $client->active,
            ],
        ]);
    }

    public function update(
        Request $request,
        Client $client
    ): RedirectResponse {
        $validated = $request->validate([
            'client_type' => [
                'required',
                Rule::in([
                    'natural',
                    'business',
                    'institution',
                    'occasional',
                    'final_consumer',
                ]),
            ],

            'first_name' => [
                Rule::requiredIf(
                    fn () => in_array(
                        $request->client_type,
                        ['natural', 'occasional']
                    )
                ),
                'nullable',
                'string',
                'max:100',
            ],

            'middle_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'last_name' => [
                Rule::requiredIf(
                    fn () => in_array(
                        $request->client_type,
                        ['natural', 'occasional']
                    )
                ),
                'nullable',
                'string',
                'max:100',
            ],

            'second_last_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'business_name' => [
                Rule::requiredIf(
                    fn () => in_array(
                        $request->client_type,
                        ['business', 'institution']
                    )
                ),
                'nullable',
                'string',
                'max:180',
            ],

            'identity_number' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('clients', 'identity_number')
                    ->ignore($client->id),
            ],

            'rtn' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('clients', 'rtn')
                    ->ignore($client->id),
            ],

            'contact_person' => [
                'nullable',
                'string',
                'max:150',
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'alternate_phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'active' => [
                'boolean',
            ],
        ]);

        $client->update([
            ...$validated,
            'active' => $validated['active'] ?? false,
        ]);

        return redirect()
            ->route('clients.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }
}