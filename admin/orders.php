<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header("Location: ../auth/login.php");
    exit();
}

$success = "";


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $oid = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $allowed = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];
    if (in_array($status, $allowed)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $status, $oid);
        $stmt->execute();
        $success = "Order status updated.";
    }
}

$orders = $conn->query("SELECT o.*, u.name as buyer_name, u.email as buyer_email FROM orders o JOIN users u ON o.buyer_id = u.user_id ORDER BY o.created_at DESC");
include '../includes/header.php';
?>

<style>
.admin-wrap { padding: 40px 50px; }
.admin-table { width: 100%; border-collapse: collapse; background: white; border-radius: 15px; overflow: hidden; }
.admin-table th { text-align: left; font-size: 0.8rem; color: #999; padding: 14px 16px; font-weight: 600; background: #f9f9f9; }
.admin-table td { padding: 12px 16px; font-size: 0.875rem; border-bottom: 1px solid #f5efe6; vertical-align: middle; }
.badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.badge-pending { background: #fff4e0; color: #b07c1a; }
.badge-paid { background: #e8f0fe; color: #1a3c6e; }
.badge-shipped { background: #f0e8fe; color: #6b3fa0; }
.badge-delivered { background: #e0f5ec; color: #2e7d5a; }
.badge-cancelled { background: #fde8ef; color: #c0395a; }
.status-select { padding: 6px 10px; border-radius: 8px; border: 1px solid #ddd; font-family: 'Poppins', sans-serif; font-size: 0.8rem; }
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
    <h1 style="margin-bottom:4px;">Manage Orders</h1>
    <p style="color:#777;margin-bottom:24px;">View and update all platform orders.</p>

    <?php if ($success): ?><div class="alert-success"><?php echo $success; ?></div><?php endif; ?>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Buyer</th>
                <th>Total</th>
                <th>Date</th>
                <th>Status</th>
                <th>Update Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($orders && $orders->num_rows > 0): ?>
                <?php while($o = $orders->fetch_assoc()): ?>
                <tr>
                    <td style="color:#8ed1c6;font-weight:600;">#<?php echo $o['order_id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($o['buyer_name']); ?></strong>
                        <div style="font-size:0.8rem;color:#999;"><?php echo htmlspecialchars($o['buyer_email']); ?></div>
                    </td>
                    <td>R<?php echo number_format($o['total'], 2); ?></td>
                    <td style="color:#999;font-size:0.8rem;"><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
                    <td><span class="badge badge-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                    <td>
                        <form method="POST" action="" style="display:flex;gap:8px;align-items:center;">
                            <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                            <select name="status" class="status-select">
                                <?php foreach(['pending','paid','shipped','delivered','cancelled'] as $s): ?>
                                    <option value="<?php echo $s; ?>" <?php echo $o['status'] == $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="update_status" class="btn" style="padding:6px 14px;font-size:0.8rem;">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;color:#999;padding:30px;">No orders yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>