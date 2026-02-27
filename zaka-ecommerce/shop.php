<?php
$pageTitle = 'Shop';
require_once 'includes/config.php';
require_once 'includes/header.php';

// Get filter parameters
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build query
$query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";

if ($category > 0) {
    $query .= " AND p.category_id = $category";
}

if (!empty($search)) {
    $query .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%' OR p.artisan_name LIKE '%$search%')";
}

// Sorting
switch($sort) {
    case 'price_low':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'name':
        $query .= " ORDER BY p.name ASC";
        break;
    default:
        $query .= " ORDER BY p.created_at DESC";
}

$products = mysqli_query($conn, $query);
$categories = mysqli_query($conn, "SELECT * FROM categories");
?>

<div class="container py-4">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Filters</h5>
                    
                    <!-- Search -->
                    <form method="GET" class="mb-4">
                        <label class="form-label">Search</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search products...">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                    
                    <!-- Categories -->
                    <h6 class="mt-4">Categories</h6>
                    <div class="list-group">
                        <a href="shop.php" class="list-group-item list-group-item-action <?php echo $category == 0 ? 'active' : ''; ?>">
                            All Categories
                        </a>
                        <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                        <a href="shop.php?category=<?php echo $cat['id']; ?>" class="list-group-item list-group-item-action <?php echo $category == $cat['id'] ? 'active' : ''; ?>">
                            <?php echo $cat['name']; ?>
                        </a>
                        <?php endwhile; ?>
                    </div>
                    
                    <!-- Sort -->
                    <h6 class="mt-4">Sort By</h6>
                    <select class="form-select" onchange="window.location.href='shop.php?sort='+this.value<?php echo $category ? '+&category='.$category : ''; ?>">
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest</option>
                        <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Products</h2>
                <span class="text-muted"><?php echo mysqli_num_rows($products); ?> products found</span>
            </div>
            
            <div class="row g-4">
                <?php if (mysqli_num_rows($products) > 0): ?>
                    <?php while($product = mysqli_fetch_assoc($products)): ?>
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm">
                            <img src="<?php echo $product['image_url']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <span class="badge bg-secondary mb-2"><?php echo $product['category_name']; ?></span>
                                <h5 class="card-title"><?php echo $product['name']; ?></h5>
                                <p class="text-muted small">By <?php echo $product['artisan_name']; ?></p>
                                <p class="h5 text-primary"><?php echo formatPrice($product['price']); ?></p>
                                <div class="d-grid gap-2">
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary">View Details</a>
                                    <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-warning">Add to Cart</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                        <h3>No products found</h3>
                        <p class="text-muted">Try adjusting your search or filter</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>