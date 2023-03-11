<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Laravel\Passport\ClientRepository;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $clients = (new \App\Models\Client)->paginate(50);

        return view('admin.clients.index', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'redirect' => 'required|url',
            'personal_access_client' => 'boolean',
            'password_client' => 'boolean',
        ]);

        $clients = new ClientRepository();

        $client = $clients->create(
            null, $request->input('name'), $request->input('redirect'),
            null, $request->boolean('personal_access_client'), $request->boolean('password_client')
        );

        return redirect()->route('admin.clients.show', compact('client'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        return view('admin.clients.create');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client): \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        return view('admin.clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client): \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        return view('admin.clients.show', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'redirect' => 'required|url',
            'password_client' => 'boolean',
            'personal_access_client' => 'boolean',
        ]);

        $client->update([
            'name' => $request->input('name'),
            'redirect' => $request->input('redirect'),
            'password_client' => $request->boolean('password_client'),
            'personal_access_client' => $request->boolean('personal_access_client'),
        ]);

        return redirect()->route('admin.clients.show', compact('client'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client): \Illuminate\Http\RedirectResponse
    {
        $client->delete();

        return redirect()->route('admin.clients.index');
    }
}
