<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit();
}

// Handle cart updates and checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantities'] as $product_id => $quantity) {
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id] = (int)$quantity;
            } else {
                unset($_SESSION['cart'][$product_id]);
            }
        }
    } elseif (isset($_POST['remove_item'])) {
        $product_id = $_POST['product_id'];
        unset($_SESSION['cart'][$product_id]);
    } elseif (isset($_POST['place_order'])) {
        // Create order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$_SESSION['user_id'], $total]);
        $order_id = $pdo->lastInsertId();

        // Add order items
        foreach ($cart_items as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
        }

        // Clear cart
        unset($_SESSION['cart']);

        header('Location: orders.php?order_placed=1');
        exit();
    }
    header('Location: cart.php');
    exit();
}

// Get cart items
$cart_items = [];
$total = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $product) {
        $quantity = $_SESSION['cart'][$product['id']];
        $subtotal = $product['price'] * $quantity;
        $total += $subtotal;

        $cart_items[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image'],
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Whisk by Mae</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #FFF8DC;
            min-height: 100vh;
        }
        .btn-primary {
            background-color: #8B4513;
            border-color: #8B4513;
        }
        .btn-primary:hover {
            background-color: #D2691E;
            border-color: #D2691E;
        }
        .form-control {
            background-color: #3d3d3d;
            border: 1px solid #555;
            color: #ffffff;
        }
        .form-control:focus {
            background-color: #3d3d3d;
            border-color: #8B4513;
            color: #ffffff;
            box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.25);
        }
        .form-control::placeholder {
            color: #cccccc;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-dark" style="background-color: var(--primary-color);">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><img src="../assets/images/logo-removebg-preview.png" alt="Whisk by Mae" height="40"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav me-auto mb-2 mb-md-0">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="cart.php">Cart (<?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">My Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="checkout.php">Checkout</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_profile.php">View Profile</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <a href="../auth/logout.php" class="btn btn-outline-light">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Shopping Cart</h2>
                </div>

                <?php if (empty($cart_items)): ?>
                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted">Your cart is empty. <a href="home.php">Start shopping</a></p>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Cart Items Table -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Cart Items</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Image</th>
                                                <th>Name</th>
                                                <th>Price</th>
                                                <th>Quantity</th>
                                                <th>Subtotal</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($cart_items as $item): ?>
                                                <tr>
                                                    <td>
                                                        <img src="../assets/images/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                                    </td>
                                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                                    <td>
                                                        <input type="number" name="quantities[<?php echo $item['id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" max="10" class="form-control" style="width: 80px;">
                                                    </td>
                                                    <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                                                    <td>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                                            <button type="submit" name="remove_item" class="btn btn-danger btn-sm">Remove</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <h4>Total: $<?php echo number_format($total, 2); ?></h4>
                                    <button type="submit" name="update_cart" class="btn btn-secondary">Update Cart</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Checkout Section -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Checkout</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Order Summary</h6>
                                    <?php foreach ($cart_items as $item): ?>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></span>
                                            <span>$<?php echo number_format($item['subtotal'], 2); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <strong>Total:</strong>
                                        <strong>$<?php echo number_format($total, 2); ?></strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Delivery Details</h6>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                                    $stmt->execute([$_SESSION['user_id']]);
                                    $user = $stmt->fetch();
                                    ?>
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                                    <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <form method="POST" id="checkout-form">
                                    <button type="submit" name="place_order" class="btn btn-primary">Place Order</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to place this order?')) {
                e.preventDefault();
            }
        });
    </script>
    </div>
</body>
</html>
