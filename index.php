<?php
session_start();
$root = '';
include 'includes/header.php';
include 'config.php';


$result = $conn->query("SELECT p.*, u.name as seller_name FROM products p JOIN users u ON p.seller_id = u.user_id WHERE p.status = 'approved' ORDER BY p.created_at DESC LIMIT 4");
?>


<section class="hero" style="background-image: url('../images/hero-bg.png'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <h1>Support Local. Discover Handmade.</h1>
    <p>Buy and sell handmade goods safely across South Africa</p>
    <div class="hero-buttons">
        <a href="buyer/browse.php" class="btn" style="width:auto;display:inline-block;min-width:0;max-width:none;">Browse Products</a>
        <a href="auth/register.php" class="btn" style="width:auto;display:inline-block;min-width:0;max-width:none;">Start Selling</a>
    </div>
</section>


<section class="features">
    <div class="card"><h3>Shop Handmade</h3><p>Discover unique local products</p></div>
    <div class="card"><h3>Sell Products</h3><p>Start your own online shop</p></div>
    <div class="card"><h3>Secure Payments</h3><p>Safe and protected transactions</p></div>
    <div class="card"><h3>Delivery Options</h3><p>Nationwide delivery services</p></div>
</section>


<section class="products">
    <h2>Featured Products</h2>
    <div class="product-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($p = $result->fetch_assoc()): ?>
                <div class="product">
                    <img src="<?php echo $p['image_url'] ? 'uploads/' . htmlspecialchars($p['image_url']) : 'https://via.placeholder.com/200'; ?>" alt="<?php echo htmlspecialchars($p['title']); ?>">
                    <h3><?php echo htmlspecialchars($p['title']); ?></h3>
                    <span>R<?php echo number_format($p['price'], 2); ?></span>
                    <br><br>
                    <a href="buyer/product.php?id=<?php echo $p['product_id']; ?>" class="btn">View</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No products listed yet. <a href="auth/register.php">Be the first to sell!</a></p>
        <?php endif; ?>
    </div>
</section>


<section class="trust">
    <h2>Why Choose HandmadeHub?</h2>
    <p>✔ Verified Sellers</p>
    <p>✔ Secure Payments</p>
    <p>✔ Reliable Delivery</p>
</section>

<?php include 'includes/footer.php'; ?>