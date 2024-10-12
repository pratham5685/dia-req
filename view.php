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

// Check if any records were returned
if ($result->num_rows > 0) {
    // Start outputting the HTML
    echo '<h1>Bangle Styles</h1>';
    echo '<table border="1" cellspacing="0" cellpadding="10">';
    echo '<tr>
            <th>Style Number</th>
            <th>Factory Style Number</th>
            <th>Gold Weight (g)</th>
            <th>Total Stone Quantity</th>
            <th>Total Stone Weight (ct)</th>
            <th>Stone Details</th>
          </tr>';

    // Iterate through each record
    while ($row = $result->fetch_assoc()) {
        // Decode the JSON for stone details
        $stones = json_decode($row['stones'], true);
        $stoneDetails = '';

        // Prepare the stone details for display
        if (!empty($stones) && is_array($stones)) {
            $stoneDetails .= '<table border="1" cellspacing="0" cellpadding="5">
                                <tr>
                                    <th>Stone Type</th>
                                    <th>Quantity</th>
                                    <th>Size</th>
                                    <th>Weight (ct)</th>
                                </tr>';

            foreach ($stones as $stone) {
                // Check if required keys exist in the array
                $stoneType = isset($stone['type']) ? htmlspecialchars($stone['type']) : 'N/A';
                $stoneQty = isset($stone['qty']) ? htmlspecialchars($stone['qty']) : 'N/A';
                $stoneSize = isset($stone['size']) ? htmlspecialchars($stone['size']) : 'N/A';
                $stoneWeight = isset($stone['weight']) ? htmlspecialchars($stone['weight']) : 'N/A';

                // Append each stone's details to the stoneDetails table
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

        // Output the main row data
        echo "<tr>
                <td>{$row['style_number']}</td>
                <td>{$row['factory_style_number']}</td>
                <td>{$row['gold_weight']}</td>
                <td>{$row['total_stone_qty']}</td>
                <td>{$row['total_stone_weight']}</td>
                <td>{$stoneDetails}</td>
              </tr>";
    }
    echo '</table>';
} else {
    echo 'No styles found.';
}

$conn->close();
?>
