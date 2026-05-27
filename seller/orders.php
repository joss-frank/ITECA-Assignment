<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../auth/login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];
$success = "";


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $oid = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $allowed = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];
    if (in_array($status, $allowed)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $status, $oid);
        $stmt->execute();
        $success = "Order status updated successfully.";
    }
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$sql = "SELECT DISTINCT o.*, u.name as buyer_name, u.email as buyer_email
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN products p ON oi.product_id = p.product_id
        JOIN users u ON o.buyer_id = u.user_id
        WHERE p.seller_id = $seller_id";
if ($filter !== 'all') $sql .= " AND o.status = '" . $conn->real_escape_string($filter) . "'";
$sql .= " ORDER BY o.created_at DESC";
$orders = $conn->query($sql);


$total_orders = $conn->query("SELECT COUNT(DISTINCT o.order_id) as c FROM orders o JOIN order_items oi ON o.order_id = oi.order_id JOIN products p ON oi.product_id = p.product_id WHERE p.seller_id = $seller_id")->fetch_assoc()['c'];
$pending_orders = $conn->query("SELECT COUNT(DISTINCT o.order_id) as c FROM orders o JOIN order_items oi ON o.order_id = oi.order_id JOIN products p ON oi.product_id = p.product_id WHERE p.seller_id = $seller_id AND o.status = 'pending'")->fetch_assoc()['c'];
$shipped_orders = $conn->query("SELECT COUNT(DISTINCT o.order_id) as c FROM orders o JOIN order_items oi ON o.order_id = oi.order_id JOIN products p ON oi.product_id = p.product_id WHERE p.seller_id = $seller_id AND o.status = 'shipped'")->fetch_assoc()['c'];
$delivered_orders = $conn->query("SELECT COUNT(DISTINCT o.order_id) as c FROM orders o JOIN order_items oi ON o.order_id = oi.order_id JOIN products p ON oi.product_id = p.product_id WHERE p.seller_id = $seller_id AND o.status = 'delivered'")->fetch_assoc()['c'];

include '../includes/header.php';
?>

