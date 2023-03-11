<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Passport\ClientRepository;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Factory|\Illuminate\Foundation\Application|View|Application
    {
        $clients = (new Client)->paginate(50);

        return view('admin.clients.index', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'redirect' => 'required|url',
            'personal_access_client' => 'boolean',
            'password_client' => 'boolean',
            'provider' => 'required|string',
        ]);

        $clients = new ClientRepository();

        $client = $clients->create(
            null, $request->input('name'), $request->input('redirect'),
            $request->input('provider'), $request->boolean('personal_access_client'), $request->boolean('password_client')
        );

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
            'redirect' => 'required|url',
            'password_client' => 'boolean',
            'personal_access_client' => 'boolean',
            'provider' => 'nullable|string',
            'trusted' => 'nullable|boolean',
        ]);

        $client->update([
            'name' => $request->input('name'),
            'redirect' => $request->input('redirect'),
            'password_client' => $request->boolean('password_client'),
            'personal_access_client' => $request->boolean('personal_access_client'),
            'provider' => $request->input('provider'),
            'trusted' => $request->boolean('trusted'),
        ]);

        return redirect()->route('admin.clients.show', compact('client'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()->route('admin.clients.index');
    }
}
