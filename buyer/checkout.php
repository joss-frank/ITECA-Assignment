<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$cart = $_SESSION['cart'];
$subtotal = 0;
foreach ($cart as $item) $subtotal += $item['price'] * $item['quantity'];
$delivery = 50;
$total = $subtotal + $delivery;

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $postal = trim($_POST['postal']);
    $payment = $_POST['payment_method'];

    if (empty($name) || empty($email) || empty($address) || empty($city) || empty($postal)) {
        $error = "Please fill in all fields.";
    } else {
        
        $buyer_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO orders (buyer_id, total, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("id", $buyer_id, $total);
        $stmt->execute();
        $order_id = $conn->insert_id;

        
        foreach ($cart as $item) {
            $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            $stmt2->execute();
        }

        
        $stmt3 = $conn->prepare("INSERT INTO payments (order_id, method, status) VALUES (?, ?, 'pending')");
        $stmt3->bind_param("is", $order_id, $payment);
        $stmt3->execute();

        
        $_SESSION['cart'] = [];

        header("Location: orders.php?success=1");
        exit();
    }
}

include '../includes/header.php';
?>

<section class="checkout">
    <div class="checkout-form">
        <h2>Checkout</h2>

        <?php if ($error): ?>
            <div style="background:#fdeef3;color:#c0395a;padding:12px;border-radius:10px;margin-bottom:15px;"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <h3>Customer Details</h3>
            <input type="text" name="full_name" placeholder="Full Name" value="<?php echo htmlspecialchars($_SESSION['name']); ?>">
            <input type="email" name="email" placeholder="Email Address">

            <h3>Delivery Address</h3>
            <input type="text" name="address" placeholder="Street Address">
            <input type="text" name="city" placeholder="City">
            <input type="text" name="postal" placeholder="Postal Code">

            <h3>Payment Method</h3>
            <select name="payment_method">
                <option value="card">Card Payment</option>
                <option value="EFT">EFT</option>
            </select>

            <button type="submit" class="btn" style="width:100%;margin-top:20px;">Place Order</button>
        </form>
    </div>

    <div class="order-summary">
        <h3>Order Summary</h3>
        <?php foreach ($cart as $item): ?>
            <div class="summary-item">
                <p><?php echo htmlspecialchars($item['title']); ?> × <?php echo $item['quantity']; ?></p>
                <span>R<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
            </div>
        <?php endforeach; ?>
        <hr>
        <div class="summary-item">
            <p>Delivery</p>
            <span>R50.00</span>
        </div>
        <div class="summary-total">
            <p>Total</p>
            <span>R<?php echo number_format($total, 2); ?></span>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>