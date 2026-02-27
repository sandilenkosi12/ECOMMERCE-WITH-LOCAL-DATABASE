<?php
$pageTitle = 'Product Details';
require_once 'includes/config.php';
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.id = $id";
$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    redirect('shop.php');
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-6">
            <img src="<?php echo $product['image_url']; ?>" class="img-fluid rounded shadow" alt="<?php echo $product['name']; ?>">
        </div>
        <div class="col-md-6">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="shop.php">Shop</a></li>
                    <li class="breadcrumb-item active"><?php echo $product['name']; ?></li>
                </ol>
            </nav>
            
            <h1 class="display-5 mb-3"><?php echo $product['name']; ?></h1>
            <p class="lead text-muted mb-3">By <?php echo $product['artisan_name']; ?></p>
            
            <div class="mb-4">
                <span class="badge bg-warning text-dark p-2"><?php echo $product['category_name']; ?></span>
                <span class="badge bg-info text-dark p-2 ms-2">In Stock: <?php echo $product['stock']; ?></span>
            </div>
            
            <h2 class="text-primary mb-4"><?php echo formatPrice($product['price']); ?></h2>
            
            <div class="mb-4">
                <h5>Description</h5>
                <p><?php echo nl2br($product['description']); ?></p>
            </div>
            
            <div class="mb-4">
                <h5>Artisan Story</h5>
                <p>This beautiful piece was handcrafted by <?php echo $product['artisan_name']; ?>, a skilled artisan preserving South African heritage.</p>
            </div>
            
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" id="quantity" class="form-control" value="1" min="1" max="<?php echo $product['stock']; ?>">
                </div>
                <div class="col-md-8 d-flex align-items-end gap-2">
                    <button onclick="addToCartWithQuantity(<?php echo $product['id']; ?>)" class="btn btn-warning btn-lg flex-grow-1">
                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                    </button>
                    <button class="btn btn-outline-secondary btn-lg">
                        <i class="far fa-heart"></i>
                    </button>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row">
                <div class="col-6">
                    <i class="fas fa-truck text-warning me-2"></i> Free shipping over R500
                </div>
                <div class="col-6">
                    <i class="fas fa-shield-alt text-warning me-2"></i> Secure checkout
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php
    $related = mysqli_query($conn, "SELECT * FROM products WHERE category_id = {$product['category_id']} AND id != $id LIMIT 4");
    if (mysqli_num_rows($related) > 0):
    ?>
    <section class="mt-5 pt-5">
        <h3 class="mb-4">You May Also Like</h3>
        <div class="row g-4">
            <?php while($rel = mysqli_fetch_assoc($related)): ?>
            <div class="col-md-3">
                <div class="card h-100">
                    <img src="<?php echo $rel['image_url']; ?>" class="card-img-top" alt="<?php echo $rel['name']; ?>" style="height: 150px; object-fit: cover;">
                    <div class="card-body">
                        <h6 class="card-title"><?php echo $rel['name']; ?></h6>
                        <p class="text-primary fw-bold"><?php echo formatPrice($rel['price']); ?></p>
                        <a href="product.php?id=<?php echo $rel['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<script>
function addToCartWithQuantity(productId) {
    const quantity = document.getElementById('quantity').value;
    addToCart(productId, quantity);
}
</script>

<?php require_once 'includes/footer.php'; ?>