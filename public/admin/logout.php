<?php
declare(strict_types=1);

require __DIR__ . '/../../app/bootstrap.php';

use App\Repositories\AdminRepository;
use App\Services\AuthService;

if (is_admin_authenticated()) {
    $authService = new AuthService(new AdminRepository(db()), admin_session_key());
    $authService->logout();
}

session_regenerate_id(true);

redirect('admin/login.php');