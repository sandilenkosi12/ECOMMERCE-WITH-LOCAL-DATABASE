<?php
require_once 'config.php';

// Get food items from database
$result = mysqli_query($conn, "SELECT * FROM food_items ORDER BY is_special DESC, category");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <style>
        /* Copy ALL your CSS from the working HTML file here */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .header {
            background: linear-gradient(135deg, #006b3c, #004d2b);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        
        .header h1 span {
            color: #ffd700;
        }
        
        .contact-bar {
            background: #ffd700;
            color: #1a1a1a;
            padding: 15px;
            text-align: center;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .contact-bar i {
            margin: 0 10px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .section-title {
            text-align: center;
            margin: 40px 0;
            font-size: 2.2rem;
            color: #1a1a1a;
            position: relative;
        }
        
        .section-title:after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: #ffd700;
            margin: 15px auto;
        }
        
        .food-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }
        
        .food-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .food-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .food-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }
        
        .food-info {
            padding: 20px;
        }
        
        .food-category {
            color: #006b3c;
            font-size: 0.85rem;
            text-transform: uppercase;
            font-weight: bold;
        }
        
        .food-name {
            font-size: 1.4rem;
            margin: 10px 0;
            color: #1a1a1a;
        }
        
        .food-description {
            color: #666;
            margin: 10px 0;
            line-height: 1.5;
        }
        
        .food-price {
            font-size: 1.6rem;
            color: #006b3c;
            font-weight: bold;
            margin: 15px 0;
        }
        
        .btn {
            display: inline-block;
            background: #ffd700;
            color: #1a1a1a;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s;
            width: 100%;
            text-align: center;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            background: #e6c200;
            transform: translateY(-2px);
        }
        
        .special-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ffd700;
            color: #1a1a1a;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .about-section {
            background: white;
            padding: 50px 0;
            margin: 40px 0;
        }
        
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }
        
        .about-image img {
            width: 100%;
            border-radius: 15px;
        }
        
        .about-content h2 {
            color: #006b3c;
            margin-bottom: 20px;
        }
        
        .about-content p {
            margin: 15px 0;
            line-height: 1.6;
        }
        
        .feature-list {
            list-style: none;
            margin: 20px 0;
        }
        
        .feature-list li {
            margin: 10px 0;
        }
        
        .feature-list i {
            color: #ffd700;
            margin-right: 10px;
        }
        
        .footer {
            background: #1a1a1a;
            color: white;
            padding: 50px 0 20px;
            margin-top: 50px;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }
        
        .footer h3 {
            color: #ffd700;
            margin-bottom: 20px;
        }
        
        .footer p {
            margin: 10px 0;
        }
        
        .social-links a {
            color: white;
            font-size: 1.5rem;
            margin-right: 15px;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .social-links a:hover {
            color: #ffd700;
        }
        
        .footer-bottom {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #333;
        }
        
        .hours-badge {
            background: #ffd700;
            color: #1a1a1a;
            padding: 10px 20px;
            border-radius: 50px;
            display: inline-block;
            font-weight: bold;
        }
        
        .cart-count {
            background: #ffd700;
            color: #1a1a1a;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 0.8rem;
            margin-left: 5px;
        }
        
        .navbar {
            background: #1a1a1a;
            padding: 10px 0;
        }
        
        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }
        
        .nav-links a:hover {
            color: #ffd700;
        }
        
        @media (max-width: 768px) {
            .about-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .navbar .container {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            </div>
        </div>
    </div>
    
    <!-- Header -->
    <div class="header">
        <h1>ZAKA <span>Food Store</span></h1>
        <p style="font-size: 1.2rem;">Authentic South African Cuisine</p>
    </div>
    
    <!-- Contact Bar -->
    <div class="contact-bar">
        <i class="fas fa-phone"></i> <?php echo CONTACT_PHONE; ?>
        <i class="fas fa-envelope"></i> <?php echo CONTACT_EMAIL; ?>
        <i class="fas fa-map-marker-alt"></i> <?php echo CONTACT_ADDRESS; ?>
    </div>
    
    <div class="container">
        <!-- Opening Hours -->
        <div style="text-align: center; margin: 20px 0;">
            <span class="hours-badge">
                <i class="fas fa-clock"></i> Open Daily: 11:00 AM - 10:00 PM
            </span>
        </div>
        
        <h2 class="section-title">Our South African Menu</h2>
        
        <!-- Food Grid from Database -->
        <div class="food-grid">
            <?php while($item = mysqli_fetch_assoc($result)): ?>
            <div class="food-card" style="position: relative;">
                <?php if($item['is_special']): ?>
                    <div class="special-badge">🔥 Today's Special</div>
                <?php endif; ?>
                <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="food-image">
                <div class="food-info">
                    <div class="food-category"><?php echo $item['category']; ?></div>
                    <h3 class="food-name"><?php echo $item['name']; ?></h3>
                    <p class="food-description"><?php echo substr($item['description'], 0, 80); ?>...</p>
                    <div class="food-price">R <?php echo number_format($item['price'], 2); ?></div>
                    <a href="item.php?id=<?php echo $item['id']; ?>" class="btn"><i class="fas fa-info-circle"></i> View Details</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <!-- About Section -->
    <div class="about-section">
        <div class="container">
            <div class="about-grid">
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800" alt="South African Food">
                </div>
                <div class="about-content">
                    <h2>Welcome to ZAKA Food Store</h2>
                    <p>We bring the authentic taste of South Africa to Katlehong. From traditional braai favorites to Cape Malay curries, every dish is prepared with love and authentic recipes passed down through generations.</p>
                    
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> Fresh ingredients daily</li>
                        <li><i class="fas fa-check-circle"></i> Traditional family recipes</li>
                        <li><i class="fas fa-check-circle"></i> Free delivery over R<?php echo FREE_DELIVERY_MIN; ?></li>
                        <li><i class="fas fa-check-circle"></i> Catering available for events</li>
                    </ul>
                    
                    <div style="background: #f0f0f0; padding: 20px; border-radius: 10px; margin-top: 20px;">
                        <p><strong><i class="fas fa-store"></i> Visit Us:</strong> <?php echo CONTACT_ADDRESS; ?></p>
                        <p><strong><i class="fas fa-clock"></i> Hours:</strong> Monday - Sunday: 11:00 AM - 10:00 PM</p>
                        <p><strong><i class="fas fa-truck"></i> Delivery:</strong> R<?php echo DELIVERY_FEE; ?> (Free over R<?php echo FREE_DELIVERY_MIN; ?>)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h3>ZAKA Food Store</h3>
                    <p>Authentic South African cuisine made with love and traditional recipes.</p>
                    <div style="margin-top: 20px;">
                        <i class="fas fa-clock" style="color: #ffd700;"></i> Mon-Sun: 11am-10pm<br>
                        <i class="fas fa-truck" style="color: #ffd700;"></i> Free delivery over R<?php echo FREE_DELIVERY_MIN; ?>
                    </div>
                </div>
                
                <div>
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-phone" style="color: #ffd700;"></i> <?php echo CONTACT_PHONE; ?></p>
                    <p><i class="fas fa-envelope" style="color: #ffd700;"></i> <?php echo CONTACT_EMAIL; ?></p>
                    <p><i class="fas fa-map-marker-alt" style="color: #ffd700;"></i> <?php echo CONTACT_ADDRESS; ?></p>
                    <p><i class="fas fa-whatsapp" style="color: #ffd700;"></i> <?php echo CONTACT_PHONE; ?> (WhatsApp)</p>
                </div>
                
                <div>
                    <h3>Follow Us</h3>
                    <div class="social-links">
                        <a href="<?php echo FACEBOOK_URL; ?>" target="_blank"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-whatsapp"></i></a>
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <h3>Payment Methods</h3>
                        <p>Cash | Card | EFT | Cash on Delivery</p>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> ZAKA Food Store - Created by Sandile Abel Nkosi. All rights reserved.</p>
                <p style="margin-top: 10px; color: #ffd700;">🇿🇦 Proudly South African 🇿🇦</p>
            </div>
        </div>
    </div>
</body>
</html>