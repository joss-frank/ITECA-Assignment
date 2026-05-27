<?php
session_start();
include '../config.php';
include '../includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: browse.php");
    exit();
}

$seller_id = (int)$_GET['id'];


$seller = $conn->query("SELECT * FROM users WHERE user_id = $seller_id AND role = 'seller'")->fetch_assoc();

if (!$seller) {
    echo "<p style='text-align:center;padding:50px;'>Seller not found.</p>";
    include '../includes/footer.php';
    exit();
}


$products = $conn->query("SELECT * FROM products WHERE seller_id = $seller_id AND status = 'approved' ORDER BY created_at DESC");


$rating_data = $conn->query("SELECT AVG(r.rating) as avg_rating, COUNT(r.review_id) as total_reviews FROM reviews r JOIN products p ON r.product_id = p.product_id WHERE p.seller_id = $seller_id")->fetch_assoc();
$avg_rating = round($rating_data['avg_rating'] ?? 0, 1);
$total_reviews = $rating_data['total_reviews'] ?? 0;


$total_sales = $conn->query("SELECT COUNT(DISTINCT oi.order_id) as total FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE p.seller_id = $seller_id")->fetch_assoc()['total'];
?>

<style>
.profile-wrap { padding: 60px 50px; }
.seller-header-card {
    background: white;
    border-radius: 20px;
    padding: 40px;
    display: flex;
    align-items: center;
    gap: 30px;
    margin-bottom: 40px;
    flex-wrap: wrap;
}
.seller-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    background: #f5efe6;
    flex-shrink: 0;
}
.seller-meta h1 { font-size: 1.8rem; margin-bottom: 6px; }
.seller-meta p { color: #777; margin: 0 0 10px; }
.seller-stats {
    display: flex;
    gap: 30px;
    margin-top: 16px;
    flex-wrap: wrap;
}
.seller-stat { text-align: center; }
.seller-stat .num { font-size: 1.4rem; font-weight: 800; color: #8ed1c6; }
.seller-stat .label { font-size: 0.8rem; color: #999; }
.stars { color: #f5a623; font-size: 1.1rem; }
.verified-badge {
    display: inline-block;
    background: #e0f5ec;
    color: #2e7d5a;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-top: 8px;
}
.section-title {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 20px;
    color: #333;
}
.product-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 50px;
}
.product {
    background: white;
    padding: 15px;
    border-radius: 15px;
    text-align: center;
    transition: 0.3s;
}
.product:hover { transform: translateY(-5px); }
.product img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 10px;
    margin-bottom: 10px;
}
.product h3 { font-size: 0.95rem; margin-bottom: 4px; }
.product span { color: #8ed1c6; font-weight: 700; }
.reviews-section { background: white; border-radius: 20px; padding: 30px; }
.review-card {
    padding: 20px 0;
    border-bottom: 1px solid #f5efe6;
}
.review-card:last-child { border-bottom: none; }
.review-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 8px; }
.reviewer-name { font-weight: 600; font-size: 0.95rem; }
.review-date { font-size: 0.8rem; color: #999; }
.review-stars { color: #f5a623; }
.review-comment { color: #555; font-size: 0.9rem; line-height: 1.6; }
.no-reviews { text-align: center; padding: 30px; color: #999; }
.empty-products { text-align: center; padding: 40px; color: #999; background: white; border-radius: 15px; }
@media (max-width: 900px) {
    .product-grid { grid-template-columns: repeat(2, 1fr); }
    .profile-wrap { padding: 20px; }
    .seller-header-card { flex-direction: column; align-items: flex-start; }
}
@media (max-width: 600px) {
    .product-grid { grid-template-columns: 1fr; }
    .seller-stats { gap: 20px; }
}
</style>

<div class="profile-wrap">

    
    <div class="seller-header-card">
        <img class="seller-avatar"
             src="<?php echo $seller['profile_pic'] ? '../uploads/' . htmlspecialchars($seller['profile_pic']) : 'https://via.placeholder.com/100'; ?>"
             alt="<?php echo htmlspecialchars($seller['name']); ?>">

        <div class="seller-meta">
            <h1><?php echo htmlspecialchars($seller['name']); ?></h1>
            <p>HandmadeHub Seller</p>

            <?php if ($seller['verified']): ?>
                <span class="verified-badge">✓ Verified Seller</span>
            <?php endif; ?>

            <div class="seller-stats">
                <div class="seller-stat">
                    <div class="num"><?php echo $products ? $products->num_rows : 0; ?></div>
                    <div class="label">Products</div>
                </div>
                <div class="seller-stat">
                    <div class="num"><?php echo $total_sales; ?></div>
                    <div class="label">Sales</div>
                </div>
                <div class="seller-stat">
                    <div class="num">
                        <?php if ($avg_rating > 0): ?>
                            <span class="stars">
                                <?php
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= round($avg_rating) ? '★' : '☆';
                                }
                                ?>
                            </span>
                            <?php echo $avg_rating; ?>
                        <?php else: ?>
                            <span style="color:#ccc;">No ratings yet</span>
                        <?php endif; ?>
                    </div>
                    <div class="label"><?php echo $total_reviews; ?> review<?php echo $total_reviews != 1 ? 's' : ''; ?></div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="section-title">Products by <?php echo htmlspecialchars($seller['name']); ?></div>

    <?php
    
    $products = $conn->query("SELECT * FROM products WHERE seller_id = $seller_id AND status = 'approved' ORDER BY created_at DESC");
    ?>

    <?php if ($products && $products->num_rows > 0): ?>
        <div class="product-grid">
            <?php while($p = $products->fetch_assoc()): ?>
                <div class="product">
                    <img src="<?php echo $p['image_url'] ? '../uploads/' . htmlspecialchars($p['image_url']) : 'https://via.placeholder.com/250x160'; ?>"
                         alt="<?php echo htmlspecialchars($p['title']); ?>">
                    <h3><?php echo htmlspecialchars($p['title']); ?></h3>
                    <span>R<?php echo number_format($p['price'], 2); ?></span>
                    <br><br>
                    <a href="product.php?id=<?php echo $p['product_id']; ?>" class="btn">View</a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-products">
            <p>This seller has no products listed yet.</p>
        </div>
    <?php endif; ?>

    
    <div class="section-title">Customer Reviews</div>
    <div class="reviews-section">
        <?php
        $reviews = $conn->query("SELECT r.*, u.name as buyer_name, p.title as product_title FROM reviews r JOIN users u ON r.buyer_id = u.user_id JOIN products p ON r.product_id = p.product_id WHERE p.seller_id = $seller_id ORDER BY r.created_at DESC");
        ?>

        <?php if ($reviews && $reviews->num_rows > 0): ?>
            <?php while($r = $reviews->fetch_assoc()): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div>
                            <span class="reviewer-name"><?php echo htmlspecialchars($r['buyer_name']); ?></span>
                            <span style="color:#999;font-size:0.8rem;margin-left:10px;">on <?php echo htmlspecialchars($r['product_title']); ?></span>
                        </div>
                        <div>
                            <span class="review-stars">
                                <?php for ($i = 1; $i <= 5; $i++) echo $i <= $r['rating'] ? '★' : '☆'; ?>
                            </span>
                            <span class="review-date"><?php echo date('d M Y', strtotime($r['created_at'])); ?></span>
                        </div>
                    </div>
                    <p class="review-comment"><?php echo htmlspecialchars($r['comment']); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-reviews">
                <p>No reviews yet for this seller.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include '../includes/footer.php'; ?>