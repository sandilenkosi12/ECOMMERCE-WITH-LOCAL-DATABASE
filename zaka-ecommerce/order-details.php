<?php
$pageTitle = 'Order Details';
require_once 'includes/config.php';
require_once 'includes/header.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Get order details
$query = "SELECT o.*, u.full_name, u.email FROM orders o 
          JOIN users u ON o.user_id = u.id 
          WHERE o.id = $order_id AND o.user_id = $user_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    redirect('orders.php');
}

$order = mysqli_fetch_assoc($result);

// Get order items
$items = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = $order_id");
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="orders.php">Orders</a></li>
                    <li class="breadcrumb-item active">Order #<?php echo $order['order_number']; ?></li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Order Status Timeline -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5>Order Status</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-3 text-center">
                            <div class="position-relative">
                                <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-check"></i>
                                </div>
                                <p class="mt-2 mb-0">Order Placed</p>
                                <small class="text-muted"><?php echo date('d M', strtotime($order['created_at'])); ?></small>
                            </div>
                        </div>
                        <div class="col-3 text-center">
                            <div class="position-relative">
                                <div class="rounded-circle <?php echo $order['order_status'] != 'pending' ? 'bg-success' : 'bg-secondary'; ?> text-white d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <p class="mt-2 mb-0">Processing</p>
                            </div>
                        </div>
                        <div class="col-3 text-center">
                            <div class="position-relative">
                                <div class="rounded-circle <?php echo in_array($order['order_status'], ['shipped', 'delivered']) ? 'bg-success' : 'bg-secondary'; ?> text-white d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-truck"></i>
                                </div>
                                <p class="mt-2 mb-0">Shipped</p>
                            </div>
                        </div>
                        <div class="col-3 text-center">
                            <div class="position-relative">
                                <div class="rounded-circle <?php echo $order['order_status'] == 'delivered' ? 'bg-success' : 'bg-secondary'; ?> text-white d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-home"></i>
                                </div>
                                <p class="mt-2 mb-0">Delivered</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5>Order Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $subtotal = 0;
                                while($item = mysqli_fetch_assoc($items)): 
                                    $subtotal += $item['price'] * $item['quantity'];
                                ?>
                                <tr>
                                    <td><?php echo $item['product_name']; ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>R<?php echo number_format($item['price'], 2); ?></td>
                                    <td>R<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td>R<?php echo number_format($subtotal, 2); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                    <td>R<?php echo number_format($order['total_amount'] - $subtotal - ($subtotal * 0.15), 2); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>VAT (15%):</strong></td>
                                    <td>R<?php echo number_format($subtotal * 0.15, 2); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong class="h5">Total:</strong></td>
                                    <td><strong class="h5 text-primary">R<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Order Summary -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5>Order Summary</h5>
                </div>
                <div class="card-body">
                    <p><strong>Order Number:</strong><br> <?php echo $order['order_number']; ?></p>
                    <p><strong>Order Date:</strong><br> <?php echo date('F j, Y H:i', strtotime($order['created_at'])); ?></p>
                    <p><strong>Payment Method:</strong><br> <?php echo ucfirst($order['payment_method']); ?></p>
                    <p><strong>Payment Status:</strong><br> 
                        <span class="badge bg-<?php echo $order['payment_status'] == 'paid' ? 'success' : 'warning'; ?> p-2">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </p>
                    <p><strong>Order Status:</strong><br>
                        <span class="badge bg-<?php echo $status_colors[$order['order_status']] ?? 'secondary'; ?> p-2">
                            <?php echo ucfirst($order['order_status']); ?>
                        </span>
                    </p>
                </div>
            </div>
            
            <!-- Shipping Address -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5>Shipping Address</h5>
                </div>
                <div class="card-body">
                    <p><strong><?php echo $order['full_name']; ?></strong></p>
                    <p><?php echo nl2br($order['shipping_address']); ?></p>
                    <p>Phone: <?php echo $order['phone']; ?></p>
                    <p>Email: <?php echo $order['email']; ?></p>
                </div>
            </div>
            
            <!-- Need Help -->
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <h5>Need Help With This Order?</h5>
                    <p class="mb-2">Contact our support team:</p>
                    <p class="mb-1"><i class="fas fa-phone me-2 text-warning"></i> 060 523 9905</p>
                    <p class="mb-1"><i class="fas fa-envelope me-2 text-warning"></i> abelnkosi2000@gmail.com</p>
                    <hr class="bg-light">
                    <p class="mb-0 small">Quote order #<?php echo $order['order_number']; ?> when contacting us.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>