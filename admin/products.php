<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header("Location: ../auth/login.php");
    exit();
}

$success = "";


if (isset($_GET['approve'])) {
    $conn->query("UPDATE products SET status = 'approved' WHERE product_id = " . (int)$_GET['approve']);
    $success = "Product approved.";
}
if (isset($_GET['reject'])) {
    $conn->query("UPDATE products SET status = 'rejected' WHERE product_id = " . (int)$_GET['reject']);
    $success = "Product rejected.";
}
if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM products WHERE product_id = " . (int)$_GET['delete']);
    $success = "Product deleted.";
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$sql = "SELECT p.*, u.name as seller_name, c.name as category_name FROM products p JOIN users u ON p.seller_id = u.user_id LEFT JOIN categories c ON p.category_id = c.category_id";
if ($filter !== 'all') $sql .= " WHERE p.status = '" . $conn->real_escape_string($filter) . "'";
$sql .= " ORDER BY p.created_at DESC";
$products = $conn->query($sql);

include '../includes/header.php';
?>

<style>
.admin-wrap { padding: 40px 50px; }
.filter-tabs { display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap; }
.filter-tab { padding: 8px 20px; border-radius: 20px; text-decoration: none; font-size: 0.875rem; font-weight: 600; background: white; color: #555; transition: 0.2s; }
.filter-tab.active, .filter-tab:hover { background: #8ed1c6; color: white; }
.admin-table { width: 100%; border-collapse: collapse; background: white; border-radius: 15px; overflow: hidden; }
.admin-table th { text-align: left; font-size: 0.8rem; color: #999; padding: 14px 16px; font-weight: 600; background: #f9f9f9; }
.admin-table td { padding: 12px 16px; font-size: 0.875rem; border-bottom: 1px solid #f5efe6; vertical-align: middle; }
.admin-table img { width: 55px; height: 55px; object-fit: cover; border-radius: 8px; }
.badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.badge-approved { background: #e0f5ec; color: #2e7d5a; }
.badge-pending { background: #fff4e0; color: #b07c1a; }
.badge-rejected { background: #fde8ef; color: #c0395a; }
.btn-sm { padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; border: none; cursor: pointer; font-family: 'Poppins', sans-serif; text-decoration: none; display: inline-block; margin-right: 4px; }
.btn-approve { background: #e0f5ec; color: #2e7d5a; }
.btn-reject { background: #fff4e0; color: #b07c1a; }
.btn-delete { background: #fde8ef; color: #c0395a; }
.alert-success { background: #e0f5ec; color: #2e7d5a; padding: 12px; border-radius: 10px; margin-bottom: 20px; }
@media (max-width: 900px) { .admin-wrap { padding: 20px; } }
    
@media (max-width: 768px) {
    .admin-wrap { padding: 20px !important; }
    .stats-grid { grid-template-columns: repeat(2, 1fr) !important; }
    .admin-grid { grid-template-columns: 1fr !important; }
    .admin-table { display: block; overflow-x: auto; white-space: nowrap; }
    body { overflow-x: hidden; }
    * { max-width: 100%; box-sizing: border-box; }
}    
    
    
</style>

<div class="admin-wrap">
    <h1 style="margin-bottom:4px;">Manage Products</h1>
    <p style="color:#777;margin-bottom:20px;">Approve, reject or remove product listings.</p>

    <?php if ($success): ?><div class="alert-success"><?php echo $success; ?></div><?php endif; ?>

    <div class="filter-tabs">
        <a href="products.php?filter=all" class="filter-tab <?php echo $filter == 'all' ? 'active' : ''; ?>">All</a>
        <a href="products.php?filter=pending" class="filter-tab <?php echo $filter == 'pending' ? 'active' : ''; ?>">Pending</a>
        <a href="products.php?filter=approved" class="filter-tab <?php echo $filter == 'approved' ? 'active' : ''; ?>">Approved</a>
        <a href="products.php?filter=rejected" class="filter-tab <?php echo $filter == 'rejected' ? 'active' : ''; ?>">Rejected</a>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Title</th>
                <th>Seller</th>
                <th>Price</th>
                <th>Category</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($products && $products->num_rows > 0): ?>
                <?php while($p = $products->fetch_assoc()): ?>
                <tr>
                    <td><img src="<?php echo $p['image_url'] ? '../uploads/' . htmlspecialchars($p['image_url']) : 'https://via.placeholder.com/55'; ?>" alt="product"></td>
                    <td><strong><?php echo htmlspecialchars($p['title']); ?></strong></td>
                    <td style="color:#999;"><?php echo htmlspecialchars($p['seller_name']); ?></td>
                    <td>R<?php echo number_format($p['price'], 2); ?></td>
                    <td style="color:#999;"><?php echo htmlspecialchars($p['category_name'] ?? 'N/A'); ?></td>
                    <td><span class="badge badge-<?php echo $p['status']; ?>"><?php echo ucfirst($p['status']); ?></span></td>
                    <td>
                        <?php if ($p['status'] !== 'approved'): ?>
                            <a href="products.php?approve=<?php echo $p['product_id']; ?>" class="btn-sm btn-approve">Approve</a>
                        <?php endif; ?>
                        <?php if ($p['status'] !== 'rejected'): ?>
                            <a href="products.php?reject=<?php echo $p['product_id']; ?>" class="btn-sm btn-reject">Reject</a>
                        <?php endif; ?>
                        <a href="products.php?delete=<?php echo $p['product_id']; ?>" class="btn-sm btn-delete" onclick="return confirm('Delete this product?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align:center;color:#999;padding:30px;">No products found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>