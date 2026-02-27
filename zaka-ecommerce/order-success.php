<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Success - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .success-box {
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .success-box i {
            font-size: 5rem;
            color: #006b3c;
            margin-bottom: 20px;
        }
        .order-number {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="success-box">
        <i class="fas fa-check-circle"></i>
        <h1>Order Placed Successfully!</h1>
        <p>Thank you for ordering from ZAKA Food Store</p>
        <div class="order-number">
            <strong>Order #<?php echo $_GET['order']; ?></strong>
        </div>
        <p>We'll notify you when your order is ready.</p>
        <a href="index.php" class="btn" style="margin-top: 20px;">Continue Shopping</a>
    </div>
</body>
</html>