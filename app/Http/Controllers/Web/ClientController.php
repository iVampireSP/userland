<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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
    public function create(): Factory|\Illuminate\Foundation\Application|View|Application
    {
        return view('clients.create');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): Factory|\Illuminate\Foundation\Application|View|Application
    {
        $client = auth('web')->user()->clients()->findOrFail($id);

        return view('clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): Factory|\Illuminate\Foundation\Application|View|Application
    {
        $client = auth('web')->user()->clients()->findOrFail($id);

        return view('clients.show', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'redirect' => ['required', 'regex:/^[^:]+:\/\//'],
            'description' => 'nullable|string',
            'reset_client_secret' => 'boolean',
            'personal_access_client' => 'boolean',
            //            'password_client' => 'boolean',
        ]);

        $client = auth('web')->user()->clients()->findOrFail($id);

        $client->update([
            'name' => $request->input('name'),
            'redirect' => $request->input('redirect'),
            'personal_access_client' => $request->boolean('personal_access_client'),
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
    public function destroy(string $id): RedirectResponse
    {
        $client = auth('web')->user()->clients()->findOrFail($id);

        $client->delete();

        return redirect()->route('clients.index');
    }
}
