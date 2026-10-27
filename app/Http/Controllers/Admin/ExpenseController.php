<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Project;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) ($request->input('year') ?: now()->year);
        $month = (int) $request->input('month');
        $month = ($month >= 1 && $month <= 12) ? $month : null;

        // Totals per month for the selected year
        $monthlyTotals = array_fill(1, 12, 0.0);
        Expense::whereYear('expense_date', $year)->get(['amount', 'expense_date'])
            ->each(function ($e) use (&$monthlyTotals) {
                $monthlyTotals[(int) $e->expense_date->format('n')] += (float) $e->amount;
            });
        $yearTotal = array_sum($monthlyTotals);
        $monthTotal = $month ? $monthlyTotals[$month] : null;

        $years = Expense::get(['expense_date'])->map(fn($e) => (int) $e->expense_date->format('Y'))
            ->push($year, (int) now()->year)->unique()->sortDesc()->values();

        $expenses = Expense::with('project.client')
            ->whereYear('expense_date', $year)
            ->when($month, fn($q) => $q->whereMonth('expense_date', $month))
            ->latest('expense_date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.expenses.index', compact('expenses', 'year', 'month', 'years', 'monthlyTotals', 'yearTotal', 'monthTotal'));
    }

    public function create()
    {
        $projects = Project::with(['client', 'service'])->latest()->get();
        return view('admin.expenses.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        Expense::create($validated);

        return redirect()->route('admin.expenses.index')->with('success', __('Expense added successfully'));
    }

    public function edit(Expense $expense)
    {
        $projects = Project::with(['client', 'service'])->latest()->get();
        return view('admin.expenses.edit', compact('expense', 'projects'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $expense->update($validated);

        return redirect()->route('admin.expenses.index')->with('success', __('Expense updated successfully'));
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('admin.expenses.index')->with('success', __('Expense deleted successfully'));
    }
}
