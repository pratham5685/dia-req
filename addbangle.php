<?php
// Database connection
$host = 'localhost';
$db = 'jewel'; // Your existing database name
$user = 'root'; // Default XAMPP username
$pass = ''; // Default XAMPP password (usually empty)

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $style_number = $_POST['style_number'];
    $factory_style_number = $_POST['factory_style_number'];
    $gold_weight = $_POST['gold_weight'];
    $stones = json_encode($_POST['stones']); // Convert stones array to JSON
    $total_stone_qty = array_sum(array_column($_POST['stones'], 'quantity'));
    $total_stone_weight = array_sum(array_column($_POST['stones'], 'weight'));

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO bangles (style_number, factory_style_number, gold_weight, stones, total_stone_qty, total_stone_weight) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsid", $style_number, $factory_style_number, $gold_weight, $stones, $total_stone_qty, $total_stone_weight);

    // Execute the statement
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>New bangle added successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Bangle</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar a {
            color: white;
            padding: 15px;
            text-decoration: none;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .main {
            margin-left: 250px;
            padding: 20px;
        }
        h1 {
            color: #343a40;
        }
        .stone {
            border: 1px solid #ced4da;
            padding: 15px;
            margin-top: 15px;
            border-radius: 5px;
            background: #fff;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2 class="text-white text-center">Dashboard</h2>
    <a href="index.php">Home</a>
    <a href="addbangle.php">Add Bangle</a>
    <a href="view.php">All bangles</a>
    <a href="req.php">Diamond requirement</a>
</div>

<div class="main">
    <h1>Add Bangle</h1>
    <form method="post" id="bangleForm">
        <div class="form-group">
            <label for="style_number">Style Number:</label>
            <input type="text" name="style_number" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="factory_style_number">Factory Style Number:</label>
            <input type="text" name="factory_style_number" class="form-control">
        </div>

        <div class="form-group">
            <label for="gold_weight">Gold Weight (grams):</label>
            <input type="number" step="0.01" name="gold_weight" class="form-control" required>
        </div>

        <h3>Stones Information</h3>
        <div id="stoneFields">
            <div class="stone">
                <label for="stone_type">Stone Type:</label>
                <input type="text" name="stones[0][stone_type]" class="form-control" required>

                <label for="quantity">Quantity:</label>
                <input type="number" name="stones[0][quantity]" class="form-control" required>

                <label for="size">Size:</label>
                <input type="text" name="stones[0][size]" class="form-control" required>

                <label for="weight">Weight (ct):</label>
                <input type="number" step="0.01" name="stones[0][weight]" class="form-control" required>
            </div>
        </div>

        <button type="button" class="btn btn-secondary mt-3" onclick="addStoneField()">Add More Stones</button><br><br>
        <input type="submit" value="Add Bangle" class="btn btn-primary mt-3">
    </form>
</div>

<script>
    let stoneCount = 1;

    function addStoneField() {
        const stoneFieldsDiv = document.getElementById('stoneFields');
        const newStoneField = `
            <div class="stone">
                <label for="stone_type">Stone Type:</label>
                <input type="text" name="stones[${stoneCount}][stone_type]" class="form-control" required>

                <label for="quantity">Quantity:</label>
                <input type="number" name="stones[${stoneCount}][quantity]" class="form-control" required>

                <label for="size">Size:</label>
                <input type="text" name="stones[${stoneCount}][size]" class="form-control" required>

                <label for="weight">Weight (ct):</label>
                <input type="number" step="0.01" name="stones[${stoneCount}][weight]" class="form-control" required>
            </div>
        `;
        stoneFieldsDiv.insertAdjacentHTML('beforeend', newStoneField);
        stoneCount++;
    }
</script>

</body>
</html>
