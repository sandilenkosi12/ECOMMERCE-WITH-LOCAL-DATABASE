<?php
$pageTitle = 'My Profile';
require_once 'includes/config.php';
require_once 'includes/header.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'user.php';
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    $query = "UPDATE users SET full_name='$name', email='$email', phone='$phone', address='$address' WHERE id=$user_id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $success = "Profile updated successfully!";
    } else {
        $error = "Update failed: " . mysqli_error($conn);
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    // Get current password from database
    $result = mysqli_query($conn, "SELECT password FROM users WHERE id=$user_id");
    $user = mysqli_fetch_assoc($result);
    
    if (!password_verify($current, $user['password'])) {
        $password_error = "Current password is incorrect";
    } elseif ($new !== $confirm) {
        $password_error = "New passwords do not match";
    } elseif (strlen($new) < 6) {
        $password_error = "Password must be at least 6 characters";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE id=$user_id");
        $password_success = "Password changed successfully!";
    }
}

// Get user data
$result = mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id");
$user = mysqli_fetch_assoc($result);

// Get user orders
$orders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id=$user_id ORDER BY created_at DESC");
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-circle fa-5x text-warning"></i>
                    </div>
                    <h4><?php echo $user['full_name']; ?></h4>
                    <p class="text-muted">Member since <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                    <hr>
                    <div class="d-grid">
                        <a href="#profile" class="btn btn-outline-warning mb-2" data-bs-toggle="pill">Profile</a>
                        <a href="#orders" class="btn btn-outline-warning mb-2" data-bs-toggle="pill">My Orders</a>
                        <a href="#password" class="btn btn-outline-warning mb-2" data-bs-toggle="pill">Change Password</a>
                        <a href="logout.php" class="btn btn-danger">Logout</a>
                    </div>
                </div>
            </div>
            
            <!-- Contact Info Card -->
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <h5><i class="fas fa-headset me-2 text-warning"></i> Need Help?</h5>
                    <p class="mb-1"><i class="fas fa-phone me-2"></i> 060 523 9905</p>
                    <p class="mb-1"><i class="fas fa-envelope me-2"></i> abelnkosi2000@gmail.com</p>
                    <p><i class="fas fa-map-marker me-2"></i> Katlehong, Gauteng</p>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <div class="tab-content">
                <!-- Profile Tab -->
                <div class="tab-pane fade show active" id="profile">
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning">
                            <h4 class="mb-0">Profile Information</h4>
                        </div>
                        <div class="card-body">
                            <?php if (isset($success)): ?>
                                <div class="alert alert-success"><?php echo $success; ?></div>
                            <?php endif; ?>
                            
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="name" class="form-control" value="<?php echo $user['full_name']; ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" name="phone" class="form-control" value="<?php echo $user['phone']; ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Role</label>
                                        <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Delivery Address</label>
                                    <textarea name="address" class="form-control" rows="3"><?php echo $user['address']; ?></textarea>
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-warning">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Orders Tab -->
                <div class="tab-pane fade" id="orders">
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning">
                            <h4 class="mb-0">My Orders</h4>
                        </div>
                        <div class="card-body">
                            <?php if (mysqli_num_rows($orders) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Order #</th>
                                                <th>Date</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                                <th>Payment</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($order = mysqli_fetch_assoc($orders)): ?>
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
                                                    <span class="badge bg-<?php echo $order['payment_status'] == 'paid' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($order['payment_status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                                    <h5>No orders yet</h5>
                                    <p class="text-muted">Start shopping to see your orders here!</p>
                                    <a href="shop.php" class="btn btn-warning mt-3">Shop Now</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Change Password Tab -->
                <div class="tab-pane fade" id="password">
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning">
                            <h4 class="mb-0">Change Password</h4>
                        </div>
                        <div class="card-body">
                            <?php if (isset($password_success)): ?>
                                <div class="alert alert-success"><?php echo $password_success; ?></div>
                            <?php endif; ?>
                            
                            <?php if (isset($password_error)): ?>
                                <div class="alert alert-danger"><?php echo $password_error; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-control" required minlength="6">
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                                
                                <button type="submit" name="change_password" class="btn btn-warning">
                                    <i class="fas fa-key me-2"></i>Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>