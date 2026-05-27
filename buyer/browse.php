<?php
session_start();
include '../config.php';
include '../includes/header.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$sql = "SELECT p.*, u.name as seller_name, c.name as category_name 
        FROM products p 
        JOIN users u ON p.seller_id = u.user_id 
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE p.status = 'approved'";

if ($search) $sql .= " AND p.title LIKE '%" . $conn->real_escape_string($search) . "%'";
if ($category) $sql .= " AND p.category_id = $category";
$sql .= " ORDER BY p.created_at DESC";

$result = $conn->query($sql);
$cats = $conn->query("SELECT * FROM categories");
?>

<section class="page-header">
    <h1>Shop Handmade Products</h1>
</section>

<section class="filters">
    <form method="GET" action="" style="display:flex; gap:15px; flex-wrap:wrap; justify-content:center;">
        <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="category">
            <option value="0">All Categories</option>
            <?php while($cat = $cats->fetch_assoc()): ?>
                <option value="<?php echo $cat['category_id']; ?>" <?php echo $category == $cat['category_id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit" class="btn">Search</button>
    </form>
</section>

<section class="products">
    <div class="product-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($p = $result->fetch_assoc()): ?>
                <div class="product">
                    <img src="<?php echo $p['image_url'] ? '../uploads/' . htmlspecialchars($p['image_url']) : 'https://via.placeholder.com/250x200'; ?>" alt="<?php echo htmlspecialchars($p['title']); ?>">
                    <h3><?php echo htmlspecialchars($p['title']); ?></h3>
                    <p>R<?php echo number_format($p['price'], 2); ?></p>
                    <p style="font-size:0.8rem;color:#999;">by <?php echo htmlspecialchars($p['seller_name']); ?></p>
                    <a href="product.php?id=<?php echo $p['product_id']; ?>" class="btn">View</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No products found.</p>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>