<style>
.seller-wrap { padding: 40px 50px; }
.seller-wrap h1 { font-size: 1.8rem; margin-bottom: 4px; }
.seller-wrap > p { color: #777; margin-bottom: 24px; }
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px; }
.mini-stat { background: white; padding: 16px 20px; border-radius: 12px; text-align: center; }
.mini-stat .num { font-size: 1.6rem; font-weight: 800; color: #8ed1c6; }
.mini-stat .label { font-size: 0.8rem; color: #999; margin-top: 2px; }
.filter-tabs { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
.filter-tab { padding: 8px 18px; border-radius: 20px; text-decoration: none; font-size: 0.875rem; font-weight: 600; background: white; color: #555; transition: 0.2s; }
.filter-tab.active, .filter-tab:hover { background: #8ed1c6; color: white; }
.orders-table { width: 100%; border-collapse: collapse; background: white; border-radius: 15px; overflow: hidden; }
.orders-table th { text-align: left; font-size: 0.8rem; color: #999; padding: 14px 16px; font-weight: 600; background: #f9f9f9; }
.orders-table td { padding: 12px 16px; font-size: 0.875rem; border-bottom: 1px solid #f5efe6; vertical-align: middle; }
.badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.badge-pending { background: #fff4e0; color: #b07c1a; }
.badge-paid { background: #e8f0fe; color: #1a3c6e; }
.badge-shipped { background: #f0e8fe; color: #6b3fa0; }
.badge-delivered { background: #e0f5ec; color: #2e7d5a; }
.badge-cancelled { background: #fde8ef; color: #c0395a; }
.status-select { padding: 6px 10px; border-radius: 8px; border: 1px solid #ddd; font-family: 'Poppins', sans-serif; font-size: 0.8rem; }
.order-items-list { font-size: 0.8rem; color: #777; margin-top: 4px; }
.alert-success { background: #e0f5ec; color: #2e7d5a; padding: 12px; border-radius: 10px; margin-bottom: 20px; }
.empty-state { text-align: center; padding: 60px 20px; color: #999; }
@media (max-width: 900px) { .seller-wrap { padding: 20px; } .stats-row { grid-template-columns: repeat(2, 1fr); } }
</style>

<div class="seller-wrap">
    <h1>My Orders</h1>
    <p>Track and manage orders for your products.</p>

    <?php if ($success): ?>
        <div class="alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    
    <div class="stats-row">
        <div class="mini-stat">
            <div class="num"><?php echo $total_orders; ?></div>
            <div class="label">Total Orders</div>
        </div>
        <div class="mini-stat">
            <div class="num" style="color:#b07c1a;"><?php echo $pending_orders; ?></div>
            <div class="label">Pending</div>
        </div>
        <div class="mini-stat">
            <div class="num" style="color:#6b3fa0;"><?php echo $shipped_orders; ?></div>
            <div class="label">Shipped</div>
        </div>
        <div class="mini-stat">
            <div class="num" style="color:#2e7d5a;"><?php echo $delivered_orders; ?></div>
            <div class="label">Delivered</div>
        </div>
    </div>

    
    <div class="filter-tabs">
        <a href="orders.php?filter=all" class="filter-tab <?php echo $filter == 'all' ? 'active' : ''; ?>">All</a>
        <a href="orders.php?filter=pending" class="filter-tab <?php echo $filter == 'pending' ? 'active' : ''; ?>">Pending</a>
        <a href="orders.php?filter=paid" class="filter-tab <?php echo $filter == 'paid' ? 'active' : ''; ?>">Paid</a>
        <a href="orders.php?filter=shipped" class="filter-tab <?php echo $filter == 'shipped' ? 'active' : ''; ?>">Shipped</a>
        <a href="orders.php?filter=delivered" class="filter-tab <?php echo $filter == 'delivered' ? 'active' : ''; ?>">Delivered</a>
        <a href="orders.php?filter=cancelled" class="filter-tab <?php echo $filter == 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
    </div>

    <?php if ($orders && $orders->num_rows > 0): ?>
    <table class="orders-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Buyer</th>
                <th>Items Ordered</th>
                <th>Order Total</th>
                <th>Date</th>
                <th>Status</th>
                <th>Update Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while($o = $orders->fetch_assoc()): ?>
            <tr>
                <td style="color:#8ed1c6;font-weight:600;">#ORD-<?php echo $o['order_id']; ?></td>
                <td>
                    <strong><?php echo htmlspecialchars($o['buyer_name']); ?></strong>
                    <div style="font-size:0.8rem;color:#999;"><?php echo htmlspecialchars($o['buyer_email']); ?></div>
                </td>
                <td>
                    <?php
                    
                    $items = $conn->query("SELECT oi.quantity, p.title FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = " . $o['order_id'] . " AND p.seller_id = $seller_id");
                    while($item = $items->fetch_assoc()):
                    ?>
                        <div class="order-items-list">
                            <?php echo htmlspecialchars($item['title']); ?> × <?php echo $item['quantity']; ?>
                        </div>
                    <?php endwhile; ?>
                </td>
                <td>R<?php echo number_format($o['total'], 2); ?></td>
                <td style="color:#999;font-size:0.8rem;"><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
                <td><span class="badge badge-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                <td>
                    <form method="POST" action="" style="display:flex;gap:8px;align-items:center;">
                        <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                        <select name="status" class="status-select">
                            <?php foreach(['pending','paid','shipped','delivered','cancelled'] as $s): ?>
                                <option value="<?php echo $s; ?>" <?php echo $o['status'] == $s ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($s); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="update_status" class="btn" style="padding:6px 14px;font-size:0.8rem;">Update</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php else: ?>
        <div class="empty-state">
            <p>No orders found.</p>
            <a href="manage_products.php" style="color:#8ed1c6;">Check your product listings</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>