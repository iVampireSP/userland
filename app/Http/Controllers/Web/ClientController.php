<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Passport\ClientRepository;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 获取此用户的所有客户端
        $clients = auth('web')->user()->clients()->latest()->paginate(20);

        return view('clients.index', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'redirect' => 'required|url',
            'personal_access_client' => 'boolean',
            'password_client' => 'boolean',
        ]);


        $clients = new ClientRepository();

        $client = $clients->create(
            $request->user()->getAuthIdentifier(), $request->input('name'), $request->input('redirect'),
            null, $request->boolean('personal_access_client'), $request->boolean('password_client')
        );

        return redirect()->route('clients.show', compact('client'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $client = auth('web')->user()->clients()->findOrFail($id);

        return view('clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $client = auth('web')->user()->clients()->findOrFail($id);

        return view('clients.show', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'redirect' => 'required|url',
            'password_client' => 'boolean',
            'personal_access_client' => 'boolean',
        ]);

        $client = auth('web')->user()->clients()->findOrFail($id);

        $client->update([
            'name' => $request->input('name'),
            'redirect' => $request->input('redirect'),
            'password_client' => $request->boolean('password_client'),
            'personal_access_client' => $request->boolean('personal_access_client'),
        ]);

        return redirect()->route('clients.show', compact('client'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $client = auth('web')->user()->clients()->findOrFail($id);

        $client->delete();

        return redirect()->route('clients.index');
    }
}
