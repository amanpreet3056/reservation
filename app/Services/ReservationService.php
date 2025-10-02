<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\GuestRepository;
use App\Repositories\ReservationRepository;
use App\Repositories\TableRepository;
use RuntimeException;

class ReservationService
{
    public function __construct(
        private ReservationRepository $reservations,
        private GuestRepository $guests,
        private ?TableRepository $tables = null
    ) {
    }

    public function create(array $data): array
    {
        if (!isset($data['restaurant_name'])) {
            throw new RuntimeException('Restaurant name is required');
        }

        $guestId = $this->resolveGuestId($data);
        $tableId = $this->resolveTableId($data['table_id'] ?? null);

        $data['guest_id'] = $guestId;
        $data['table_id'] = $tableId;
        $data['token'] = $data['token'] ?? bin2hex(random_bytes(16));
        $data['status'] = $data['status'] ?? 'active';

        $result = $this->reservations->create($data);

        return array_merge($data, $result);
    }

    public function findActiveByToken(int $id, string $token): ?array
    {
        return $this->reservations->findActiveByToken($id, $token);
    }

    public function cancel(int $id): bool
    {
        return $this->reservations->cancel($id);
    }

    public function updateDateTime(int $id, string $date, string $time): bool
    {
        return $this->reservations->updateDateTime($id, $date, $time);
    }

    public function recent(int $limit = 20): array
    {
        return $this->reservations->recent($limit);
    }

    public function all(int $limit = 100, int $offset = 0): array
    {
        return $this->reservations->all($limit, $offset);
    }

    public function countAll(): int
    {
        return $this->reservations->countAll();
    }

    public function countUpcoming(): int
    {
        return $this->reservations->countUpcoming();
    }

    public function buildConfirmationEmail(array $reservation): array
    {
        $restaurant = $reservation['restaurant_name'];
        $subject = 'Reservation Confirmation - ' . $restaurant;

        $cancelLink = $this->manageLink('cancel.php', $reservation);
        $updateLink = $this->manageLink('update.php', $reservation);

        $body = "Hello {$reservation['fname']},\n\n" .
            "Thank you for your reservation.\n\n" .
            "Details:\n" .
            "Date: {$reservation['reservation_date']}\n" .
            "Time: {$reservation['reservation_time']}\n" .
            "Guests: {$reservation['people']}\n\n" .
            "Manage your reservation:\n" .
            "Cancel Booking: {$cancelLink}\n" .
            "Update Booking: {$updateLink}\n\n" .
            "We look forward to serving you!\n\n{$restaurant}";

        return [$subject, $body];
    }

    private function manageLink(string $script, array $reservation): string
    {
        $id = $reservation['id'];
        $token = $reservation['token'];

        return \url("actions/{$script}?id={$id}&token={$token}");
    }

    private function resolveGuestId(array $data): int
    {
        $email = $data['email'];
        $guest = $this->guests->findByEmail($email);
        $firstName = $data['fname'] ?? '';
        $lastName = $data['lname'] ?? '';
        $phone = $data['phone'] ?? '';

        if ($guest) {
            $this->guests->updateContact((int) $guest['id'], $firstName, $lastName, $phone);
            return (int) $guest['id'];
        }

        return $this->guests->create($firstName, $lastName, $email, $phone);
    }

    private function resolveTableId($tableId): ?int
    {
        if ($tableId === null || $tableId === '') {
            return null;
        }

        $tableId = (int) $tableId;

        if ($tableId <= 0 || $this->tables === null) {
            return null;
        }

        $table = $this->tables->findActiveById($tableId);

        return $table ? $tableId : null;
    }
}