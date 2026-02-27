<?php
$pageTitle = 'My Orders';
require_once 'includes/config.php';
require_once 'includes/header.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'orders.php';
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get filter parameters
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$date_filter = isset($_GET['date']) ? sanitize($_GET['date']) : '';

// Build query
$query = "SELECT * FROM orders WHERE user_id = $user_id";

if (!empty($status_filter)) {
    $query .= " AND order_status = '$status_filter'";
}

if (!empty($date_filter)) {
    $query .= " AND DATE(created_at) = '$date_filter'";
}

$query .= " ORDER BY created_at DESC";

$orders = mysqli_query($conn, $query);

// Get order statistics
$stats_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
    SUM(total_amount) as total_spent
    FROM orders WHERE user_id = $user_id";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));
?>

<div class="container py-5">
    <h1 class="mb-4">My Orders</h1>
    
    <!-- Order Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6>Total Orders</h6>
                    <h2><?php echo $stats['total_orders'] ?: 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Completed</h6>
                    <h2><?php echo $stats['completed'] ?: 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6>Total Spent</h6>
                    <h2>R<?php echo number_format($stats['total_spent'] ?: 0, 2); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6>Avg Order Value</h6>
                    <h2>R<?php echo $stats['total_orders'] ? number_format($stats['total_spent'] / $stats['total_orders'], 2) : '0.00'; ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Filter by Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Orders</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo $status_filter == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $status_filter == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filter by Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo $date_filter; ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-warning me-2">Apply Filters</button>
                    <a href="orders.php" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Orders List -->
    <?php if (mysqli_num_rows($orders) > 0): ?>
        <div class="row">
            <?php while($order = mysqli_fetch_assoc($orders)): 
                // Get order items
                $items_query = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = {$order['id']}");
            ?>
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Order #<?php echo $order['order_number']; ?></strong>
                            <span class="text-muted ms-3">Placed on <?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></span>
                        </div>
                        <div>
                            <?php
                            $status_colors = [
                                'pending' => 'warning',
                                'processing' => 'info',
                                'shipped' => 'primary',
                                'delivered' => 'success',
                                'cancelled' => 'danger'
                            ];
                            $color = $status_colors[$order['order_status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?php echo $color; ?> p-2"><?php echo ucfirst($order['order_status']); ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Items:</h6>
                                <ul class="list-unstyled">
                                    <?php while($item = mysqli_fetch_assoc($items_query)): ?>
                                    <li class="mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span><?php echo $item['product_name']; ?> x <?php echo $item['quantity']; ?></span>
                                            <span class="text-primary">R<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                        </div>
                                    </li>
                                    <?php endwhile; ?>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <div class="border-start ps-3">
                                    <p><strong>Total:</strong> <span class="text-primary h5">R<?php echo number_format($order['total_amount'], 2); ?></span></p>
                                    <p><strong>Payment:</strong> 
                                        <span class="badge bg-<?php echo $order['payment_status'] == 'paid' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </p>
                                    <p><strong>Delivery:</strong> <?php echo $order['shipping_address']; ?></p>
                                    <p><strong>Phone:</strong> <?php echo $order['phone']; ?></p>
                                    
                                    <!-- Order Timeline -->
                                    <div class="mt-3">
                                        <small class="text-muted">Order Timeline:</small>
                                        <ul class="list-unstyled mt-2">
                                            <li><i class="fas fa-check-circle text-success me-2"></i>Order Placed: <?php echo date('d M H:i', strtotime($order['created_at'])); ?></li>
                                            <?php if($order['order_status'] != 'pending'): ?>
                                            <li><i class="fas fa-check-circle text-success me-2"></i>Processing Started</li>
                                            <?php endif; ?>
                                            <?php if($order['order_status'] == 'shipped' || $order['order_status'] == 'delivered'): ?>
                                            <li><i class="fas fa-truck text-primary me-2"></i>Shipped</li>
                                            <?php endif; ?>
                                            <?php if($order['order_status'] == 'delivered'): ?>
                                            <li><i class="fas fa-home text-success me-2"></i>Delivered</li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                    
                                    <a href="order-invoice.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="fas fa-download"></i> Download Invoice
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Need help with this order? Contact us: 060 523 9905</small>
                            <?php if($order['order_status'] == 'pending'): ?>
                            <a href="cancel-order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this order?')">
                                Cancel Order
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-box-open fa-5x text-muted mb-4"></i>
            <h3>No orders found</h3>
            <p class="text-muted">You haven't placed any orders yet.</p>
            <?php if(!empty($status_filter) || !empty($date_filter)): ?>
                <p class="text-muted">Try clearing your filters.</p>
                <a href="orders.php" class="btn btn-warning mt-3">Clear Filters</a>
            <?php else: ?>
                <a href="shop.php" class="btn btn-warning btn-lg mt-3">Start Shopping</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>