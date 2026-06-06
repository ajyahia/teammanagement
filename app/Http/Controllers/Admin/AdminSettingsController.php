<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SpecificHoliday;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    /**
     * Display the settings index page.
     */
    public function index()
    {
        $weeklyHolidaysJson = Setting::getVal('weekly_holidays', '[]');
        $weeklyHolidays = json_decode($weeklyHolidaysJson, true) ?? [];

        $specificHolidays = SpecificHoliday::orderBy('date', 'asc')->get();

        return view('admin.settings.index', compact('weeklyHolidays', 'specificHolidays'));
    }

    /**
     * Save weekly holidays.
     */
    public function saveWeekly(Request $request)
    {
        $validatedData = $request->validate([
            'weekly_holidays' => ['nullable', 'array'],
            'weekly_holidays.*' => ['integer', 'min:0', 'max:6'],
        ]);

        $weeklyHolidays = $validatedData['weekly_holidays'] ?? [];

        Setting::setVal('weekly_holidays', json_encode(array_map('intval', $weeklyHolidays)));

        return redirect()->route('admin.settings.index')->with('success', 'Weekly holidays saved successfully.');
    }

    /**
     * Add a specific holiday date.
     */
    public function addSpecific(Request $request)
    {
        $validatedData = $request->validate([
            'date' => ['required', 'date', 'unique:specific_holidays,date'],
            'name' => ['nullable', 'string', 'max:100'],
        ]);

        SpecificHoliday::create([
            'date' => $validatedData['date'],
            'name' => $validatedData['name'] ?? null,
        ]);

        return redirect()->route('admin.settings.index')->with('success', 'Holiday date added successfully.');
    }

    /**
     * Delete a specific holiday date.
     */
    public function deleteSpecific(SpecificHoliday $holiday)
    {
        $holiday->delete();
        return redirect()->route('admin.settings.index')->with('success', 'Holiday date removed successfully.');
    }
}
