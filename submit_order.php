<?php
// Establish database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "orderdb";
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture form data
    $floor = $_POST['floor'] ?? '';
    $note_to_rider = $_POST['note_to_rider'] ?? '';
    $deliveryMethod = $_POST['delivery'] ?? '';
    $paymentMethod = $_POST['payment'] ?? '';
    $tipAmount = $_POST['tip'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';

    // Decode order items JSON
    $items = json_decode($_POST['order_items'], true);
    if (!is_array($items)) {
        echo "Invalid order items!";
        exit;
    }

    // Calculate totals
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += (float) $item['price'] * (int) $item['quantity'];
    }
    $deliveryFee = ($deliveryMethod == 'priority') ? 19.00 : 0.00;
    $smallOrderFee = ($subtotal < 100) ? 10.00 : 0.00;
    $tipAmount = (float) ($tipAmount ?: 0.00);
    $subtotal = (float) $subtotal;
    $total = $subtotal + $deliveryFee + $smallOrderFee + $tipAmount;

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Insert order data
        $stmt = $conn->prepare("
            INSERT INTO orders (
                name, email, phone, floor, note_to_rider, deliveryMethod, paymentMethod, tipAmount,
                subtotal, deliveryFee, smallOrderFee, total
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "ssssssssssss",
            $name,
            $email,
            $phone,
            $floor,
            $note_to_rider,
            $deliveryMethod,
            $paymentMethod,
            $tipAmount,
            $subtotal,
            $deliveryFee,
            $smallOrderFee,
            $total
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $orderId = $stmt->insert_id; // Get the ID of the inserted order
        $stmt->close();

        // Insert order items data
        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id, itemName, itemPrice, quantity)
            VALUES (?, ?, ?, ?)
        ");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        foreach ($items as $item) {
            $itemName = $item['name'];
            $itemPrice = (float) $item['price'];
            $itemQuantity = (int) $item['quantity'];

            $stmt->bind_param(
                "isdi",
                $orderId,
                $itemName,
                $itemPrice,
                $itemQuantity
            );

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        }

        $stmt->close();
        $conn->commit();

        echo "Order successfully placed!";
    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaction on error
        echo "Error: " . $e->getMessage();
    }

    // Close connection
    $conn->close();
} else {
    echo "No data submitted.";
}
