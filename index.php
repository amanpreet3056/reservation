<?php
// Simple config
$restaurant_name = "BachstA?b'l";
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
      <p><?php echo $restaurant_name; ?></p>

      <form action="actions/submit.php" method="POST" class="reservation-form">
        
        <label>Number of People</label>
        <select name="people" required>
          <option value="2">2 people</option>
          <option value="3">3 people</option>
          <option value="4">4 people</option>
          <option value="5">5 people</option>
          <option value="6">6 people</option>
        </select>

        <label>Date</label>
        <input type="date" name="date" required>

        <label>Time</label>
        <select name="time" required>
          <?php
          $times = ["11:00 am","11:30 am","12:00 pm","12:30 pm","01:00 pm",
                    "01:30 pm","02:00 pm","02:30 pm","03:00 pm","03:30 pm",
                    "04:00 pm","04:30 pm","05:00 pm"];
          foreach($times as $t){
            echo "<option value='$t'>$t</option>";
          }
          ?>
        </select>

        <label>First Name</label>
        <input type="text" name="fname" required>

        <label>Last Name</label>
        <input type="text" name="lname" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Phone</label>
        <input type="text" name="phone" required>

        <label>Visit Purpose</label>
        <select name="purpose">
          <option value="">Please select visit purpose</option>
          <option value="Dinner">Dinner</option>
          <option value="Birthday">Birthday</option>
          <option value="Business">Business</option>
        </select>

        <label>Message</label>
        <textarea name="message"></textarea>

        <div class="terms">
          <input type="checkbox" required> I accept the <a href="#">T&Cs</a> and <a href="#">Privacy Policy</a>.
        </div>

        <button type="submit" class="submit-btn">Reserve Now</button>
      </form>
    </div>
  </div>
</body>
</html>
