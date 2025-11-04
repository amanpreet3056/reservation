<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RestaurantTableRequest;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RestaurantTableController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $tables = RestaurantTable::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('area_name', 'like', "%{$search}%");
            })
            ->orderBy('priority')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.tables.index', [
            'tables' => $tables,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.tables.create');
    }

    public function store(RestaurantTableRequest $request): RedirectResponse
    {
        RestaurantTable::create($request->validated());

        return redirect()->route('admin.tables.index')->with('success', __('Table created successfully.'));
    }

    public function edit(RestaurantTable $table): View
    {
        return view('admin.tables.edit', compact('table'));
    }

    public function update(RestaurantTableRequest $request, RestaurantTable $table): RedirectResponse
    {
        $table->update($request->validated());

        return redirect()->route('admin.tables.index')->with('success', __('Table updated successfully.'));
    }

    public function destroy(RestaurantTable $table): RedirectResponse
    {
        $hasFutureReservations = Reservation::query()
            ->where('restaurant_table_id', $table->id)
            ->whereDate('reservation_date', '>=', now()->toDateString())
            ->exists();

        if ($hasFutureReservations) {
            return back()->withErrors([
                'table' => __('This table has upcoming reservations and cannot be deleted.'),
            ]);
        }

        $table->delete();

        return redirect()->route('admin.tables.index')->with('success', __('Table removed successfully.'));
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:restaurant_tables,id'],
        ]);

        $tables = RestaurantTable::query()->whereIn('id', $data['ids'])->get();

        $blocked = [];
        $removable = [];

        foreach ($tables as $table) {
            $hasFutureReservations = Reservation::query()
                ->where('restaurant_table_id', $table->id)
                ->whereDate('reservation_date', '>=', now()->toDateString())
                ->exists();

            if ($hasFutureReservations) {
                $blocked[] = $table->name;
                continue;
            }

            $removable[] = $table->id;
        }

        if (empty($removable)) {
            return redirect()
                ->route('admin.tables.index')
                ->withErrors([
                    'tables' => __('Selected tables could not be removed because they have upcoming reservations: :tables', [
                        'tables' => implode(', ', $blocked),
                    ]),
                ]);
        }

        $deleted = RestaurantTable::query()->whereIn('id', $removable)->delete();

        $message = trans_choice('Removed :count table.|Removed :count tables.', $deleted, ['count' => $deleted]);

        if (!empty($blocked)) {
            $message .= ' ' . __('Skipped: :tables (upcoming reservations).', ['tables' => implode(', ', $blocked)]);
        }

        return redirect()->route('admin.tables.index')->with('success', $message);
    }
}
