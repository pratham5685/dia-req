<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start(); // Start session for storing data

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

// Initialize variables
$bangleData = [];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle reset action
    if (isset($_POST['reset'])) {
        session_destroy(); // Clear session data
        header("Location: " . $_SERVER['PHP_SELF']); // Reload the page
        exit;
    }

    // Check if 'style_number' is set and is an array
    if (isset($_POST['style_number']) && is_array($_POST['style_number'])) {
        foreach ($_POST['style_number'] as $index => $styleNumber) {
            $goldWeight = $_POST['gold_weight'][$index] ?? '';
            $quantity = $_POST['quantity'][$index] ?? '';
            $po = $_POST['po'][$index] ?? '';
            $factory = $_POST['factory'][$index] ?? '';
            $size = $_POST['size'][$index] ?? '';
            $quality = $_POST['quality'][$index] ?? '';

            // Fetch data from the bangles table based on the style number
            if (!empty($styleNumber)) {
                $sql = "SELECT * FROM bangles WHERE style_number = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $styleNumber);
                $stmt->execute();
                $result = $stmt->get_result();

                // Fetch the data if it exists
                if ($result->num_rows > 0) {
                    $bangleData[$index] = $result->fetch_assoc();
                    // Include submitted data in the fetched array
                    $bangleData[$index]['submitted'] = [
                        'gold_weight' => $goldWeight,
                        'quantity' => $quantity,
                        'po' => $po,
                        'factory' => $factory,
                        'size' => $size,
                        'quality' => $quality,
                    ];
                } else {
                    echo "<p>No data found for style number: " . htmlspecialchars($styleNumber) . "</p>";
                }
            }
        }
        // Store bangle data in session for export
        $_SESSION['bangleData'] = $bangleData;
    }

    // Handle CSV export
    if (isset($_POST['export_csv'])) {
        if (!empty($_SESSION['bangleData'])) {
            $bangleData = $_SESSION['bangleData']; // Retrieve data from session

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="bangle_details.csv"');

            // Open output stream
            $output = fopen('php://output', 'w');

            // Write CSV header
            fputcsv($output, [
                'Style No',
                'Fac-Style No',
                'Gold Wt(g)',
                'Total Stone Qty',
                'Total Stone Wt (ct)',
                'Stone Details',
                'Gold',
                'Qty',
                'PO',
                'Factory',
                'Size',
                'Quality',
            ]);

            // Write each row to the CSV
            foreach ($bangleData as $data) {
                if ($data) {
                    // Prepare stone details for CSV
                    $stones = json_decode($data['stones'], true);
                    $stoneDetails = [];

                    if (!empty($stones) && is_array($stones)) {
                        foreach ($stones as $stone) {
                            $stoneType = htmlspecialchars($stone['stone_type'] ?? 'N/A');
                            $stoneQty = htmlspecialchars($stone['quantity'] ?? 'N/A');
                            $stoneSize = htmlspecialchars($stone['size'] ?? 'N/A');
                            $stoneWeight = htmlspecialchars($stone['weight'] ?? 'N/A');

                            $stoneDetails[] = "{$stoneType} (Qty: {$stoneQty}, Size: {$stoneSize}, Wt: {$stoneWeight} ct)";
                        }
                    }

                    // Join stone details into a single string
                    $stoneDetailsStr = implode('; ', $stoneDetails);

                    // Write the data to the CSV
                    fputcsv($output, [
                        htmlspecialchars($data['style_number']),
                        htmlspecialchars($data['factory_style_number']),
                        htmlspecialchars($data['gold_weight']),
                        htmlspecialchars($data['total_stone_qty']),
                        htmlspecialchars($data['total_stone_weight']),
                        $stoneDetailsStr,
                        htmlspecialchars($data['submitted']['gold_weight']),
                        htmlspecialchars($data['submitted']['quantity']),
                        htmlspecialchars($data['submitted']['po']),
                        htmlspecialchars($data['submitted']['factory']),
                        htmlspecialchars($data['submitted']['size']),
                        htmlspecialchars($data['submitted']['quality']),
                    ]);
                }
            }

            fclose($output);
            exit; // Prevent further script execution
        } else {
            echo "<script>alert('No data available for export. Please submit the form first.');</script>";
        }
    }
}

