<?php
declare(strict_types=1);

require __DIR__ . '/../../app/bootstrap.php';

use App\Repositories\AdminRepository;
use App\Services\AuthService;

require_admin_auth();

$authService = new AuthService(new AdminRepository(db()), admin_session_key());
$currentAdmin = $authService->user();
$currentAdminRole = $currentAdmin['role'] ?? current_admin_role();
$currentAdminName = $currentAdmin['name'] ?? ($_SESSION['admin_name'] ?? 'Admin');