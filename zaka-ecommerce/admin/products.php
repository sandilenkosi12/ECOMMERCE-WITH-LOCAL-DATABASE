<?php
$pageTitle = 'Manage Products';
require_once '../includes/config.php';

if (!isAdmin()) {
    redirect('../login.php');
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM products WHERE id = $id");
    redirect('products.php');
}

$products = mysqli_query($conn, "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
?>

<?php require_once '../includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h1>Manage Products</h1>
        </div>
        <div class="col text-end">
            <a href="product-add.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Add New Product
            </a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($p = mysqli_fetch_assoc($products)): ?>
                    <tr>
                        <td><?php echo $p['id']; ?></td>
                        <td>
                            <img src="<?php echo $p['image_url']; ?>" style="width: 50px; height: 50px; object-fit: cover;" alt="">
                        </td>
                        <td><?php echo $p['name']; ?></td>
                        <td><?php echo $p['category_name']; ?></td>
                        <td>R<?php echo number_format($p['price'], 2); ?></td>
                        <td><?php echo $p['stock']; ?></td>
                        <td><?php echo $p['featured'] ? 'Yes' : 'No'; ?></td>
                        <td>
                            <a href="product-edit.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete product?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>