// Fetch all bangle styles for display
$sql = "SELECT * FROM bangles";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diamond Requirement Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        h1, h2, h3 {
            color: #333;
        }
        .style-entry {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<h1>Diamond Requirement Form</h1>
<form method="post" action="">
    <div id="style-entries">
        <div class="style-entry">
            <label for="style_number">Style Number:</label>
            <input type="text" name="style_number[]" required>

            <label for="gold_weight">Gold Weight:</label>
            <input type="text" name="gold_weight[]" required>

            <label for="quantity">Quantity:</label>
            <input type="text" name="quantity[]" required>

            <label for="po">Purchase Order (PO):</label>
            <input type="text" name="po[]" required>

            <label for="factory">Factory:</label>
            <input type="text" name="factory[]" required>

            <label for="size">Size:</label>
            <input type="text" name="size[]" required>

            <label for="quality">Quality:</label>
            <input type="text" name="quality[]" required>
        </div>
    </div>
    <button type="button" id="add-style">Add Another Style</button>
    <button type="submit">Submit</button>
</form>

<!-- Reset Button -->
<form method="post" action="" style="margin-top: 20px;">
    <button type="submit" name="reset">Reset Form</button>
</form>

<?php if (!empty($_SESSION['bangleData'])): ?>
    <?php foreach ($_SESSION['bangleData'] as $index => $data): ?>
        <?php if ($data): ?>
            <h2>Details for Style Number: <?php echo htmlspecialchars($data['style_number']); ?></h2>
            <table>
                <tr>
                    <th>Style No</th>
                    <th>Fac-Style No</th>
                    <th>Gold Wt(g)</th>
                    <th>Total Stone Qty</th>
                    <th>Total Stone Wt (ct)</th>
                    <th>Stone Details</th>
                    <th>Gold</th>
                    <th>Qty</th>
                    <th>PO</th>
                    <th>Factory</th>
                    <th>Size</th>
                    <th>Quality</th>
                </tr>
                <tr>
                    <td><?php echo htmlspecialchars($data['style_number']); ?></td>
                    <td><?php echo htmlspecialchars($data['factory_style_number']); ?></td>
                    <td><?php echo htmlspecialchars($data['gold_weight']); ?></td>
                    <td><?php echo htmlspecialchars($data['total_stone_qty']); ?></td>
                    <td><?php echo htmlspecialchars($data['total_stone_weight']); ?></td>
                    <td>
                        <?php
                        // Decode the JSON for stone details
                        $stones = json_decode($data['stones'], true);
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
                                $stoneType = htmlspecialchars($stone['stone_type'] ?? 'N/A');
                                $stoneQty = htmlspecialchars($stone['quantity'] ?? 'N/A');
                                $stoneSize = htmlspecialchars($stone['size'] ?? 'N/A');
                                $stoneWeight = htmlspecialchars($stone['weight'] ?? 'N/A');

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

                        // Display stone details
                        echo $stoneDetails;
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($data['submitted']['gold_weight']); ?></td>
                    <td><?php echo htmlspecialchars($data['submitted']['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($data['submitted']['po']); ?></td>
                    <td><?php echo htmlspecialchars($data['submitted']['factory']); ?></td>
                    <td><?php echo htmlspecialchars($data['submitted']['size']); ?></td>
                    <td><?php echo htmlspecialchars($data['submitted']['quality']); ?></td>
                </tr>
            </table>
        <?php else: ?>
            <p>No details found for the entered style number: <strong><?php echo htmlspecialchars($data['style_number']); ?></strong>.</p>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<form method="post" action="">
    <button type="submit" name="export_csv">Export to CSV</button>
</form>

<?php
$conn->close();
?>

<script>
    document.getElementById('add-style').addEventListener('click', function() {
        const styleEntries = document.getElementById('style-entries');
        const newEntry = document.createElement('div');
        newEntry.className = 'style-entry';
        newEntry.innerHTML = ` 
            <label for="style_number">Style Number:</label>
            <input type="text" name="style_number[]" required>
            <label for="gold_weight">Gold Weight:</label>
            <input type="text" name="gold_weight[]" required>
            <label for="quantity">Quantity:</label>
            <input type="text" name="quantity[]" required>
            <label for="po">Purchase Order (PO):</label>
            <input type="text" name="po[]" required>
            <label for="factory">Factory:</label>
            <input type="text" name="factory[]" required>
            <label for="size">Size:</label>
            <input type="text" name="size[]" required>
            <label for="quality">Quality:</label>
            <input type="text" name="quality[]" required>
        `;
        styleEntries.appendChild(newEntry);
    });
</script>

</body>
</html>
