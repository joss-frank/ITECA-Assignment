<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../auth/login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];
$success = "";
$error = "";


if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM products WHERE product_id = $del_id AND seller_id = $seller_id");
    $success = "Product deleted successfully.";
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.seller_id = $seller_id";
if ($filter !== 'all') $sql .= " AND p.status = '" . $conn->real_escape_string($filter) . "'";
$sql .= " ORDER BY p.created_at DESC";
$products = $conn->query($sql);


$count_all = $conn->query("SELECT COUNT(*) as c FROM products WHERE seller_id = $seller_id")->fetch_assoc()['c'];
$count_approved = $conn->query("SELECT COUNT(*) as c FROM products WHERE seller_id = $seller_id AND status = 'approved'")->fetch_assoc()['c'];
$count_pending = $conn->query("SELECT COUNT(*) as c FROM products WHERE seller_id = $seller_id AND status = 'pending'")->fetch_assoc()['c'];
$count_rejected = $conn->query("SELECT COUNT(*) as c FROM products WHERE seller_id = $seller_id AND status = 'rejected'")->fetch_assoc()['c'];

include '../includes/header.php';
?>

<style>
.seller-wrap { padding: 40px 50px; }
.seller-wrap h1 { font-size: 1.8rem; margin-bottom: 4px; }
.seller-wrap > p { color: #777; margin-bottom: 24px; }
.top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
.filter-tabs { display: flex; gap: 10px; flex-wrap: wrap; }
.filter-tab { padding: 8px 18px; border-radius: 20px; text-decoration: none; font-size: 0.875rem; font-weight: 600; background: white; color: #555; transition: 0.2s; }
.filter-tab.active, .filter-tab:hover { background: #8ed1c6; color: white; }
.filter-tab span { background: #f5efe6; color: #555; padding: 2px 7px; border-radius: 10px; font-size: 0.75rem; margin-left: 5px; }
.filter-tab.active span { background: rgba(255,255,255,0.3); color: white; }
.product-table { width: 100%; border-collapse: collapse; background: white; border-radius: 15px; overflow: hidden; }
.product-table th { text-align: left; font-size: 0.8rem; color: #999; padding: 14px 16px; font-weight: 600; background: #f9f9f9; }
.product-table td { padding: 12px 16px; font-size: 0.875rem; border-bottom: 1px solid #f5efe6; vertical-align: middle; }
.product-table img { width: 55px; height: 55px; object-fit: cover; border-radius: 8px; }
.badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.badge-approved { background: #e0f5ec; color: #2e7d5a; }
.badge-pending { background: #fff4e0; color: #b07c1a; }
.badge-rejected { background: #fde8ef; color: #c0395a; }
.btn-sm { padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; border: none; cursor: pointer; font-family: 'Poppins', sans-serif; text-decoration: none; display: inline-block; margin-right: 4px; }
.btn-edit { background: #f5efe6; color: #555; }
.btn-delete { background: #fde8ef; color: #c0395a; }
.alert-success { background: #e0f5ec; color: #2e7d5a; padding: 12px; border-radius: 10px; margin-bottom: 20px; }
.empty-state { text-align: center; padding: 60px 20px; color: #999; }
.empty-state p { margin-bottom: 16px; }
@media (max-width: 900px) { .seller-wrap { padding: 20px; } .top-bar { flex-direction: column; align-items: flex-start; } }
    
@media (max-width: 768px) {
    .admin-wrap { padding: 20px !important; }
    .stats-grid { grid-template-columns: repeat(2, 1fr) !important; }
    .admin-grid { grid-template-columns: 1fr !important; }
    .admin-table { display: block; overflow-x: auto; white-space: nowrap; }
    body { overflow-x: hidden; }
    * { max-width: 100%; box-sizing: border-box; }
}    
    
</style>

<div class="seller-wrap">
    <h1>My Products</h1>
    <p>Manage all your product listings.</p>

    <?php if ($success): ?>
        <div class="alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="top-bar">
        <div class="filter-tabs">
            <a href="manage_products.php?filter=all" class="filter-tab <?php echo $filter == 'all' ? 'active' : ''; ?>">
                All <span><?php echo $count_all; ?></span>
            </a>
            <a href="manage_products.php?filter=approved" class="filter-tab <?php echo $filter == 'approved' ? 'active' : ''; ?>">
                Approved <span><?php echo $count_approved; ?></span>
            </a>
            <a href="manage_products.php?filter=pending" class="filter-tab <?php echo $filter == 'pending' ? 'active' : ''; ?>">
                Pending <span><?php echo $count_pending; ?></span>
            </a>
            <a href="manage_products.php?filter=rejected" class="filter-tab <?php echo $filter == 'rejected' ? 'active' : ''; ?>">
                Rejected <span><?php echo $count_rejected; ?></span>
            </a>
        </div>
        <a href="add_product.php" class="btn">+ Add Product</a>
    </div>

    <?php if ($products && $products->num_rows > 0): ?>
    <table class="product-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Date Added</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($p = $products->fetch_assoc()): ?>
            <tr>
                <td>
                    <img src="<?php echo $p['image_url'] ? '../uploads/' . htmlspecialchars($p['image_url']) : 'https://via.placeholder.com/55'; ?>" alt="product">
                </td>
                <td><strong><?php echo htmlspecialchars($p['title']); ?></strong></td>
                <td style="color:#999;"><?php echo htmlspecialchars($p['category_name'] ?? 'Uncategorised'); ?></td>
                <td>R<?php echo number_format($p['price'], 2); ?></td>
                <td><?php echo $p['stock']; ?></td>
                <td><span class="badge badge-<?php echo $p['status']; ?>"><?php echo ucfirst($p['status']); ?></span></td>
                <td style="color:#999;font-size:0.8rem;"><?php echo date('d M Y', strtotime($p['created_at'])); ?></td>
                <td>
                    <a href="add_product.php?edit=<?php echo $p['product_id']; ?>" class="btn-sm btn-edit">Edit</a>
                    <a href="manage_products.php?delete=<?php echo $p['product_id']; ?>" class="btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php else: ?>
        <div class="empty-state">
            <p>No products found in this category.</p>
            <a href="add_product.php" class="btn">+ Add Your First Product</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>