<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accept_order'])) {
        $order_id = $_POST['order_id'];
        $stmt = $pdo->prepare("UPDATE orders SET status = 'accepted' WHERE id = ?");
        $stmt->execute([$order_id]);
    } elseif (isset($_POST['mark_done'])) {
        $order_id = $_POST['order_id'];
        $stmt = $pdo->prepare("UPDATE orders SET status = 'done' WHERE id = ?");
        $stmt->execute([$order_id]);
    }
    header('Location: orders.php');
    exit();
}

// Get orders by status
$pending_orders = $pdo->query("
    SELECT o.*, u.name as customer_name, u.email, u.address,
           GROUP_CONCAT(CONCAT(oi.quantity, 'x ', p.name) SEPARATOR ', ') as items
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.status = 'pending'
    GROUP BY o.id
    ORDER BY o.created_at DESC
")->fetchAll();

$accepted_orders = $pdo->query("
    SELECT o.*, u.name as customer_name, u.email, u.address,
           GROUP_CONCAT(CONCAT(oi.quantity, 'x ', p.name) SEPARATOR ', ') as items
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.status = 'accepted'
    GROUP BY o.id
    ORDER BY o.created_at DESC
")->fetchAll();

$done_orders = $pdo->query("
    SELECT o.*, u.name as customer_name, u.email, u.address,
           GROUP_CONCAT(CONCAT(oi.quantity, 'x ', p.name) SEPARATOR ', ') as items
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.status = 'done'
    GROUP BY o.id
    ORDER BY o.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Whisk by Mae</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="orders.php">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="history.php">Order History</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="customers.php">Customers</a>
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
                    <h2 class="mb-0">Manage Orders</h2>
                </div>

                <!-- Orders Tabs -->
                <ul class="nav nav-tabs" id="orderTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab" aria-controls="pending" aria-selected="true">Pending Orders</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="accepted-tab" data-bs-toggle="tab" data-bs-target="#accepted" type="button" role="tab" aria-controls="accepted" aria-selected="false">Accepted Orders</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="done-tab" data-bs-toggle="tab" data-bs-target="#done" type="button" role="tab" aria-controls="done" aria-selected="false">Done Orders</button>
                    </li>
                </ul>
                <div class="tab-content" id="orderTabsContent">
                    <!-- Pending Orders Tab -->
                    <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">Pending Orders</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($pending_orders)): ?>
                                    <p class="text-muted">No pending orders.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Customer Name</th>
                                                    <th>Email</th>
                                                    <th>Address</th>
                                                    <th>Items</th>
                                                    <th>Total</th>
                                                    <th>Ordered On</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pending_orders as $order): ?>
                                                    <tr>
                                                        <td><?php echo $order['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($order['email']); ?></td>
                                                        <td><?php echo htmlspecialchars($order['address']); ?></td>
                                                        <td><?php echo htmlspecialchars($order['items']); ?></td>
                                                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                                        <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                                        <td>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                                <button type="submit" name="accept_order" class="btn btn-success btn-sm">Accept Order</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Accepted Orders Tab -->
                    <div class="tab-pane fade" id="accepted" role="tabpanel" aria-labelledby="accepted-tab">
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">Accepted Orders</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($accepted_orders)): ?>
                                    <p class="text-muted">No accepted orders.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Customer Name</th>
                                                    <th>Email</th>
                                                    <th>Address</th>
                                                    <th>Items</th>
                                                    <th>Total</th>
                                                    <th>Ordered On</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($accepted_orders as $order): ?>
                                                    <tr>
                                                        <td><?php echo $order['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($order['email']); ?></td>
                                                        <td><?php echo htmlspecialchars($order['address']); ?></td>
                                                        <td><?php echo htmlspecialchars($order['items']); ?></td>
                                                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                                        <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                                        <td>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                                <button type="submit" name="mark_done" class="btn btn-primary btn-sm">Mark as Done</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Done Orders Tab -->
                    <div class="tab-pane fade" id="done" role="tabpanel" aria-labelledby="done-tab">
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">Done Orders</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($done_orders)): ?>
                                    <p class="text-muted">No done orders.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Customer Name</th>
                                                    <th>Email</th>
                                                    <th>Address</th>
                                                    <th>Items</th>
                                                    <th>Total</th>
                                                    <th>Ordered On</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($done_orders as $order): ?>
                                                    <tr>
                                                        <td><?php echo $order['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($order['email']); ?></td>
                                                        <td><?php echo htmlspecialchars($order['address']); ?></td>
                                                        <td><?php echo htmlspecialchars($order['items']); ?></td>
                                                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                                        <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                                        <td><span class="badge bg-success">Done</span></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
