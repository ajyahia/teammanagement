<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyPolicy;
use Illuminate\Http\Request;

class AdminPolicyController extends Controller
{
    /**
     * Display a listing of the policies.
     */
    public function index()
    {
        $policies = CompanyPolicy::orderBy('sort_order', 'asc')->get();
        return view('admin.policies.index', compact('policies'));
    }

    /**
     * Show the form for creating a new policy.
     */
    public function create()
    {
        return view('admin.policies.create');
    }

    /**
     * Store a newly created policy in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'title_ar' => 'required|string|max:255',
            'content' => 'required|string',
            'content_ar' => 'required|string',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        if (empty($validated['icon'])) {
            $validated['icon'] = 'ri-file-text-line';
        }
        if (empty($validated['sort_order'])) {
            $validated['sort_order'] = 0;
        }

        CompanyPolicy::create($validated);

        return redirect()->route('admin.policies.index')
            ->with('success', 'Policy created successfully.');
    }

    /**
     * Show the form for editing the specified policy.
     */
    public function edit($id)
    {
        $policy = CompanyPolicy::findOrFail($id);
        return view('admin.policies.edit', compact('policy'));
    }

    /**
     * Update the specified policy in storage.
     */
    public function update(Request $request, $id)
    {
        $policy = CompanyPolicy::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'title_ar' => 'required|string|max:255',
            'content' => 'required|string',
            'content_ar' => 'required|string',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        if (empty($validated['icon'])) {
            $validated['icon'] = 'ri-file-text-line';
        }
        if (empty($validated['sort_order'])) {
            $validated['sort_order'] = 0;
        }

        $policy->update($validated);

        return redirect()->route('admin.policies.index')
            ->with('success', 'Policy updated successfully.');
    }

    /**
     * Remove the specified policy from storage.
     */
    public function destroy($id)
    {
        $policy = CompanyPolicy::findOrFail($id);
        $policy->delete();

        return redirect()->route('admin.policies.index')
            ->with('success', 'Policy deleted successfully.');
    }
}
