<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with('components')->withCount(['projects', 'users'])->latest()->paginate(12);
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        $otherServices = Service::orderBy('name')->get();
        return view('admin.services.create', compact('otherServices'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_price' => 'required|numeric|min:0',
            'component_ids' => 'nullable|array',
            'component_ids.*' => 'exists:services,id',
        ]);

        $componentIds = $data['component_ids'] ?? [];
        unset($data['component_ids']);

        $service = Service::create($data);
        $service->components()->sync($componentIds);
        return redirect()->route('admin.services.index')->with('success', 'Service created successfully.');
    }

    public function edit(Service $service)
    {
        $otherServices = Service::where('id', '!=', $service->id)->orderBy('name')->get();
        return view('admin.services.edit', compact('service', 'otherServices'));
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_price' => 'required|numeric|min:0',
            'component_ids' => 'nullable|array',
            'component_ids.*' => 'exists:services,id',
        ]);

        $componentIds = $data['component_ids'] ?? [];
        unset($data['component_ids']);

        $service->update($data);
        // A service cannot contain itself
        $service->components()->sync(array_values(array_diff($componentIds, [$service->id])));
        return redirect()->route('admin.services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('admin.services.index')->with('success', 'Service deleted successfully.');
    }
}
