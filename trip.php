<?php
// trip.php – visar/­redigerar en tur eller skapar en ny
header('Content-Type: text/html; charset=utf-8'); // tvinga UTF-8

require 'config.php';
$pdo = db();

/* ==============================================================
   1)  CREATE MODE  ( trip.php?new=1 )
   ==============================================================*/
if (isset($_GET['new'])) {

    /* Formulär skickat?  -> INSERT + redirect */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name  = trim($_POST['trip_name'] ?? '');
        $dest  = trim($_POST['destination'] ?? '');
        $miles = (float)($_POST['sea_miles'] ?? 0);

        if ($name && $dest && $miles > 0) {
            $stmt = $pdo->prepare(
                "INSERT INTO boat_log (trip_name, destination, sea_miles)
                 VALUES (?,?,?)"
            );
            $stmt->execute([$name, $dest, $miles]);

            header('Location: trip.php?id=' . $pdo->lastInsertId());
            exit;
        }
        $error = 'Fyll i alla fält (sjömil > 0).';
    }
    ?>
    <!doctype html>
    <html lang="sv">
    <head>
        <meta charset="utf-8">
        <title>Starta ny trip</title>
        <style>
            body{font-family:sans-serif;margin:2rem;max-width:420px}
            label{display:block;margin:1rem 0 .3rem}
            input[type=text],input[type=number]{width:100%;padding:.4rem}
            .btn{margin-top:1rem;padding:.5rem 1rem;background:#007bff;color:#fff;border:0}
            .error{color:#d00}
        </style>
    </head>
    <body>
        <h1>Starta ny trip</h1>

        <?php if (!empty($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <form method="post">
            <label>Namn på tur</label>
            <input type="text" name="trip_name" required>

            <label>Destination</label>
            <input type="text" name="destination" required>

            <label>Sjömil</label>
            <input type="number" step="0.1" name="sea_miles" required>

            <button class="btn" type="submit">Skapa</button>
        </form>

        <p><a href="index.php">← Tillbaka</a></p>
    </body>
    </html>
    <?php
    exit; // resten gäller bara edit-läget
}

/* ==============================================================
   2)  EDIT MODE  ( trip.php?id=123 )
   ==============================================================*/
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { exit('Ogiltigt id'); }

/* När formuläret sparas – uppdatera raden */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $cols = [
        'chk_weather_route','chk_fuel_oil','chk_engine_start',
        'chk_battery_electrics','chk_bilge_hull','chk_steering_throttle',
        'chk_safety_gear','chk_comms','chk_lines_anchor','chk_food_sunscreen'
    ];
    $data = [];
    foreach ($cols as $c) { $data[$c] = isset($_POST[$c]) ? 1 : 0; }

    $data['weather_summary'] = trim($_POST['weather_summary'] ?? '');
    $data['notes']           = trim($_POST['notes'] ?? '');

    $set = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));
    $stmt = $pdo->prepare("UPDATE boat_log SET $set WHERE id = :id");
    $data['id'] = $id;
    $stmt->execute($data);

    header('Location: trip.php?id=' . $id); // PRG-redirect
    exit;
}

/* Hämta turen för visning */
$stmt = $pdo->prepare("SELECT * FROM boat_log WHERE id = ?");
$stmt->execute([$id]);
$trip = $stmt->fetch();
if (!$trip) { exit('Hittar inte turen'); }

/* Metadata för checklistan */
$checklist = [
  'chk_weather_route'     => ['Väder & rutt',        'Kolla prognosen, meddela någon vart du ska'],
  'chk_fuel_oil'          => ['Bränsle & olja',      'Bensintank ≥ 30 %, olja ≥ 50 %'],
  'chk_engine_start'      => ['Motorstart',          'Dödmansgrepp i, kylvatten OK'],
  'chk_battery_electrics' => ['Batteri & el',        'Huvudbrytare på, > 12,4 V, lanternor OK'],
  'chk_bilge_hull'        => ['Läns & skrov',        'Självlänsar rena, inget läck'],
  'chk_steering_throttle' => ['Styrning & gas',      'Ratt lätt, växel fram/bak OK'],
  'chk_safety_gear'       => ['Flytväst & nöd',      'Flytväst, brandsläckare, kniv'],
  'chk_comms'             => ['Kommunikation',       'Mobil laddad'],
  'chk_lines_anchor'      => ['Tampar & ankare',     'Tampar hela, ankare redo'],
  'chk_food_sunscreen'    => ['Proviant & solskydd', 'Vatten, snacks, solkräm'],
];
?>
<!doctype html>
<html lang="sv">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($trip['trip_name']) ?></title>
    <style>
        body{font-family:sans-serif;margin:2rem;max-width:820px}
        table{border-collapse:collapse;width:100%}
        th,td{padding:.4rem;border:1px solid #ccc;text-align:left;vertical-align:top}
        textarea{width:100%;height:80px;padding:.4rem}
        .btn{padding:.5rem 1rem;background:#28a745;color:#fff;border:0;margin-top:1rem}
    </style>
</head>
<body>

<h1><?= htmlspecialchars($trip['trip_name']) ?> – <?= htmlspecialchars($trip['destination']) ?></h1>
<p><strong>Sjömil:</strong> <?= number_format($trip['sea_miles'], 1, ',', '') ?></p>
<p><strong>Datum:</strong> <?= date('Y-m-d H:i', strtotime($trip['trip_date'])) ?></p>

<form method="post">
    <h2>Väder</h2>
    <textarea name="weather_summary"><?= htmlspecialchars($trip['weather_summary']) ?></textarea>

    <h2>Checklista</h2>
    <table>
        <tr><th>OK?</th><th>Punkt</th><th>Beskrivning</th></tr>
        <?php foreach ($checklist as $col => [$title, $desc]): ?>
            <tr>
                <td style="text-align:center">
                    <input type="checkbox" name="<?= $col ?>" value="1"
                           <?= (int)$trip[$col] === 1 ? 'checked' : '' ?>>
                </td>
                <td><?= $title ?></td>
                <td><?= $desc ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>Anteckningar</h2>
    <textarea name="notes"><?= htmlspecialchars($trip['notes']) ?></textarea>

    <button class="btn" type="submit">💾 Spara</button>
</form>

<p><a href="index.php">← Tillbaka</a></p>
</body>
</html>
