<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$dbname = "jewel";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetching data from the bangles table
$sql = "SELECT * FROM bangles";
$result = $conn->query($sql);

// Start outputting the HTML
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bangle Styles</title>
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
        table {
            margin-top: 20px;
            width: 100%;
        }
        th, td {
            text-align: left;
        }
        .stone-table {
            margin-top: 10px;
            border-collapse: collapse;
            width: 100%;
        }
        .stone-table th, .stone-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
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
    <h1>Bangle Styles</h1>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Style Number</th>
                    <th>Factory Style Number</th>
                    <th>Gold Weight (g)</th>
                    <th>Total Stone Quantity</th>
                    <th>Total Stone Weight (ct)</th>
                    <th>Stone Details</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    // Decode the JSON for stone details
                    $stones = json_decode($row['stones'], true);
                    $stoneDetails = '';
                    
                    // Prepare the stone details for display
                    if (!empty($stones) && is_array($stones)) {
                        $stoneDetails .= '<table class="stone-table">
                                            <tr>
                                                <th>Stone Type</th>
                                                <th>Quantity</th>
                                                <th>Size</th>
                                                <th>Weight (ct)</th>
                                            </tr>';

                        foreach ($stones as $stone) {
                            $stoneType = htmlspecialchars($stone['stone_type'] ?? 'N/A');
                            $stoneQty = htmlspecialchars($stone['quantity'] ?? 'N/A');
                            $stoneSize = htmlspecialchars($stone['size'] ?? 'N/A');
                            $stoneWeight = htmlspecialchars($stone['weight'] ?? 'N/A');

                            $stoneDetails .= "<tr>
                                                <td>{$stoneType}</td>
                                                <td>{$stoneQty}</td>
                                                <td>{$stoneSize}</td>
                                                <td>{$stoneWeight}</td>
                                              </tr>";
                        }
                        $stoneDetails .= '</table>';
                    } else {
                        $stoneDetails = 'No stone details available';
                    }
                    ?>

                    <tr>
                        <td><?= htmlspecialchars($row['style_number']) ?></td>
                        <td><?= htmlspecialchars($row['factory_style_number']) ?></td>
                        <td><?= htmlspecialchars($row['gold_weight']) ?></td>
                        <td><?= htmlspecialchars($row['total_stone_qty']) ?></td>
                        <td><?= htmlspecialchars($row['total_stone_weight']) ?></td>
                        <td><?= $stoneDetails ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No styles found.</p>
    <?php endif; ?>

    <?php $conn->close(); ?>
</div>

</body>
</html>
