<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreReservationRequest;
use App\Http\Requests\Admin\UpdateReservationStatusRequest;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function __construct(private readonly ReservationService $reservationService)
    {
    }

    public function index(Request $request): View
    {
        $statusFilter = $request->string('status')->toString();
        $date = $request->date('date');

        $query = Reservation::query()
            ->with(['guest', 'table'])
            ->orderByDesc('reservation_date')
            ->orderBy('reservation_time');

        if ($date) {
            $query->whereDate('reservation_date', $date);
        }

        $filterActive = 'all';

        switch ($statusFilter) {
            case 'confirmed':
                $query->where('status', ReservationStatus::Confirmed->value);
                $filterActive = 'confirmed';
                break;
            case 'pending':
                $query->where('status', ReservationStatus::Pending->value);
                $filterActive = 'pending';
                break;
            case 'cancelled':
                $query->where('status', ReservationStatus::Cancelled->value);
                $filterActive = 'cancelled';
                break;
            case 'upcoming':
                $query->whereDate('reservation_date', '>=', now()->toDateString())
                    ->whereIn('status', [
                        ReservationStatus::Pending->value,
                        ReservationStatus::Confirmed->value,
                    ]);
                $filterActive = 'upcoming';
                break;
        }

        $reservations = $query->paginate(15)->withQueryString();

        $totals = [
            'all' => Reservation::count(),
            'confirmed' => Reservation::where('status', ReservationStatus::Confirmed->value)->count(),
            'pending' => Reservation::where('status', ReservationStatus::Pending->value)->count(),
            'cancelled' => Reservation::where('status', ReservationStatus::Cancelled->value)->count(),
            'upcoming' => Reservation::whereDate('reservation_date', '>=', now()->toDateString())
                ->whereIn('status', [
                    ReservationStatus::Pending->value,
                    ReservationStatus::Confirmed->value,
                ])->count(),
        ];

        return view('admin.reservations.index', [
            'reservations' => $reservations,
            'totals' => $totals,
            'filterActive' => $filterActive,
            'selectedDate' => $date?->format('Y-m-d'),
            'statusOptions' => ReservationStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.reservations.create', [
            'tables' => RestaurantTable::orderBy('priority')->orderBy('name')->get(),
            'sources' => config('reservations.sources'),
            'occasions' => config('reservations.occasions'),
            'allergies' => config('reservations.allergies'),
            'diets' => config('reservations.diets'),
        ]);
    }

    public function store(StoreReservationRequest $request): RedirectResponse
    {
        $reservation = $this->reservationService->createFromAdmin($request->validated(), $request->user());

        return redirect()->route('admin.reservations.show', $reservation)->with('success', __('Reservation created successfully.'));
    }

    public function show(Reservation $reservation): View
    {
        $reservation->load(['guest', 'table', 'history.performer']);

        return view('admin.reservations.show', [
            'reservation' => $reservation,
            'statusOptions' => ReservationStatus::cases(),
            'tables' => RestaurantTable::orderBy('priority')->orderBy('name')->get(),
        ]);
    }

    public function updateStatus(UpdateReservationStatusRequest $request, Reservation $reservation): RedirectResponse
    {
        $status = ReservationStatus::from($request->validated('status'));

        $context = [
            'reservation_notes' => $request->validated('reservation_notes'),
            'restaurant_table_id' => $request->validated('restaurant_table_id'),
            'performed_by' => $request->user()->id,
        ];

        if ($status === ReservationStatus::Cancelled) {
            $context['cancel_reason'] = $request->validated('cancel_reason');
        }

        $this->reservationService->updateStatus($reservation, $status, $context);

        return back()->with('success', __('Reservation status updated successfully.'));
    }

    public function destroy(Reservation $reservation): RedirectResponse
    {
        $reservation->delete();

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', __('Reservation deleted successfully.'));
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:reservations,id'],
        ]);

        $ids = $data['ids'];

        $count = Reservation::query()->whereIn('id', $ids)->count();

        if ($count > 0) {
            Reservation::query()->whereIn('id', $ids)->delete();
        }

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', trans_choice('Deleted :count reservation.|Deleted :count reservations.', $count, ['count' => $count]));
    }
}
