<?php
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$item = mysqli_query($conn, "SELECT * FROM food_items WHERE id = $id");
$item = mysqli_fetch_assoc($item);

if (!$item) {
    header("Location: index.php");
    exit();
}

// Get related items
$related = mysqli_query($conn, "SELECT * FROM food_items WHERE category = '{$item['category']}' AND id != $id LIMIT 3");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $item['name']; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .item-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .item-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .item-image img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 15px;
        }
        
        .item-info h1 {
            font-size: 2.5rem;
            color: #1a1a1a;
            margin-bottom: 10px;
        }
        
        .item-category {
            color: #006b3c;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 15px;
        }
        
        .item-price {
            font-size: 2rem;
            color: #006b3c;
            font-weight: bold;
            margin: 20px 0;
        }
        
        .item-description {
            line-height: 1.8;
            color: #666;
            margin: 20px 0;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 30px 0;
        }
        
        .quantity-input {
            width: 80px;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1.1rem;
            text-align: center;
        }
        
        .add-to-cart-btn {
            background: #006b3c;
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .add-to-cart-btn:hover {
            background: #004d2b;
            transform: translateY(-2px);
        }
        
        .special-badge-large {
            background: #ffd700;
            color: #1a1a1a;
            padding: 8px 20px;
            border-radius: 25px;
            display: inline-block;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #006b3c;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .nutrition-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        @media (max-width: 768px) {
            .item-grid {
                grid-template-columns: 1fr;
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

    <div class="item-container">
        <a href="menu.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Menu</a>
        
        <div class="item-grid">
            <div class="item-image">
                <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
            </div>
            
            <div class="item-info">
                <?php if($item['is_special']): ?>
                    <div class="special-badge-large">🔥 Today's Special</div>
                <?php endif; ?>
                
                <div class="item-category"><?php echo $item['category']; ?></div>
                <h1><?php echo $item['name']; ?></h1>
                
                <div class="item-price">R <?php echo number_format($item['price'], 2); ?></div>
                
                <div class="item-description">
                    <?php echo nl2br($item['description']); ?>
                </div>
                
                <div class="nutrition-info">
                    <h3><i class="fas fa-info-circle"></i> Additional Info</h3>
                    <p>✅ Freshly prepared daily</p>
                    <p>✅ Authentic South African recipe</p>
                    <p>✅ Serves 1-2 people</p>
                </div>
                
                <form method="POST" action="cart.php">
                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                    <div class="quantity-selector">
                        <label><strong>Quantity:</strong></label>
                        <input type="number" name="quantity" value="1" min="1" max="10" class="quantity-input">
                        <button type="submit" name="add_to_cart" class="add-to-cart-btn">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </div>
                </form>
                
                <div style="margin-top: 20px; color: #666;">
                    <p><i class="fas fa-clock"></i> Preparation time: 15-20 min</p>
                    <p><i class="fas fa-truck"></i> Free delivery over R<?php echo FREE_DELIVERY_MIN; ?></p>
                </div>
            </div>
        </div>
        
        <!-- Related Items -->
        <?php if (mysqli_num_rows($related) > 0): ?>
        <h2 style="margin: 50px 0 30px;">You Might Also Like</h2>
        <div class="food-grid">
            <?php while($rel = mysqli_fetch_assoc($related)): ?>
            <div class="food-card">
                <img src="<?php echo $rel['image']; ?>" alt="<?php echo $rel['name']; ?>" class="food-image">
                <div class="food-info">
                    <div class="food-category"><?php echo $rel['category']; ?></div>
                    <h3 class="food-name"><?php echo $rel['name']; ?></h3>
                    <div class="food-price">R <?php echo number_format($rel['price'], 2); ?></div>
                    <a href="item.php?id=<?php echo $rel['id']; ?>" class="btn">View Details</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>