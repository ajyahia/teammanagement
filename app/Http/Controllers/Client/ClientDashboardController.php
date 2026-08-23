<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientDashboardController extends Controller
{
    public function index()
    {
        $client = Auth::guard('client')->user()->load(['projects.service', 'projects.payments']);
        
        $activeProjectsCount = $client->projects->whereIn('status', ['pending', 'in_progress'])->count();
        $totalPaid = $client->projects->flatMap->payments->where('status', 'paid')->sum('amount');
        
        // Calculate overdue payments
        $overduePayments = $client->projects->flatMap->payments->where('status', 'overdue')->sum('amount');
        $pendingPayments = $client->projects->flatMap->payments->where('status', 'pending')->sum('amount');
        
        // Get recent projects
        $recentProjects = $client->projects->sortByDesc('created_at')->take(3);

        return view('client.dashboard', compact(
            'client', 
            'activeProjectsCount', 
            'totalPaid', 
            'overduePayments', 
            'pendingPayments',
            'recentProjects'
        ));
    }
}
