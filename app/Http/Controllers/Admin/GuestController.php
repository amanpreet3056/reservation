<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class GuestController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $hasCompanyColumn = Schema::hasColumn('guests', 'company');

        $guests = Guest::query()
            ->withCount('reservations')
            ->when($search, function ($query, $search) use ($hasCompanyColumn) {
                $query->where(function ($q) use ($search, $hasCompanyColumn) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");

                    if ($hasCompanyColumn) {
                        $q->orWhere('company', 'like', "%{$search}%");
                    }
                });
            })
            ->orderByDesc('last_reservation_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.guests.index', [
            'guests' => $guests,
            'search' => $search,
        ]);
    }

    public function destroy(Request $request, Guest $guest): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $guest->delete();

        return redirect()
            ->route('admin.guests.index')
            ->with('success', __('Guest removed successfully.'));
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:guests,id'],
        ]);

        $ids = $data['ids'];

        $deleted = Guest::query()->whereIn('id', $ids)->delete();

        return redirect()
            ->route('admin.guests.index')
            ->with('success', trans_choice('Deleted :count guest.|Deleted :count guests.', $deleted, ['count' => $deleted]));
    }

    public function export(Request $request): Response
    {
        $this->authorizeAdmin($request);

        $filename = 'guests-' . now()->format('Ymd-His') . '.csv';

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['First Name', 'Last Name', 'Email', 'Phone', 'Company', 'Location', 'Last Reservation']);

            Guest::query()
                ->orderByDesc('last_reservation_at')
                ->chunk(200, function ($chunk) use ($handle) {
                    foreach ($chunk as $guest) {
                        fputcsv($handle, [
                            $guest->first_name,
                            $guest->last_name,
                            $guest->email,
                            $guest->phone,
                            $guest->company,
                            $guest->location,
                            optional($guest->last_reservation_at)->format('Y-m-d H:i'),
                        ]);
                    }
                });

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function authorizeAdmin(Request $request): void
    {
        if (!$request->user()?->isAdmin()) {
            abort(403);
        }
    }
}
