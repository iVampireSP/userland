<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
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
    public function index(): View
    {
        // 获取此用户的所有客户端
        $clients = auth('web')->user()->clients()->latest()->paginate(20);

        return view('clients.index', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'redirect' => ['required', 'regex:/^[^:]+:\/\//'],
            'pkce_client' => 'boolean',
            //            'password_client' => 'boolean',
        ]);

        $clients = new ClientRepository;

        $client = $clients->create(
            userId: $request->user()->getAuthIdentifier(),
            name: $request->input('name'),
            redirect: $request->input('redirect'),
        );

        if ($request->boolean('pkce_client')) {
            $client->update([
                'secret' => null,
            ]);
        }

        return redirect()->route('clients.show', compact('client'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('clients.create');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $client = auth('web')->user()->clients()->findOrFail($id);

        $this->authorize('view', $client);

        return view('clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client): View
    {
        $this->authorize('update', $client);

        return view('clients.show', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $request->validate([
            'name' => 'required|string|max:255',
            'redirect' => ['required', 'regex:/^[^:]+:\/\//'],
            'description' => 'nullable|string',
            'reset_client_secret' => 'boolean',
        ]);

        $client->update([
            'name' => $request->input('name'),
            'redirect' => $request->input('redirect'),
            'description' => $request->input('description'),
        ]);

        if ($request->boolean('reset_client_secret')) {
            $client->update([
                'secret' => Str::random(40),
            ]);
        }

        return redirect()->route('clients.show', compact('client'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $client->delete();

        return redirect()->route('clients.index');
    }
}
