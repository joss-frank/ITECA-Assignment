<?php
session_start();
include '../config.php';
include '../includes/header.php';


if (isset($_GET['remove'])) {
    $removeId = (int)$_GET['remove'];
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['product_id'] == $removeId) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    header("Location: cart.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $pid => $qty) {
        $qty = (int)$qty;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $pid) {
                $item['quantity'] = max(1, $qty);
            }
        }
    }
    header("Location: cart.php");
    exit();
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$delivery = count($cart) > 0 ? 50 : 0;
$total = $subtotal + $delivery;
?>

<section class="cart">
    <div class="cart-items">
        <h2>Your Cart</h2>
        <?php if (empty($cart)): ?>
            <p>Your cart is empty. <a href="browse.php">Continue shopping</a></p>
        <?php else: ?>
            <form method="POST" action="">
                <?php foreach ($cart as $item): ?>
                    <div class="cart-item">
                        <img src="<?php echo $item['image'] ? '../uploads/' . htmlspecialchars($item['image']) : 'https://via.placeholder.com/100x100'; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" width="100">
                        <div class="item-info">
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p>R<?php echo number_format($item['price'], 2); ?></p>
                        </div>
                        <input type="number" name="quantities[<?php echo $item['product_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" style="width:60px;padding:5px;">
                        <a href="cart.php?remove=<?php echo $item['product_id']; ?>" class="remove-btn">Remove</a>
                    </div>
                <?php endforeach; ?>
                <button type="submit" name="update_cart" class="btn" style="margin-top:15px;">Update Cart</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="cart-summary">
        <h3>Order Summary</h3>
        <p>Subtotal: R<?php echo number_format($subtotal, 2); ?></p>
        <p>Delivery: R<?php echo number_format($delivery, 2); ?></p>
        <hr>
        <h2>Total: R<?php echo number_format($total, 2); ?></h2>
        <?php if (!empty($cart)): ?>
            <a href="checkout.php" class="btn" style="display:block;text-align:center;text-decoration:none;">Proceed to Checkout</a>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>