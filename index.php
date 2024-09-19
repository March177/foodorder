<?php
include 'config.php'; // Adjust the path as needed

// Fetch categories for the sidebar
$query = "SELECT id, category_name FROM categories"; // Adjust the table name and columns as needed
$categoriesResult = $conn->query($query);

if (!$categoriesResult) {
    die("Error: " . $conn->error);
}

// Define $selectedCategory based on user input
$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Fetch menu items if a category is selected
if ($selectedCategory > 0) {
    $menuQuery = "SELECT id, menu_name, price, image_path FROM menu WHERE category_id = ?";
    $stmt = $conn->prepare($menuQuery);
    $stmt->bind_param("i", $selectedCategory);
    $stmt->execute();
    $menuResult = $stmt->get_result();
    $stmt->close();
}

// Handle form submission for placing an order
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $location = $_POST['address'];
    $number = $_POST['number'];

    // Prepare and bind the SQL statement
    $stmt = $conn->prepare("INSERT INTO `order` (or_name, or_address, or_number) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $location, $number);

    if ($stmt->execute()) {
        echo "Order submitted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Ordering System</title>
    <link rel="stylesheet" href="ak.css"> <!-- Adjust the path as needed -->
</head>
<body>
    <header>
        <div class="logo">Restaurant</div>
        <div class="cart-icon">
            <img src="images/cart.png" alt="Cart" />
            <span id="cart-count">0</span>
        </div>
    </header>

    <div class="container">
        <aside class="sidebar">
            <ul>
                <?php while ($category = $categoriesResult->fetch_assoc()): ?>
                    <li data-category="<?php echo $category['id']; ?>">
                        <a href="?category=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </aside>

        <main class="main-content">
            <div id="products" class="product-grid">
                <?php if ($selectedCategory > 0): ?>
                    <?php while ($menuItem = $menuResult->fetch_assoc()): ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($menuItem['image_path']); ?>" class="product-image" alt="<?php echo htmlspecialchars($menuItem['menu_name']); ?>">
                            <div class="product-info">
                                <h4 class="product-name"><?php echo htmlspecialchars($menuItem['menu_name']); ?></h4>
                                <span class="product-price">₱<?php echo number_format($menuItem['price'], 2); ?></span>
                                <button class="add-to-cart-btn" data-id="<?php echo $menuItem['id']; ?>" data-name="<?php echo htmlspecialchars($menuItem['menu_name']); ?>" data-price="<?php echo number_format($menuItem['price'], 2); ?>" data-image="<?php echo htmlspecialchars($menuItem['image_path']); ?>">Buy</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Please select a category to view menu items.</p>
                <?php endif; ?>
            </div>
        </main>

        <!-- Order Summary Section -->
        <aside class="order-summary">
            <div class="order-summary-content">
                <h2>Your Orders</h2>
                <div class="order-options">
                    <div class="order-option" data-type="delivery">
                        <span>Delivery</span>
                        <small>Get your order delivered</small>
                    </div>
                    <div class="order-option" data-type="pickup">
                        <span>Pick-up</span>
                        <small>Pick up from store</small>
                    </div>
                </div>

                <div id="order-items">
                    <!-- Order items will be added here -->
                </div>
                <div class="total">Total: <span id="total-price">₱0.00</span></div>
                <button id="order-now" class="order-now-button">Order Now</button>
            </div>
        </aside>
    </div>

    <div id="orderPopup" class="order-popup-overlay">
    <div class="order-popup">
        <span class="close-popup">&times;</span>
        <h2>Order Details</h2>
        <div id="popup-order-items">
            <!-- Order items will be displayed here -->
        </div>
        <div class="popup-footer">
            <!-- Form with the continue button -->
            <form id="order-form" action="ordercard.php" method="post">
                <input type="hidden" name="order_items" id="order-items-data">
                <input type="hidden" name="name" id="popup-name">
                <input type="hidden" name="address" id="popup-address">
                <input type="hidden" name="number" id="popup-number">
                <button id="checkout-button" class="checkout-btn" type="submit">CHECKOUT</button>
            </form>

            <button class="order-more-btn">ORDER MORE</button>
        </div>
    </div>
</div>


  

    <script src="script.js"></script>
    <script>
    // JavaScript to handle "Order Now" and "Continue" button clicks
    document.addEventListener('DOMContentLoaded', function() {
        const orderItems = []; // This will hold the order items

        document.querySelectorAll('.add-to-cart-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const price = parseFloat(this.getAttribute('data-price'));
                const image = this.getAttribute('data-image');
                const quantity = 1; // Default quantity

                // Add item to the order
                orderItems.push({ id, name, price, quantity, image });

                // Update the order summary
                updateOrderSummary();
            });
        });

        document.getElementById('order-now').addEventListener('click', function() {
            document.getElementById('orderPopup').style.display = 'block';
            const orderItemsData = JSON.stringify(orderItems);
            document.getElementById('order-items-data').value = orderItemsData;

            // Collect user information and set hidden fields in the popup form
            document.getElementById('popup-name').value = 'User Name'; // Replace with actual user input
            document.getElementById('popup-address').value = 'User Address'; // Replace with actual user input
            document.getElementById('popup-number').value = 'User Number'; // Replace with actual user input

            const popupOrderItems = document.getElementById('popup-order-items');
            popupOrderItems.innerHTML = ''; // Clear previous items

            orderItems.forEach(item => {
                const itemElement = document.createElement('div');
                itemElement.innerHTML = `
                    <p>${item.quantity} x ${item.name} <span>₱${(item.price * item.quantity).toFixed(2)}</span></p>
                `;
                popupOrderItems.appendChild(itemElement);
            });
        });

        document.querySelector('.close-popup').addEventListener('click', function() {
            document.getElementById('orderPopup').style.display = 'none';
        });

        document.querySelector('.order-more-btn').addEventListener('click', function() {
            document.getElementById('orderPopup').style.display = 'none';
        });

        function updateOrderSummary() {
            let total = 0;
            const orderItemsContainer = document.getElementById('order-items');
            orderItemsContainer.innerHTML = '';

            orderItems.forEach(item => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;

                const itemElement = document.createElement('div');
                itemElement.innerHTML = `
                    <p>${item.quantity} x ${item.name} <span>₱${itemTotal.toFixed(2)}</span></p>
                `;
                orderItemsContainer.appendChild(itemElement);
            });

            document.getElementById('total-price').innerText = `₱${total.toFixed(2)}`;
        }
    });
    </script>
</body>
</html>
