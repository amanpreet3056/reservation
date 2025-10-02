<?php
declare(strict_types=1);

require __DIR__ . '/../../app/bootstrap.php';

use App\Repositories\AdminRepository;
use App\Services\AuthService;

if (is_admin_authenticated()) {
    redirect('admin/dashboard.php');
}

$authService = new AuthService(new AdminRepository(db()), admin_session_key());
$authService->ensureDefaultSuperAdmin(config('auth.default_super_admin'));

$error = null;

if (is_post()) {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: '';
    $password = (string) ($_POST['password'] ?? '');

    remember_old_input(['email' => $email]);

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } elseif ($authService->attempt($email, $password)) {
        redirect('admin/dashboard.php');
    } else {
        $error = 'Invalid credentials.';
    }
}

$oldEmail = old('email');
clear_old_input();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super Admin Login</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #f2f4f7;
        }
        .login-container {
            max-width: 420px;
            margin: 80px auto;
            padding: 40px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }
        .login-container h1 {
            margin-top: 0;
            margin-bottom: 20px;
        }
        .login-container label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }
        .login-container input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d4d4d4;
            border-radius: 6px;
            background: #f5f5f5;
            margin-bottom: 16px;
        }
        .login-container button {
            width: 100%;
            padding: 14px;
            background: #fcb040;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .login-container button:hover {
            background: #e89a1e;
        }
        .login-error {
            color: #c0392b;
            margin-bottom: 16px;
        }
        .back-link {
            margin-top: 16px;
            text-align: center;
        }
        .back-link a {
            color: #2b2b2b;
        }
        .roles-note {
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h1>Admin Login</h1>
    <p class="roles-note">Super Admin, Admin and Manager accounts can access this portal.</p>
    <?php if ($error): ?>
        <div class="login-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($oldEmail, ENT_QUOTES, 'UTF-8'); ?>" required>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Login</button>
    </form>
    <div class="back-link">
        <a href="<?php echo htmlspecialchars(url('index.php'), ENT_QUOTES, 'UTF-8'); ?>">&larr; Back to reservations</a>
    </div>
</div>
</body>
</html>