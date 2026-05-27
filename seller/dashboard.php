<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/header.php';

$seller_id = $_SESSION['user_id'];


$total_products = $conn->query("SELECT COUNT(*) as count FROM products WHERE seller_id = $seller_id")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(DISTINCT o.order_id) as count FROM orders o JOIN order_items oi ON o.order_id = oi.order_id JOIN products p ON oi.product_id = p.product_id WHERE p.seller_id = $seller_id")->fetch_assoc()['count'];
$total_earnings = $conn->query("SELECT SUM(oi.price * oi.quantity) as total FROM order_items oi JOIN products p ON oi.product_id = p.product_id JOIN orders o ON oi.order_id = o.order_id WHERE p.seller_id = $seller_id AND o.status != 'cancelled'")->fetch_assoc()['total'];
$total_earnings = $total_earnings ?? 0;


$products = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.seller_id = $seller_id ORDER BY p.created_at DESC");


$recent_orders = $conn->query("SELECT DISTINCT o.order_id, o.status, o.created_at, o.total FROM orders o JOIN order_items oi ON o.order_id = oi.order_id JOIN products p ON oi.product_id = p.product_id WHERE p.seller_id = $seller_id ORDER BY o.created_at DESC LIMIT 5");
?>

<style>
.dashboard { padding: 40px 50px; }
.dash-welcome h1 { font-size: 1.8rem; margin-bottom: 4px; }
.dash-welcome p { color: #777; margin-bottom: 30px; }
.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }
.stat-card { background: white; padding: 24px; border-radius: 15px; text-align: center; }
.stat-num { font-size: 2rem; font-weight: 800; color: #8ed1c6; }
.stat-label { font-size: 0.85rem; color: #777; margin-top: 4px; }
.dash-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
.dash-section { background: white; border-radius: 15px; padding: 24px; }
.dash-section h2 { font-size: 1.1rem; margin-bottom: 18px; padding-bottom: 10px; border-bottom: 2px solid #f5efe6; }
.product-table { width: 100%; border-collapse: collapse; }
.product-table th { text-align: left; font-size: 0.8rem; color: #999; padding: 8px 10px; font-weight: 600; }
.product-table td { padding: 12px 10px; font-size: 0.9rem; border-bottom: 1px solid #f5efe6; vertical-align: middle; }
.product-table img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
.badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.badge-approved { background: #e0f5ec; color: #2e7d5a; }
.badge-pending { background: #fff4e0; color: #b07c1a; }
.badge-rejected { background: #fde8ef; color: #c0395a; }
.btn-edit { background: #f5efe6; color: #555; border: none; padding: 6px 12px; border-radius: 8px; cursor: pointer; font-size: 0.8rem; font-family: 'Poppins', sans-serif; text-decoration: none; }
.btn-delete { background: #fde8ef; color: #c0395a; border: none; padding: 6px 12px; border-radius: 8px; cursor: pointer; font-size: 0.8rem; font-family: 'Poppins', sans-serif; }
.orders-list { list-style: none; padding: 0; margin: 0; }
.orders-list li { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f5efe6; font-size: 0.9rem; }
.orders-list li:last-child { border-bottom: none; }
@media (max-width: 900px) { .stats-grid { grid-template-columns: 1fr 1fr; } .dash-grid { grid-template-columns: 1fr; } .dashboard { padding: 20px; } }
    
@media (max-width: 768px) {
    .dashboard { padding: 20px !important; }
    .stats-grid { grid-template-columns: repeat(2, 1fr) !important; }
    .dash-grid { grid-template-columns: 1fr !important; }
    .product-table { display: block; overflow-x: auto; white-space: nowrap; }
    .dash-section { overflow-x: auto; }
    body { overflow-x: hidden; }
    * { max-width: 100%; }
}    
    
</style>

<div class="dashboard">
    <div class="dash-welcome">
        <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?> 👋</h1>
        <p>Here's what's happening with your shop today.</p>
    </div>

    
    <div class="stats-grid">
        <div class="stat-card">
            <div style="font-size:2rem;">📦</div>
            <div class="stat-num"><?php echo $total_products; ?></div>
            <div class="stat-label">Total Products</div>
        </div>
        <div class="stat-card">
            <div style="font-size:2rem;">🛒</div>
            <div class="stat-num"><?php echo $total_orders; ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
            <div style="font-size:2rem;">💰</div>
            <div class="stat-num">R<?php echo number_format($total_earnings, 2); ?></div>
            <div class="stat-label">Total Earnings</div>
        </div>
    </div>

    <div class="dash-grid">
        
        <div class="dash-section">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
                <h2 style="margin:0;border:none;padding:0;">My Products</h2>
                <a href="add_product.php" class="btn">+ Add Product</a>
            </div>

            <?php if ($products && $products->num_rows > 0): ?>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($p = $products->fetch_assoc()): ?>
                    <tr>
                        <td><img src="<?php echo $p['image_url'] ? '../uploads/' . htmlspecialchars($p['image_url']) : 'https://via.placeholder.com/50'; ?>" alt="product"></td>
                        <td><?php echo htmlspecialchars($p['title']); ?></td>
                        <td>R<?php echo number_format($p['price'], 2); ?></td>
                        <td><?php echo $p['stock']; ?></td>
                        <td><span class="badge badge-<?php echo $p['status']; ?>"><?php echo ucfirst($p['status']); ?></span></td>
                        <td style="display:flex;gap:6px;">
                            <a href="add_product.php?edit=<?php echo $p['product_id']; ?>" class="btn-edit">Edit</a>
                            <a href="dashboard.php?delete=<?php echo $p['product_id']; ?>" class="btn-delete" onclick="return confirm('Delete this product?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p style="color:#999;">No products yet. <a href="add_product.php" style="color:#8ed1c6;">Add your first product!</a></p>
            <?php endif; ?>
        </div>

        
        <div class="dash-section">
            <h2>Recent Orders</h2>
            <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
            <ul class="orders-list">
                <?php while($o = $recent_orders->fetch_assoc()): ?>
                <li>
                    <div>
                        <div style="color:#8ed1c6;font-weight:600;">#ORD-<?php echo $o['order_id']; ?></div>
                        <div style="font-size:0.8rem;color:#999;">R<?php echo number_format($o['total'], 2); ?></div>
                    </div>
                    <span class="badge badge-<?php echo $o['status'] == 'delivered' ? 'approved' : 'pending'; ?>">
                        <?php echo ucfirst($o['status']); ?>
                    </span>
                </li>
                <?php endwhile; ?>
            </ul>
            <?php else: ?>
                <p style="color:#999;">No orders yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php

if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM products WHERE product_id = $del_id AND seller_id = $seller_id");
    header("Location: dashboard.php");
    exit();
}
?>

<?php include '../includes/footer.php'; ?>