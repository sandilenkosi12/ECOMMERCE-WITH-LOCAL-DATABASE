<?php
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'foodstore_db';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Site Information
define('SITE_NAME', 'ZAKA Food Store');
define('CONTACT_PHONE', '060 523 9905');
define('CONTACT_EMAIL', 'abelnkosi2000@gmail.com');
define('CONTACT_ADDRESS', 'Katlehong, Gauteng');
define('FACEBOOK_URL', 'https://www.facebook.com/sandile.nkosi.142687');
define('DELIVERY_FEE', 35);
define('FREE_DELIVERY_MIN', 200);

// Cart functions
function getCartCount() {
    return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
}

function formatPrice($price) {
    return 'R ' . number_format($price, 2);
}
?>