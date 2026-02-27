<?php
require_once 'config.php';

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header("Location: login.php");
    exit();
}

// Get cart items
$cart_items = [];
$subtotal = 0;
$ids = implode(',', array_keys($_SESSION['cart']));
$result = mysqli_query($conn, "SELECT * FROM food_items WHERE id IN ($ids)");

while ($row = mysqli_fetch_assoc($result)) {
    $row['cart_qty'] = $_SESSION['cart'][$row['id']];
    $cart_items[] = $row;
    $subtotal += $row['price'] * $row['cart_qty'];
}

$delivery = $subtotal >= FREE_DELIVERY_MIN ? 0 : DELIVERY_FEE;
$total = $subtotal + $delivery;

// Get user data
$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));

// Process order
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    
    // Generate order number
    $order_number = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
    
    // Insert order
    $query = "INSERT INTO orders (user_id, order_number, total_amount, shipping_address, phone, payment_method, order_status) 
              VALUES ($user_id, '$order_number', $total, '$address', '$phone', '$payment_method', 'pending')";
    
    if (mysqli_query($conn, $query)) {
        $order_id = mysqli_insert_id($conn);
        
        // Insert order items
        foreach ($cart_items as $item) {
            $product_id = $item['id'];
            $product_name = $item['name'];
            $quantity = $item['cart_qty'];
            $price = $item['price'];
            
            mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, product_name, quantity, price) 
                                 VALUES ($order_id, $product_id, '$product_name', $quantity, $price)");
        }
        
        // Clear cart
        unset($_SESSION['cart']);
        
        // Redirect to success
        header("Location: order-success.php?order=$order_number");
        exit();
    } else {
        $error = "Order failed: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .checkout-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin: 40px 0;
        }
        
        .checkout-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .order-summary {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            position: sticky;
            top: 100px;
            height: fit-content;
        }
        
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .form-section h3 {
            margin-bottom: 20px;
            color: #1a1a1a;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .form-control:focus {
            border-color: #ffd700;
            outline: none;
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .payment-method {
            border: 2px solid #eee;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
        }
        
        .payment-method.active {
            border-color: #ffd700;
            background: #fff9e6;
        }
        
        .payment-method input {
            margin-right: 5px;
        }
        
        .place-order-btn {
            width: 100%;
            padding: 16px;
            background: #006b3c;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            margin: 20px 0;
        }
        
        .place-order-btn:hover {
            background: #004d2b;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }
        
        .total-row {
            font-size: 1.3rem;
            font-weight: bold;
            color: #006b3c;
            border-top: 2px solid #ffd700;
            padding-top: 15px;
            margin-top: 15px;
        }
        
        .secure-badge {
            text-align: center;
            color: #666;
            margin: 15px 0;
        }
        
        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container">
            <a href="index.php" style="text-decoration: none;"><h2 style="color: #ffd700; margin: 0;">ZAKA <span style="color: white;">Food</span></h2></a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="menu.php">Menu</a>
                <a href="cart.php">Cart <span class="cart-count"><?php echo getCartCount(); ?></span></a>
                <a href="logout.php">Logout (<?php echo $_SESSION['user_name']; ?>)</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <h1 class="section-title">Checkout</h1>
        
        <div class="checkout-container">
            <div class="checkout-form">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-section">
                        <h3><i class="fas fa-user"></i> Your Information</h3>
                        <p><strong>Name:</strong> <?php echo $_SESSION['user_name']; ?></p>
                        <p><strong>Email:</strong> <?php echo $_SESSION['user_email']; ?></p>
                    </div>
                    
                    <div class="form-section">
                        <h3><i class="fas fa-map-marker-alt"></i> Delivery Details</h3>
                        
                        <div class="form-group">
                            <label>Delivery Address *</label>
                            <textarea name="address" class="form-control" rows="3" required><?php echo $user['address']; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Phone Number *</label>
                            <input type="tel" name="phone" class="form-control" value="<?php echo $user['phone']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
                        
                        <div class="payment-methods">
                            <label class="payment-method active">
                                <input type="radio" name="payment_method" value="Cash on Delivery" checked> 💵 Cash on Delivery
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="Card"> 💳 Card (Pay at pickup)
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="EFT"> 🏦 EFT
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="SnapScan"> 📱 SnapScan
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="place-order-btn">
                        <i class="fas fa-check-circle"></i> Place Order • R <?php echo number_format($total, 2); ?>
                    </button>
                </form>
            </div>
            
            <div class="order-summary">
                <h3>Order Summary</h3>
                
                <?php foreach ($cart_items as $item): ?>
                <div class="order-item">
                    <span><?php echo $item['name']; ?> x <?php echo $item['cart_qty']; ?></span>
                    <span>R <?php echo number_format($item['price'] * $item['cart_qty'], 2); ?></span>
                </div>
                <?php endforeach; ?>
                
                <div class="order-item">
                    <span>Subtotal:</span>
                    <span>R <?php echo number_format($subtotal, 2); ?></span>
                </div>
                
                <div class="order-item">
                    <span>Delivery:</span>
                    <span><?php echo $delivery == 0 ? 'FREE' : 'R ' . number_format($delivery, 2); ?></span>
                </div>
                
                <div class="total-row">
                    <span>Total:</span>
                    <span>R <?php echo number_format($total, 2); ?></span>
                </div>
                
                <div class="secure-badge">
                    <i class="fas fa-lock"></i> Secure Checkout
                </div>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                    <p><i class="fas fa-clock"></i> Estimated delivery: 30-45 min</p>
                    <p><i class="fas fa-store"></i> Pickup available at <?php echo CONTACT_ADDRESS; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>