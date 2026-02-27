<?php
require_once 'config.php';

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to cart
if (isset($_POST['add_to_cart'])) {
    $id = (int)$_POST['item_id'];
    $qty = (int)$_POST['quantity'];
    
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] += $qty;
    } else {
        $_SESSION['cart'][$id] = $qty;
    }
    
    header("Location: cart.php");
    exit();
}

// Remove from cart
if (isset($_GET['remove'])) {
    $id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$id]);
    header("Location: cart.php");
    exit();
}

// Update cart
if (isset($_POST['update'])) {
    foreach ($_POST['qty'] as $id => $qty) {
        if ($qty > 0) {
            $_SESSION['cart'][$id] = $qty;
        } else {
            unset($_SESSION['cart'][$id]);
        }
    }
    header("Location: cart.php");
    exit();
}

// Get cart items from database
$cart_items = [];
$subtotal = 0;

if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $result = mysqli_query($conn, "SELECT * FROM food_items WHERE id IN ($ids)");
    
    while ($row = mysqli_fetch_assoc($result)) {
        $row['cart_qty'] = $_SESSION['cart'][$row['id']];
        $cart_items[] = $row;
        $subtotal += $row['price'] * $row['cart_qty'];
    }
}

$delivery = $subtotal >= FREE_DELIVERY_MIN ? 0 : DELIVERY_FEE;
$total = $subtotal + $delivery;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .cart-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin: 40px 0;
        }
        
        .cart-items {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto auto;
            gap: 20px;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        
        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .cart-summary {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            position: sticky;
            top: 100px;
            height: fit-content;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
        }
        
        .total-row {
            font-size: 1.3rem;
            font-weight: bold;
            color: #006b3c;
            border-top: 2px solid #ffd700;
            padding-top: 15px;
            margin-top: 15px;
        }
        
        .checkout-btn {
            background: #006b3c;
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            margin: 20px 0;
        }
        
        .checkout-btn:hover {
            background: #004d2b;
        }
        
        .empty-cart {
            text-align: center;
            padding: 50px;
        }
        
        .empty-cart i {
            font-size: 5rem;
            color: #ffd700;
            margin-bottom: 20px;
        }
        
        .qty-input {
            width: 60px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
        }
        
        .remove-link {
            color: #dc3545;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .delivery-progress {
            background: #f0f0f0;
            height: 10px;
            border-radius: 5px;
            margin: 15px 0;
            overflow: hidden;
        }
        
        .delivery-bar {
            height: 100%;
            background: #ffd700;
            width: <?php echo min(100, ($subtotal / FREE_DELIVERY_MIN) * 100); ?>%;
        }
        
        @media (max-width: 768px) {
            .cart-container {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                grid-template-columns: 80px 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <div class="navbar">
        <div class="container">
            <a href="index.php" style="text-decoration: none;"><h2 style="color: #ffd700; margin: 0;">ZAKA <span style="color: white;">Food</span></h2></a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="menu.php">Menu</a>
                <a href="cart.php">Cart <span class="cart-count"><?php echo getCartCount(); ?></span></a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="orders.php">My Orders</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="container">
        <h1 class="section-title">Your Shopping Cart</h1>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any delicious South African food yet!</p>
                <a href="menu.php" class="btn" style="width: auto; padding: 12px 40px;">Browse Menu</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="cart-container">
                    <div class="cart-items">
                        <h3>Cart Items (<?php echo count($cart_items); ?>)</h3>
                        
                        <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                            <div>
                                <h4><?php echo $item['name']; ?></h4>
                                <p class="food-category"><?php echo $item['category']; ?></p>
                                <p class="food-price" style="font-size: 1.1rem;">R <?php echo number_format($item['price'], 2); ?> each</p>
                            </div>
                            <div>
                                <input type="number" name="qty[<?php echo $item['id']; ?>]" value="<?php echo $item['cart_qty']; ?>" min="0" max="10" class="qty-input">
                            </div>
                            <div style="text-align: right;">
                                <strong>R <?php echo number_format($item['price'] * $item['cart_qty'], 2); ?></strong>
                                <br>
                                <a href="?remove=<?php echo $item['id']; ?>" class="remove-link"><i class="fas fa-trash"></i> Remove</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <button type="submit" name="update" class="btn" style="margin-top: 20px;">Update Cart</button>
                    </div>
                    
                    <div class="cart-summary">
                        <h3>Order Summary</h3>
                        
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <strong>R <?php echo number_format($subtotal, 2); ?></strong>
                        </div>
                        
                        <div class="summary-row">
                            <span>Delivery Fee:</span>
                            <strong><?php echo $delivery == 0 ? 'FREE' : 'R ' . number_format($delivery, 2); ?></strong>
                        </div>
                        
                        <?php if ($subtotal < FREE_DELIVERY_MIN): ?>
                            <div class="delivery-progress">
                                <div class="delivery-bar"></div>
                            </div>
                            <p style="font-size: 0.9rem; color: #006b3c;">
                                Add R <?php echo number_format(FREE_DELIVERY_MIN - $subtotal, 2); ?> more for FREE delivery
                            </p>
                        <?php endif; ?>
                        
                        <div class="total-row">
                            <span>Total:</span>
                            <span>R <?php echo number_format($total, 2); ?></span>
                        </div>
                        
                        <a href="checkout.php" class="checkout-btn">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </a>
                        
                        <div style="font-size: 0.9rem; color: #666;">
                            <p><i class="fas fa-clock"></i> Estimated delivery: 30-45 min</p>
                            <p><i class="fas fa-map-marker-alt"></i> Pickup available at <?php echo CONTACT_ADDRESS; ?></p>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>