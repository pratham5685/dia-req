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
        echo "New bangle added successfully!";
    } else {
        echo "Error: " . $stmt->error;
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
    <title>Bangles Input</title>
</head>
<body>
    <h1>Add Bangle</h1>
    <form method="post" id="bangleForm">
        <label for="style_number">Style Number:</label>
        <input type="text" name="style_number" required><br>

        <label for="factory_style_number">Factory Style Number:</label>
        <input type="text" name="factory_style_number"><br>

        <label for="gold_weight">Gold Weight (grams):</label>
        <input type="number" step="0.01" name="gold_weight" required><br>

        <h3>Stones Information</h3>
        <div id="stoneFields">
            <div class="stone">
                <label for="stone_type">Stone Type:</label>
                <input type="text" name="stones[0][stone_type]" required>

                <label for="quantity">Quantity:</label>
                <input type="number" name="stones[0][quantity]" required>

                <label for="size">Size:</label>
                <input type="text" name="stones[0][size]" required>

                <label for="weight">Weight (ct):</label>
                <input type="number" step="0.01" name="stones[0][weight]" required>
            </div>
        </div>

        <button type="button" onclick="addStoneField()">Add More Stones</button><br><br>
        <input type="submit" value="Add Bangle">
    </form>

    <script>
        let stoneCount = 1;

        function addStoneField() {
            const stoneFieldsDiv = document.getElementById('stoneFields');
            const newStoneField = `
                <div class="stone">
                    <label for="stone_type">Stone Type:</label>
                    <input type="text" name="stones[${stoneCount}][stone_type]" required>

                    <label for="quantity">Quantity:</label>
                    <input type="number" name="stones[${stoneCount}][quantity]" required>

                    <label for="size">Size:</label>
                    <input type="text" name="stones[${stoneCount}][size]" required>

                    <label for="weight">Weight (ct):</label>
                    <input type="number" step="0.01" name="stones[${stoneCount}][weight]" required>
                </div>
            `;
            stoneFieldsDiv.insertAdjacentHTML('beforeend', newStoneField);
            stoneCount++;
        }
    </script>
</body>
</html>
