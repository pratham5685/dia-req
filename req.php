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
                    echo "<p class='alert alert-danger'>No data found for style number: " . htmlspecialchars($styleNumber) . "</p>";
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
                'Gold-Wt(g)',
                'Total-Stone-Qty',
                'Total-Stone-t (ct)',
                'Stone-Type',
                'Stone-Quantity',
                'Stone-Size',
                'Stone-Weight',
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

                    // Write the main bangle data for the first stone
                    fputcsv($output, [
                        htmlspecialchars($data['style_number']),
                        htmlspecialchars($data['factory_style_number']),
                        htmlspecialchars($data['gold_weight']),
                        htmlspecialchars($data['total_stone_qty']),
                        htmlspecialchars($data['total_stone_weight']),
                        htmlspecialchars($stones[0]['stone_type'] ?? 'N/A'),
                        htmlspecialchars($stones[0]['quantity'] ?? 'N/A'),
                        htmlspecialchars($stones[0]['size'] ?? 'N/A'),
                        htmlspecialchars($stones[0]['weight'] ?? 'N/A'),
                        htmlspecialchars($data['submitted']['gold_weight']),
                        htmlspecialchars($data['submitted']['quantity']),
                        htmlspecialchars($data['submitted']['po']),
                        htmlspecialchars($data['submitted']['factory']),
                        htmlspecialchars($data['submitted']['size']),
                        htmlspecialchars($data['submitted']['quality']),
                    ]);

                    // Write additional stone details in new rows
                    for ($i = 1; $i < count($stones); $i++) {
                        fputcsv($output, [
                            '', '', '', '', '', // Leave first columns empty
                            htmlspecialchars($stones[$i]['stone_type'] ?? 'N/A'),
                            htmlspecialchars($stones[$i]['quantity'] ?? 'N/A'),
                            htmlspecialchars($stones[$i]['size'] ?? 'N/A'),
                            htmlspecialchars($stones[$i]['weight'] ?? 'N/A'),
                            '', '', '', '', '', // Leave last columns empty
                        ]);
                    }

                    // Add a blank row between different styles
                    fputcsv($output, ['', '', '', '', '', '', '', '', '', '', '', '', '', '']);
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
    <title>Diamond Requirement Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            background: #343a40;
            padding-top: 20px;
        }
        .sidebar a {
            color: white;
            padding: 10px;
            text-decoration: none;
            display: block;
        }
        .sidebar a:hover {
            background: #575d63;
        }
        .content {
            padding: 20px;
        }
        h1, h2 {
            color: #343a40;
        }
        .card {
            margin-top: 20px;
        }
        .table th, .table td {
            vertical-align: middle;
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

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 sidebar">
            <h2 class="text-white">Dashboard</h2>
            <a href="index.php">Home</a>
    <a href="addbangle.php">Add Bangle</a>
    <a href="view.php">All bangles</a>
    <a href="req.php">Diamond requirement</a>
        </nav>
        
        <main class="col-md-9 content">
            <h1>Diamond Requirement Dashboard</h1>
            <div class="card">
                <div class="card-header">
                    <h5>Submit Diamond Requirements</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <div id="style-entries">
                            <div class="style-entry">
                                <label for="style_number">Style Number:</label>
                                <input type="text" name="style_number[]" class="form-control" required>

                                <label for="gold_weight" class="mt-2">Gold:</label>
                                <select name="gold_weight[]" class="form-control" required>
                                    <option value="">Select Gold Type</option>
                                    <option value="14KY">14KY</option>
                                    <option value="14KW">14KW</option>
                                    <option value="14KR">14KR</option>
                                    <option value="14TT">14TT</option>
                                    <option value="18KY">18KY</option>
                                    <option value="18KW">18KW</option>
                                    <option value="18KR">18KR</option>
                                    <option value="18K-TT">18K-TT</option>
                                    <option value="PT-950">PT-950</option>
                                </select>

                                <label for="quantity" class="mt-2">Quantity:</label>
                                <input type="text" name="quantity[]" class="form-control" required>

                                <label for="po" class="mt-2">Purchase Order (PO):</label>
                                <input type="text" name="po[]" class="form-control" required>

                                <label for="factory" class="mt-2">Factory:</label>
                                <input type="text" name="factory[]" class="form-control" required>

                                <label for="size" class="mt-2">Size:</label>
                                <input type="text" name="size[]" class="form-control" required>

                                <label for="quality" class="mt-2">Quality:</label>
                                <select name="quality[]" class="form-control" required>
                                    <option value="">Select Quality</option>
                                    <option value="Natural">Natural</option>
                                    <option value="Labgrown">Labgrown</option>
                                </select>
                            </div>
                        </div>
                        <button type="button" id="add-style" class="btn btn-secondary mt-3">Add Another Style</button>
                        <button type="submit" class="btn btn-primary mt-3">Submit</button>
                    </form>

                    <!-- Reset Button -->
                    <form method="post" action="" style="margin-top: 20px;">
                        <button type="submit" name="reset" class="btn btn-warning">Reset Form</button>
                    </form>
                </div>
            </div>

            <?php if (!empty($_SESSION['bangleData'])): ?>
                <?php foreach ($_SESSION['bangleData'] as $index => $data): ?>
                    <?php if ($data): ?>
                        <h2>Details for Style Number: <?php echo htmlspecialchars($data['style_number']); ?></h2>
                        <div class="card">
                            <div class="card-body">
                                <table class="table table-bordered">
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
                                                $stoneDetails .= '<table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Stone Type</th>
                                                            <th>Quantity</th>
                                                            <th>Size</th>
                                                            <th>Weight (ct)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>';

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
                                                $stoneDetails .= '</tbody></table>';
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
                            </div>
                        </div>
                    <?php else: ?>
                        <p>No details found for the entered style number: <strong><?php echo htmlspecialchars($data['style_number']); ?></strong>.</p>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <form method="post" action="">
                <button type="submit" name="export_csv" class="btn btn-success mt-3">Export to CSV</button>
            </form>
        </main>
    </div>
</div>

<?php
$conn->close();
?>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    document.getElementById('add-style').addEventListener('click', function() {
        const styleEntries = document.getElementById('style-entries');
        const newEntry = document.createElement('div');
        newEntry.className = 'style-entry mt-2';
        newEntry.innerHTML = ` 
            <label for="style_number">Style Number:</label>
            <input type="text" name="style_number[]" class="form-control" required>
            <label for="gold_weight" class="mt-2">Gold Weight:</label>
            <select name="gold_weight[]" class="form-control" required>
                <option value="">Select Gold Type</option>
                <option value="14KY">14KY</option>
                <option value="14KW">14KW</option>
                <option value="14KR">14KR</option>
                <option value="14TT">14TT</option>
                <option value="18KY">18KY</option>
                <option value="18KW">18KW</option>
                <option value="18KR">18KR</option>
                <option value="18K-TT">18K-TT</option>
                <option value="PT-950">PT-950</option>
            </select>
            <label for="quantity" class="mt-2">Quantity:</label>
            <input type="text" name="quantity[]" class="form-control" required>
            <label for="po" class="mt-2">Purchase Order (PO):</label>
            <input type="text" name="po[]" class="form-control" required>
            <label for="factory" class="mt-2">Factory:</label>
            <input type="text" name="factory[]" class="form-control" required>
            <label for="size" class="mt-2">Size:</label>
            <input type="text" name="size[]" class="form-control" required>
            <label for="quality" class="mt-2">Quality:</label>
            <select name="quality[]" class="form-control" required>
                <option value="">Select Quality</option>
                <option value="Natural">Natural</option>
                <option value="Labgrown">Labgrown</option>
            </select>
        `;
        styleEntries.appendChild(newEntry);
    });
</script>

</body>
</html>
