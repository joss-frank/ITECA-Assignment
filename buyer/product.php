<?php
session_start();
include '../config.php';
include '../includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: browse.php");
    exit();
}

$id = (int)$_GET['id'];
$product = $conn->query("SELECT p.*, u.name as seller_name, u.user_id as seller_id, u.profile_pic as seller_pic FROM products p JOIN users u ON p.seller_id = u.user_id WHERE p.product_id = $id AND p.status = 'approved'")->fetch_assoc();

if (!$product) {
    echo "<p style='text-align:center;padding:50px;'>Product not found.</p>";
    include '../includes/footer.php';
    exit();
}


if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }
    $qty = (int)$_POST['quantity'];
    $pid = (int)$_POST['product_id'];

    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_id'] == $pid) {
            $item['quantity'] += $qty;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $_SESSION['cart'][] = [
            'product_id' => $pid,
            'title' => $product['title'],
            'price' => $product['price'],
            'image' => $product['image_url'],
            'quantity' => $qty
        ];
    }
    header("Location: cart.php");
    exit();
}


$reviews = $conn->query("SELECT r.*, u.name FROM reviews r JOIN users u ON r.buyer_id = u.user_id WHERE r.product_id = $id ORDER BY r.created_at DESC");
?>

<section class="product-details">
    <div class="product-image">
        <img src="<?php echo $product['image_url'] ? '../uploads/' . htmlspecialchars($product['image_url']) : 'https://via.placeholder.com/400x350'; ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
    </div>

    <div class="product-info">
        <h1><?php echo htmlspecialchars($product['title']); ?></h1>
        <h2>R<?php echo number_format($product['price'], 2); ?></h2>
        <p class="description"><?php echo htmlspecialchars($product['description']); ?></p>

        <div class="seller-box">
            <img src="<?php echo $product['seller_pic'] ? '../uploads/' . htmlspecialchars($product['seller_pic']) : 'https://via.placeholder.com/60'; ?>" alt="<?php echo htmlspecialchars($product['seller_name']); ?>" style="width:60px;height:60px;border-radius:50%;object-fit:cover;">
            <div>
                <h4><?php echo htmlspecialchars($product['seller_name']); ?></h4>
                <p>HandmadeHub Seller</p>
                <a href="seller-profile.php?id=<?php echo $product['seller_id']; ?>" class="view-profile">View Profile</a>
            </div>
        </div>

        <form method="POST" action="">
            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
            <label>Quantity:</label>
            <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
            <div class="buttons" style="margin-top:15px;">
                <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
            </div>
        </form>

        <a href="browse.php" class="back-link">← Back to Shop</a>
    </div>
</section>


<section style="padding: 0 60px 60px;">
    <h2>Customer Reviews</h2>

    <?php
    
    $can_review = false;
    $already_reviewed = false;

    if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'buyer') {
        $buyer_id = $_SESSION['user_id'];

        
        $purchased = $conn->query("SELECT oi.item_id FROM order_items oi JOIN orders o ON oi.order_id = o.order_id WHERE o.buyer_id = $buyer_id AND oi.product_id = $id AND o.status = 'delivered'")->num_rows;
        $can_review = $purchased > 0;

        
        $already_reviewed = $conn->query("SELECT review_id FROM reviews WHERE buyer_id = $buyer_id AND product_id = $id")->num_rows > 0;
    }
    ?>

    <?php
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../auth/login.php");
            exit();
        }
        $buyer_id = $_SESSION['user_id'];
        $rating = (int)$_POST['rating'];
        $comment = trim($_POST['comment']);

        if ($rating < 1 || $rating > 5) {
            $review_error = "Please select a star rating.";
        } elseif (empty($comment)) {
            $review_error = "Please write a comment.";
        } else {
            $stmt = $conn->prepare("INSERT INTO reviews (product_id, buyer_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $id, $buyer_id, $rating, $comment);
            $stmt->execute();
            $review_success = "Your review has been submitted!";
            $already_reviewed = true;
        }
    }
    ?>

    <?php if (isset($review_error)): ?>
        <div style="background:#fdeef3;color:#c0395a;padding:12px;border-radius:10px;margin-bottom:20px;"><?php echo $review_error; ?></div>
    <?php endif; ?>
    <?php if (isset($review_success)): ?>
        <div style="background:#e0f5ec;color:#2e7d5a;padding:12px;border-radius:10px;margin-bottom:20px;"><?php echo $review_success; ?></div>
    <?php endif; ?>

    
    <?php if ($can_review && !$already_reviewed): ?>
        <div style="background:white;padding:30px;border-radius:15px;margin-bottom:30px;">
            <h3 style="margin-bottom:20px;">Leave a Review</h3>
            <form method="POST" action="">

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:0.85rem;font-weight:600;margin-bottom:8px;color:#333;">Your Rating</label>
                    <div class="star-rating">
                        <?php for($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>">
                            <label for="star<?php echo $i; ?>">★</label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:0.85rem;font-weight:600;margin-bottom:8px;color:#333;">Your Review</label>
                    <textarea name="comment" placeholder="Share your experience with this product..." style="width:100%;padding:12px;border:1px solid #ddd;border-radius:10px;font-family:'Poppins',sans-serif;font-size:0.9rem;height:100px;resize:vertical;box-sizing:border-box;"></textarea>
                </div>

                <button type="submit" name="submit_review" class="btn">Submit Review</button>
            </form>
        </div>

    <?php elseif ($already_reviewed): ?>
        <div style="background:#f5efe6;padding:16px;border-radius:10px;margin-bottom:20px;color:#777;font-size:0.9rem;">
            ✓ You have already reviewed this product.
        </div>

    <?php elseif (isset($_SESSION['user_id']) && $_SESSION['role'] == 'buyer' && !$can_review): ?>
        <div style="background:#f5efe6;padding:16px;border-radius:10px;margin-bottom:20px;color:#777;font-size:0.9rem;">
            You can only leave a review after your order has been delivered.
        </div>

    <?php elseif (!isset($_SESSION['user_id'])): ?>
        <div style="background:#f5efe6;padding:16px;border-radius:10px;margin-bottom:20px;color:#777;font-size:0.9rem;">
            <a href="../auth/login.php" style="color:#8ed1c6;font-weight:600;">Login</a> to leave a review.
        </div>
    <?php endif; ?>

    
    <?php
    $reviews = $conn->query("SELECT r.*, u.name FROM reviews r JOIN users u ON r.buyer_id = u.user_id WHERE r.product_id = $id ORDER BY r.created_at DESC");
    ?>

    <?php if ($reviews && $reviews->num_rows > 0): ?>
        <?php while($r = $reviews->fetch_assoc()): ?>
            <div style="background:white;padding:20px;border-radius:15px;margin-bottom:15px;">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:8px;">
                    <strong><?php echo htmlspecialchars($r['name']); ?></strong>
                    <div>
                        <span style="color:#f5a623;">
                            <?php for($i = 1; $i <= 5; $i++) echo $i <= $r['rating'] ? '★' : '☆'; ?>
                        </span>
                        <span style="color:#999;font-size:0.8rem;margin-left:8px;"><?php echo date('d M Y', strtotime($r['created_at'])); ?></span>
                    </div>
                </div>
                <p style="color:#555;margin:0;font-size:0.9rem;"><?php echo htmlspecialchars($r['comment']); ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="color:#999;">No reviews yet. Be the first to review!</p>
    <?php endif; ?>

</section>

<?php include '../includes/footer.php'; ?>