<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/header.php';

$buyer_id = $_SESSION['user_id'];
$orders = $conn->query("SELECT * FROM orders WHERE buyer_id = $buyer_id ORDER BY created_at DESC");
?>

<section style="padding:60px;">
    <h1>My Orders</h1>

    <?php if (isset($_GET['success'])): ?>
        <div style="background:#e0f5ec;color:#2e7d5a;padding:15px;border-radius:10px;margin-bottom:20px;">
            Order placed successfully! Thank you for your purchase.
        </div>
    <?php endif; ?>

    <?php if ($orders && $orders->num_rows > 0): ?>
        <?php while($order = $orders->fetch_assoc()): ?>
            <div style="background:white;padding:20px;border-radius:15px;margin-bottom:20px;">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                    <div>
                        <h3 style="margin:0;">Order #<?php echo $order['order_id']; ?></h3>
                        <p style="color:#999;margin:5px 0;font-size:0.85rem;"><?php echo date('d M Y', strtotime($order['created_at'])); ?></p>
                    </div>
                    <div>
                        <span style="background:#f5efe6;padding:6px 14px;border-radius:20px;font-size:0.85rem;font-weight:600;">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                    <div>
                        <strong>R<?php echo number_format($order['total'], 2); ?></strong>
                    </div>
                </div>

                <?php
                $items = $conn->query("SELECT oi.*, p.title FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = " . $order['order_id']);
                ?>
                <div style="margin-top:15px;padding-top:15px;border-top:1px solid #f5efe6;">
                    <?php while($item = $items->fetch_assoc()): ?>
                        <p style="margin:5px 0;color:#555;">
                            <?php echo htmlspecialchars($item['title']); ?> × <?php echo $item['quantity']; ?>
                            — R<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </p>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>You have no orders yet. <a href="browse.php" style="color:#8ed1c6;">Start shopping!</a></p>
    <?php endif; ?>
</section>

<?php include '../includes/footer.php'; ?>