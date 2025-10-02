<?php
declare(strict_types=1);

use App\Repositories\SettingRepository;
use App\Repositories\TableRepository;

require __DIR__ . '/../app/bootstrap.php';

$restaurantName = config('app.name', "BachstA?b'l");
$activeTables = [];
$connectionError = null;

try {
    $connection = db();
    $settingRepository = new SettingRepository($connection);
    $tableRepository = new TableRepository($connection);

    $restaurantName = $settingRepository->getValue('restaurant.name') ?? $restaurantName;
    $activeTables = $tableRepository->active();
} catch (Throwable $e) {
    $connectionError = $e;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Table Reservation</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="reservation-container">
    <div class="reservation-left">
      <img src="assets/img/menu.jpg" alt="Restaurant Menu" />
    </div>

    <div class="reservation-right">
      <h2>TISCH RESERVIERUNG</h2>
      <p><?php echo htmlspecialchars($restaurantName, ENT_QUOTES, 'UTF-8'); ?></p>
      <p class="preview-link">
        <a href="<?php echo htmlspecialchars(url(), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">Preview on localhost</a>
        <?php if (!is_admin_authenticated()): ?>
          <span class="admin-login">
            <a href="<?php echo htmlspecialchars(url('admin/login.php'), ENT_QUOTES, 'UTF-8'); ?>">Super Admin Login</a>
          </span>
        <?php else: ?>
          <span class="admin-login">
            <a href="<?php echo htmlspecialchars(url('admin/dashboard.php'), ENT_QUOTES, 'UTF-8'); ?>">Dashboard</a>
          </span>
        <?php endif; ?>
      </p>

      <?php if ($connectionError): ?>
        <div class="db-warning" style="padding:12px 16px; margin-bottom:16px; border-radius:6px; background:#fff4f4; color:#8a1a1a;">
          <strong>Database connection problem.</strong> Update <code>config/app.php</code> with valid credentials and apply <code>schema.sql</code>. Error: <?php echo htmlspecialchars($connectionError->getMessage(), ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php endif; ?>

      <form action="actions/submit.php" method="POST" class="reservation-form">
        <div class="form-field">
          <label for="people">Number of People</label>
          <select name="people" id="people" required>
            <option value="2">2 people</option>
            <option value="3">3 people</option>
            <option value="4">4 people</option>
            <option value="5">5 people</option>
            <option value="6">6 people</option>
          </select>
        </div>

        <div class="form-field">
          <label for="date">Date</label>
          <input type="date" name="date" id="date" required>
        </div>

        <div class="form-field">
          <label for="time">Time</label>
          <select name="time" id="time" required>
            <?php
            $times = [
              '11:00 am','11:30 am','12:00 pm','12:30 pm','01:00 pm',
              '01:30 pm','02:00 pm','02:30 pm','03:00 pm','03:30 pm',
              '04:00 pm','04:30 pm','05:00 pm'
            ];
            foreach ($times as $t) {
              $value = htmlspecialchars($t, ENT_QUOTES, 'UTF-8');
              echo "<option value='{$value}'>{$value}</option>";
            }
            ?>
          </select>
        </div>

        <?php if (!empty($activeTables)): ?>
        <div class="form-field">
          <label for="table_id">Table Preference</label>
          <select name="table_id" id="table_id">
            <option value="">No preference</option>
            <?php foreach ($activeTables as $table): ?>
              <option value="<?php echo (int) $table['id']; ?>">
                <?php echo htmlspecialchars($table['name'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo (int) $table['capacity']; ?> seats)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>

        <div class="form-field">
          <label for="fname">First Name</label>
          <input type="text" name="fname" id="fname" required>
        </div>

        <div class="form-field">
          <label for="lname">Last Name</label>
          <input type="text" name="lname" id="lname" required>
        </div>

        <div class="form-field">
          <label for="email">Email</label>
          <input type="email" name="email" id="email" required>
        </div>

        <div class="form-field">
          <label for="phone">Phone</label>
          <input type="text" name="phone" id="phone" required>
        </div>

        <div class="form-field">
          <label for="purpose">Visit Purpose</label>
          <select name="purpose" id="purpose">
            <option value="">Please select visit purpose</option>
            <option value="Dinner">Dinner</option>
            <option value="Birthday">Birthday</option>
            <option value="Business">Business</option>
          </select>
        </div>

        <div class="form-field full-width">
          <label for="message">Message</label>
          <textarea name="message" id="message"></textarea>
        </div>

        <div class="form-field full-width terms">
          <label>
            <input type="checkbox" required> I accept the <a href="#">T&Cs</a> and <a href="#">Privacy Policy</a>.
          </label>
        </div>

        <div class="form-field full-width">
          <button type="submit" class="submit-btn">Reserve Now</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>