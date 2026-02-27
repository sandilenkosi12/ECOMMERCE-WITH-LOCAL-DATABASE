<?php
$pageTitle = 'Dashboard';
require_once 'includes/config.php';
require_once 'includes/header.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'dashboard.php';
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get user data
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));

// Get recent orders (last 5)
$recent_orders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");

// Get order statistics
$stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as completed_orders,
    SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(total_amount) as total_spent,
    MAX(created_at) as last_order
    FROM orders WHERE user_id = $user_id"));

// Get wishlist items (if you have a wishlist table)
// $wishlist = mysqli_query($conn, "SELECT p.* FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = $user_id LIMIT 4");

// Get recently viewed products (from session)
$recently_viewed = isset($_SESSION['recently_viewed']) ? $_SESSION['recently_viewed'] : [];
$recent_products = [];
if (!empty($recently_viewed)) {
    $ids = implode(',', array_slice($recently_viewed, 0, 4));
    $recent_products = mysqli_query($conn, "SELECT * FROM products WHERE id IN ($ids)");
}
?>

<div class="container py-5">
    <!-- Welcome Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-2">Welcome back, <?php echo explode(' ', $user['full_name'])[0]; ?>! 👋</h2>
                            <p class="mb-0"><?php echo date('l, F j, Y'); ?></p>
                        </div>
                        <div>
                            <a href="user.php" class="btn btn-dark">
                                <i class="fas fa-user-edit me-2"></i>Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-shopping-bag fa-3x text-warning mb-3"></i>
                    <h3 class="mb-2"><?php echo $stats['total_orders'] ?: 0; ?></h3>
                    <p class="text-muted mb-0">Total Orders</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h3 class="mb-2"><?php echo $stats['completed_orders'] ?: 0; ?></h3>
                    <p class="text-muted mb-0">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-3x text-info mb-3"></i>
                    <h3 class="mb-2"><?php echo $stats['pending_orders'] ?: 0; ?></h3>
                    <p class="text-muted mb-0">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-rand fa-3x text-primary mb-3"></i>
                    <h3 class="mb-2">R<?php echo number_format($stats['total_spent'] ?: 0, 2); ?></h3>
                    <p class="text-muted mb-0">Total Spent</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Recent Orders -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Orders</h5>
                    <a href="orders.php" class="btn btn-sm btn-warning">View All</a>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($recent_orders) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                                    <tr>
                                        <td><strong><?php echo $order['order_number']; ?></strong></td>
                                        <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                        <td>R<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
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
                                            <span class="badge bg-<?php echo $color; ?>">
                                                <?php echo ucfirst($order['order_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <p>No orders yet</p>
                            <a href="shop.php" class="btn btn-warning btn-sm">Start Shopping</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recently Viewed -->
            <?php if (mysqli_num_rows($recent_products) > 0): ?>
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recently Viewed</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php while($product = mysqli_fetch_assoc($recent_products)): ?>
                        <div class="col-md-3">
                            <div class="card h-100">
                                <img src="<?php echo $product['image_url']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>" style="height: 120px; object-fit: cover;">
                                <div class="card-body p-2">
                                    <h6 class="card-title small"><?php echo substr($product['name'], 0, 20); ?>...</h6>
                                    <p class="text-primary small mb-2">R<?php echo $product['price']; ?></p>
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-warning w-100">View</a>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Profile Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <i class="fas fa-user-circle fa-4x text-warning mb-3"></i>
                    <h5><?php echo $user['full_name']; ?></h5>
                    <p class="text-muted mb-2"><i class="fas fa-envelope me-2"></i><?php echo $user['email']; ?></p>
                    <p class="text-muted mb-2"><i class="fas fa-phone me-2"></i><?php echo $user['phone'] ?: 'Not provided'; ?></p>
                    <p class="text-muted mb-3"><i class="fas fa-map-marker me-2"></i><?php echo $user['address'] ?: 'Katlehong, Gauteng'; ?></p>
                    <a href="user.php" class="btn btn-outline-warning btn-sm">Edit Profile</a>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="shop.php" class="btn btn-warning">
                            <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                        </a>
                        <a href="cart.php" class="btn btn-outline-warning">
                            <i class="fas fa-shopping-cart me-2"></i>View Cart
                        </a>
                        <a href="wishlist.php" class="btn btn-outline-warning">
                            <i class="fas fa-heart me-2"></i>Wishlist
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Support Card -->
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <h5><i class="fas fa-headset me-2 text-warning"></i> Need Assistance?</h5>
                    <p class="mb-2">Contact our support team:</p>
                    <p class="mb-1"><i class="fas fa-phone me-2 text-warning"></i> 060 523 9905</p>
                    <p class="mb-1"><i class="fas fa-envelope me-2 text-warning"></i> abelnkosi2000@gmail.com</p>
                    <p><i class="fas fa-map-marker me-2 text-warning"></i> Katlehong, Gauteng</p>
                    <hr class="bg-light">
                    <p class="mb-0 small">Response time: Within 24 hours</p>
                </div>
            </div>
            
            <!-- Last Order Info -->
            <?php if ($stats['last_order']): ?>
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h6>Last Order</h6>
                    <p class="text-muted small"><?php echo date('d M Y', strtotime($stats['last_order'])); ?></p>
                    <a href="orders.php" class="btn btn-sm btn-warning w-100">Track Order</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>