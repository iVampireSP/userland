<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Passport\ClientRepository;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Factory|\Illuminate\Foundation\Application|View|Application
    {
        $clients = (new Client)->load('user')->paginate(50);

        return view('admin.clients.index', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'redirect' => ['required', 'regex:/^[^:]+:\/\//'],
            'personal_access_client' => 'boolean',
            'password_client' => 'boolean',
            'pkce_client' => 'boolean',
        ]);

        $clients = new ClientRepository;

        $client = $clients->create(
            userId: null,
            name: $request->input('name'),
            redirect: $request->input('redirect'),
            personalAccess: $request->boolean('personal_access_client'),
            password: $request->boolean('password_client'),
        );

        if ($request->boolean('pkce_client')) {
            $client->update([
                'secret' => null,
            ]);
        }

        return redirect()->route('admin.clients.show', compact('client'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Factory|\Illuminate\Foundation\Application|View|Application
    {
        return view('admin.clients.create');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client): Factory|\Illuminate\Foundation\Application|View|Application
    {
        return view('admin.clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client): Factory|\Illuminate\Foundation\Application|View|Application
    {
        return view('admin.clients.show', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'redirect' => ['required', 'regex:/^[^:]+:\/\//'],
            'password_client' => 'boolean',
            'trusted' => 'nullable|boolean',
            'description' => 'nullable|string',
            'reset_client_secret' => 'boolean',
        ]);

        $client->update([
            'name' => $request->input('name'),
            'redirect' => $request->input('redirect'),
            'password_client' => $request->boolean('password_client'),
            'trusted' => $request->boolean('trusted'),
            'description' => $request->input('description'),
        ]);

        if ($request->boolean('reset_client_secret')) {
            $client->update([
                'secret' => Str::random(40),
            ]);
        }

        return redirect()->route('admin.clients.show', compact('client'));
    }

    //    /**
    //     * @throws ApiException
    //     */
    //    public function enableTenant(Client $client): RedirectResponse
    //    {
    //        $client->enableTenant();
    //
    //        return redirect()->route('admin.clients.show', compact('client'));
    //    }
    //
    //    public function disableTenant(Client $client): RedirectResponse
    //    {
    //        $client->disableTenant();
    //
    //        return redirect()->route('admin.clients.show', compact('client'));
    //    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()->route('admin.clients.index');
    }
}
