<?php
$connection = mysqli_connect("localhost", "root", "", "vr_ar_db");

if (!$connection) {
    die("Database connection failed.");
}

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $opinion = $_POST['opinion'];
    $tech = $_POST['technology_type'];

    $query = "INSERT INTO Survey (name, age, opinion, technology_type)
              VALUES ('$name', '$age', '$opinion', '$tech')";
    mysqli_query($connection, $query);
}

$results = mysqli_query($connection, "SELECT * FROM Survey");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Survey Data</title>
    <link rel="stylesheet" href="virtual.css">
</head>

<body>

<header>
    <h1>Survey Data</h1>
    <nav>
        <a href="home.html">Home</a>
        <a href="virtual.html">Virtual</a>
        <a href="augmented.html">Augmented</a>
        <a href="data.php">Data</a>
    </nav>
</header>

<section>

<h2>Submit Survey</h2>

<form action="" method="POST" class="form-box">
    <label>Name:</label><br>
    <input type="text" name="name"><br><br>

    <label>Age:</label><br>
    <input type="number" name="age"><br><br>

    <label>Opinion:</label><br>
    <textarea name="opinion"></textarea><br><br>

    <label>Technology Type:</label><br>
    <select name="technology_type">
        <option>VR</option>
        <option>AR</option>
    </select><br><br>

    <button name="submit">Submit</button>
</form>

<h2>Survey Results</h2>

<table border="1" cellpadding="8">
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Age</th>
    <th>Opinion</th>
    <th>Technology</th>
</tr>

<?php while ($row = mysqli_fetch_assoc($results)) { ?>
<tr>
    <td><?= $row['survey_id'] ?></td>
    <td><?= $row['name'] ?></td>
    <td><?= $row['age'] ?></td>
    <td><?= $row['opinion'] ?></td>
    <td><?= $row['technology_type'] ?></td>
</tr>
<?php } ?>

</table>

</section>

<footer>
    © 2025 VR & AR Research Database
</footer>

</body>
</html>
