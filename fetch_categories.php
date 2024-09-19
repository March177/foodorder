<?php
header('Content-Type: application/json');

// Include the database configuration
include 'config.php';

// Query to fetch categories
$sql = "SELECT category_name FROM categories";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Fetch categories
$categories = array();
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}

// Output categories in JSON format
echo json_encode($categories);

// Close the connection
mysqli_close($conn);
?>
