<?php
header('Content-Type: text/html; charset=utf-8'); // tvinga r�tt charset

/* index.php  � shows latest 9 trips + �new trip� button */
require 'config.php';

$trips = db()->query(
    "SELECT id, trip_name, destination, sea_miles
     FROM boat_log
     ORDER BY trip_date DESC
     LIMIT 9"
)->fetchAll();
?>
<!doctype html>
<html lang="sv">
<head>
  <meta charset="utf-8">
  <title>B�t-logg</title>
  <style>
    body{font-family:sans-serif;margin:2rem}
    table{border-collapse:collapse;width:100%}
    th,td{padding:.5rem;border:1px solid #ccc;text-align:left}
    a{color:#0366d6;text-decoration:none}
    .btn{display:inline-block;background:#28a745;color:#fff;
         padding:.5rem 1rem;margin-bottom:1rem;border-radius:.3rem}
  </style>
</head>
<body>

<!-- NEW!  link goes to trip.php?new=1 -->
<a class="btn" href="trip.php?new=1">? Starta ny trip</a>

<h1>Senaste turerna</h1>
<table>
  <tr><th>Namn</th><th>Destination</th><th>Sj�mil</th></tr>
  <?php foreach ($trips as $t): ?>
    <tr>
      <td><a href="trip.php?id=<?= $t['id'] ?>">
            <?= htmlspecialchars($t['trip_name']) ?></a></td>
      <td><?= htmlspecialchars($t['destination']) ?></td>
      <td><?= number_format($t['sea_miles'], 1, ',', '') ?></td>
    </tr>
  <?php endforeach; ?>
</table>
</body>
</html>
