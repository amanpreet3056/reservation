<?php
/** @var string $pageTitle */
/** @var string $activeNav */
/** @var string|null $activeSubnav */
/** @var string $currentAdminName */
/** @var string|null $currentAdminRole */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Dashboard', ENT_QUOTES, 'UTF-8'); ?> - Admin</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url('assets/css/admin.css'), ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="admin-content">
        <header class="admin-header">
            <div class="welcome">
                <div>
                    <h1><?php echo htmlspecialchars($pageTitle ?? 'Dashboard', ENT_QUOTES, 'UTF-8'); ?></h1>
                    <?php if (!empty($currentAdminRole)): ?>
                        <span class="role"><?php echo ucfirst(htmlspecialchars($currentAdminRole, ENT_QUOTES, 'UTF-8')); ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <?php echo htmlspecialchars($currentAdminName ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?>
                </div>
            </div>
        </header>
        <main class="admin-body">