<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientProjectController extends Controller
{
    public function index()
    {
        $clientId = Auth::guard('client')->id();
        $projects = Project::with(['service', 'payments'])->where('client_id', $clientId)->latest()->get();
        
        return view('client.projects.index', compact('projects'));
    }

    public function show(Project $project)
    {
        // Ensure the project belongs to the logged-in client
        if ($project->client_id !== Auth::guard('client')->id()) {
            abort(403, 'Unauthorized action.');
        }

        $project->load(['service', 'payments']);
        
        return view('client.projects.show', compact('project'));
    }
}
