<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::latest()->paginate(10);
        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        Client::create($data);
        return redirect()->route('admin.clients.index')->with('success', 'Client created successfully.');
    }

    public function show(Client $client)
    {
        $client->load(['projects.service', 'projects.payments', 'projects.employees']);
        
        $totalProjects = $client->projects->count();
        $totalRevenue = $client->projects->sum('total_revenue');
        $totalPaid = $client->projects->sum('total_paid');
        $totalDue = $client->projects->sum('due_amount');
        
        return view('admin.clients.show', compact('client', 'totalProjects', 'totalRevenue', 'totalPaid', 'totalDue'));
    }

    public function edit(Client $client)
    {
        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $client->update($data);
        return redirect()->route('admin.clients.index')->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('admin.clients.index')->with('success', 'Client deleted successfully.');
    }

    public function updatePassword(Request $request, Client $client)
    {
        $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $client->update([
            'password' => bcrypt($request->password),
        ]);

        return back()->with('success', 'Client portal password updated successfully.');
    }
}
