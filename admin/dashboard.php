<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/header.php';


$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$pending_products = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'pending'")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];


$recent_users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");


$recent_orders = $conn->query("SELECT o.*, u.name as buyer_name FROM orders o JOIN users u ON o.buyer_id = u.user_id ORDER BY o.created_at DESC LIMIT 5");
?>

<style>
.admin-wrap { padding: 40px 50px; }
.admin-wrap h1 { font-size: 1.8rem; margin-bottom: 4px; }
.admin-wrap > p { color: #777; margin-bottom: 30px; }
.admin-wrap { padding: 40px 50px; overflow: hidden; }    
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px; }
.stat-card { background: white; padding: 24px; border-radius: 15px; text-align: center; }
.stat-num { font-size: 2rem; font-weight: 800; color: #8ed1c6; }
.stat-label { font-size: 0.85rem; color: #777; margin-top: 4px; }
.admin-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
.admin-section { background: white; border-radius: 15px; padding: 24px; }
.admin-section h2 { font-size: 1.1rem; margin-bottom: 18px; padding-bottom: 10px; border-bottom: 2px solid #f5efe6; display: flex; justify-content: space-between; align-items: center; }
.admin-table { width: 100%; border-collapse: collapse; }
.admin-table th { text-align: left; font-size: 0.8rem; color: #999; padding: 8px 10px; font-weight: 600; }
.admin-table td { padding: 10px; font-size: 0.875rem; border-bottom: 1px solid #f5efe6; vertical-align: middle; }
.badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.badge-buyer { background: #e8f0fe; color: #1a3c6e; }
.badge-seller { background: #fff4e0; color: #b07c1a; }
.badge-admin { background: #fde8ef; color: #c0395a; }
.badge-superadmin { background: #e0f5ec; color: #2e7d5a; }
.badge-pending { background: #fff4e0; color: #b07c1a; }
.badge-paid { background: #e0f5ec; color: #2e7d5a; }
.badge-delivered { background: #e0f5ec; color: #2e7d5a; }
.badge-cancelled { background: #fde8ef; color: #c0395a; }
.quick-links { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 40px; }
.quick-link { background: white; padding: 20px; border-radius: 15px; text-align: center; text-decoration: none; color: #333; transition: 0.2s; border: 2px solid transparent; }
.quick-link:hover { border-color: #8ed1c6; }
.quick-link .icon { font-size: 2rem; margin-bottom: 8px; }
.quick-link p { margin: 0; font-size: 0.9rem; color: #777; }
@media (max-width: 900px) { .stats-grid { grid-template-columns: repeat(2,1fr); } .admin-grid { grid-template-columns: 1fr; } .admin-wrap { padding: 20px; } .quick-links { grid-template-columns: 1fr 1fr; } }
    
@media (max-width: 768px) {
    .admin-wrap { padding: 20px !important; overflow-x: hidden !important; }
    .stats-grid { grid-template-columns: repeat(2, 1fr) !important; }
    .admin-grid { grid-template-columns: 1fr !important; }
    .admin-table { display: block; overflow-x: auto; white-space: nowrap; width: 100%; }
    .quick-links { grid-template-columns: 1fr 1fr !important; }
    body { overflow-x: hidden !important; }
    .admin-section { overflow-x: auto; }
}  
    
</style>

<div class="admin-wrap">
    <h1>Admin Dashboard</h1>
    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>. Here's your platform overview.</p>

    
    <div class="stats-grid">
        <div class="stat-card">
            <div style="font-size:2rem;">👥</div>
            <div class="stat-num"><?php echo $total_users; ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <div style="font-size:2rem;">📦</div>
            <div class="stat-num"><?php echo $total_products; ?></div>
            <div class="stat-label">Total Products</div>
        </div>
        <div class="stat-card">
            <div style="font-size:2rem;">⏳</div>
            <div class="stat-num" style="color:<?php echo $pending_products > 0 ? '#b07c1a' : '#8ed1c6'; ?>">
                <?php echo $pending_products; ?>
            </div>
            <div class="stat-label">Pending Approvals</div>
        </div>
        <div class="stat-card">
            <div style="font-size:2rem;">🛒</div>
            <div class="stat-num"><?php echo $total_orders; ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>

    
    <div class="quick-links">
        <a href="users.php" class="quick-link">
            <div class="icon">👥</div>
            <strong>Manage Users</strong>
            <p>View, edit and delete users</p>
        </a>
        <a href="products.php" class="quick-link">
            <div class="icon">📦</div>
            <strong>Manage Products</strong>
            <p>Approve or reject listings</p>
        </a>
        <a href="orders.php" class="quick-link">
            <div class="icon">🛒</div>
            <strong>Manage Orders</strong>
            <p>View and update all orders</p>
        </a>
        <?php if ($_SESSION['role'] == 'superadmin'): ?>
        <a href="roles.php" class="quick-link">
            <div class="icon">🔐</div>
            <strong>Manage Roles</strong>
            <p>Assign admin permissions</p>
        </a>
        <?php endif; ?>
    </div>

    
     <div class="admin-grid">
        <div class="admin-section">
            <h2>Recent Users <a href="users.php" style="font-size:0.8rem;color:#8ed1c6;font-weight:400;">View all</a></h2>
            <table class="admin-table">
                <thead><tr><th>Name</th><th>Email</th><th>Role</th></tr></thead>
                <tbody>
                    <?php while($u = $recent_users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['name']); ?></td>
                        <td style="color:#999;font-size:0.8rem;"><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><span class="badge badge-<?php echo $u['role']; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="admin-section">
            <h2>Recent Orders <a href="orders.php" style="font-size:0.8rem;color:#8ed1c6;font-weight:400;">View all</a></h2>
            <table class="admin-table">
                <thead><tr><th>Order</th><th>Buyer</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                    <?php while($o = $recent_orders->fetch_assoc()): ?>
                    <tr>
                        <td style="color:#8ed1c6;font-weight:600;">#<?php echo $o['order_id']; ?></td>
                        <td><?php echo htmlspecialchars($o['buyer_name']); ?></td>
                        <td>R<?php echo number_format($o['total'], 2); ?></td>
                        <td><span class="badge badge-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>