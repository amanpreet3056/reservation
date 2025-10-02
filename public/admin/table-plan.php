<?php
declare(strict_types=1);

use App\Repositories\TableRepository;
use App\Services\TableService;

require __DIR__ . '/init.php';

$tableRepository = new TableRepository(db());
$tableService = new TableService($tableRepository);

$successMessage = flash('table_success');
$errorMessage = null;

if (is_post()) {
    $name = trim((string) ($_POST['name'] ?? ''));
    $capacity = (int) ($_POST['capacity'] ?? 0);
    $location = trim((string) ($_POST['location_hint'] ?? ''));
    $status = (string) ($_POST['status'] ?? 'active');
    $notes = trim((string) ($_POST['notes'] ?? ''));

    try {
        $tableService->create($name, $capacity, $location !== '' ? $location : null, $status, $notes !== '' ? $notes : null);
        flash('table_success', 'Table created successfully.');
        redirect('admin/table-plan.php');
    } catch (\InvalidArgumentException $e) {
        $errorMessage = $e->getMessage();
    } catch (\Throwable $e) {
        $errorMessage = 'Unable to create table right now.';
    }
}

$tables = $tableService->all();

$pageTitle = 'Table Plan';
$activeNav = 'tables';
$activeSubnav = null;

include __DIR__ . '/partials/header.php';
?>
<div class="form-card">
    <h2>Create Table</h2>
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-grid">
            <div>
                <label for="name">Table Name</label>
                <input type="text" name="name" id="name" required>
            </div>
            <div>
                <label for="capacity">Capacity</label>
                <input type="number" name="capacity" id="capacity" min="1" value="2" required>
            </div>
            <div>
                <label for="location_hint">Location Hint</label>
                <input type="text" name="location_hint" id="location_hint" placeholder="e.g. Window, Patio">
            </div>
            <div>
                <label for="status">Status</label>
                <select name="status" id="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div>
            <label for="notes">Notes</label>
            <textarea name="notes" id="notes" rows="3" placeholder="Optional details"></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Add Table</button>
        </div>
    </form>
</div>

<div class="table-wrapper">
    <table>
        <thead>
        <tr>
            <th>Name</th>
            <th>Capacity</th>
            <th>Location</th>
            <th>Status</th>
            <th>Notes</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($tables)): ?>
            <tr>
                <td colspan="5">No tables defined yet.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($tables as $table): ?>
                <tr>
                    <td><?php echo htmlspecialchars($table['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo (int) $table['capacity']; ?></td>
                    <td><?php echo htmlspecialchars($table['location_hint'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($table['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($table['notes'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/partials/footer.php';