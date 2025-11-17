<?php
// data.php - debug-friendly: creates DB/table if missing, inserts, lists rows

$dbHost = 'mysql5011.site4now.net';   // use 127.0.0.1 to avoid socket vs TCP ambiguity
$dbUser = 'mark13';
$dbPass = 'jesussavior13';            // default for XAMPP
$dbName = 'mark13_virtual_augmented_data';
$dbPort = 3306;          // default MySQL port

// --- 0) Quick runtime checks for XAMPP users ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1) Try connecting to MySQL server (no DB selected)
$connection = @mysqli_connect($dbHost, $dbUser, $dbPass, '', $dbPort);

if (!$connection) {
    die("<strong>MySQL connection failed.</strong><br>"
        ."Check that MySQL is running in XAMPP.<br>"
        ."Error: " . htmlspecialchars(mysqli_connect_error()));
}

// 2) Create database if it does not exist
$createDbSql = "CREATE DATABASE IF NOT EXISTS `" . mysqli_real_escape_string($connection, $dbName) . "`
                CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
if (!mysqli_query($connection, $createDbSql)) {
    die("Failed to create database: " . htmlspecialchars(mysqli_error($connection)));
}

// 3) Select the database explicitly
if (!mysqli_select_db($connection, $dbName)) {
    die("Failed to select database '{$dbName}': " . htmlspecialchars(mysqli_error($connection)));
}

// 4) Set charset
if (!mysqli_set_charset($connection, 'utf8mb4')) {
    die("Failed to set charset: " . htmlspecialchars(mysqli_error($connection)));
}

// 5) Create table if not exists
$createTableSql = "
CREATE TABLE IF NOT EXISTS `Survey` (
    `survey_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `age` INT NOT NULL,
    `opinion` TEXT NOT NULL,
    `technology_type` VARCHAR(10) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (!mysqli_query($connection, $createTableSql)) {
    die("Failed to create table: " . htmlspecialchars(mysqli_error($connection)));
}

// 6) Handle POST safely
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $name = trim($_POST['name'] ?? '');
    $age  = trim($_POST['age'] ?? '');
    $op  = trim($_POST['opinion'] ?? '');
    $tech = trim($_POST['technology_type'] ?? '');

    if ($name === '' || $age === '' || $op === '' || $tech === '') {
        $error = "All fields are required.";
    } elseif (!is_numeric($age) || (int)$age < 0) {
        $error = "Please enter a valid age.";
    } else {
        $ageInt = (int)$age;
        $stmt = mysqli_prepare($connection, "INSERT INTO `Survey` (name, age, opinion, technology_type) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            $error = "Prepare failed: " . htmlspecialchars(mysqli_error($connection));
        } else {
            mysqli_stmt_bind_param($stmt, "siss", $name, $ageInt, $op, $tech);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Survey saved successfully.";
            } else {
                $error = "Insert failed: " . htmlspecialchars(mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// 7) Fetch rows
$results = mysqli_query($connection, "SELECT * FROM `Survey` ORDER BY survey_id DESC");
if ($results === false) {
    die("Query failed: " . htmlspecialchars(mysqli_error($connection)));
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Survey DB (debug)</title>
<style>
body{font-family:Arial; padding:18px; background:#f6fbff}
.form-box{background:#fff;padding:16px;border-radius:8px;width:640px}
table{border-collapse:collapse;margin-top:14px;width:90%}
th,td{border:1px solid #ccc;padding:8px;text-align:left}
.message{padding:10px;margin-bottom:10px;border-radius:6px}
.error{background:#ffe6e6;color:#900}
.success{background:#e6ffe6;color:#060}
.debug{font-size:13px;color:#444;background:#fff;padding:8px;border:1px dashed #ccc;margin-bottom:12px}
</style>
</head>
<body>

<h2>Survey Database (debug)</h2>

<div class="debug">
<strong>Server:</strong> <?= htmlspecialchars($dbHost) ?> (port <?= (int)$dbPort ?>) &nbsp;|&nbsp;
<strong>User:</strong> <?= htmlspecialchars($dbUser) ?> &nbsp;|&nbsp;
<strong>Database:</strong> <?= htmlspecialchars($dbName) ?>
</div>

<?php if ($error): ?>
    <div class="message error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="message success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="post" class="form-box">
    <label>Name<br><input name="name" required style="width:100%"></label><br><br>
    <label>Age<br><input name="age" type="number" required style="width:100%"></label><br><br>
    <label>Opinion<br><textarea name="opinion" required style="width:100%;height:90px"></textarea></label><br><br>
    <label>Technology<br>
        <select name="technology_type" required>
            <option value="VR">VR</option>
            <option value="AR">AR</option>
        </select>
    </label><br><br>
    <button type="submit" name="submit">Submit</button>
</form>

<h3>Saved Responses</h3>
<table>
<tr><th>ID</th><th>Name</th><th>Age</th><th>Opinion</th><th>Tech</th><th>Created</th></tr>
<?php while ($row = mysqli_fetch_assoc($results)): ?>
<tr>
    <td><?= (int)$row['survey_id'] ?></td>
    <td><?= htmlspecialchars($row['name']) ?></td>
    <td><?= (int)$row['age'] ?></td>
    <td><?= nl2br(htmlspecialchars($row['opinion'])) ?></td>
    <td><?= htmlspecialchars($row['technology_type']) ?></td>
    <td><?= htmlspecialchars($row['created_at']) ?></td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>
