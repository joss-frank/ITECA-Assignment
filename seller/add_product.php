<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../auth/login.php");
    exit();
}

$error = "";
$success = "";
$edit_product = null;


if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_product = $conn->query("SELECT * FROM products WHERE product_id = $edit_id AND seller_id = " . $_SESSION['user_id'])->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];
    $seller_id = $_SESSION['user_id'];

    if (empty($title) || empty($price) || empty($stock)) {
        $error = "Please fill in all required fields.";
    } else {
        $image_url = null;

        
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error = "Invalid image type. Use JPG, PNG, GIF or WEBP.";
            } else {
                $filename = uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $filename);
                $image_url = $filename;
            }
        }

        if (!$error) {
            if (isset($_POST['product_id']) && !empty($_POST['product_id'])) {
                // UPDATE existing product
                $pid = (int)$_POST['product_id'];
                $sql = "UPDATE products SET title=?, description=?, price=?, stock=?, category_id=?";
                if ($image_url) $sql .= ", image_url=?";
                $sql .= " WHERE product_id=? AND seller_id=?";

                if ($image_url) {
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssdissis", $title, $description, $price, $stock, $category_id, $image_url, $pid, $seller_id);
                } else {
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssdiis ii", $title, $description, $price, $stock, $category_id, $pid, $seller_id);
                }
                $stmt->execute();
                $success = "Product updated successfully!";
            } else {
                
                $stmt = $conn->prepare("INSERT INTO products (seller_id, title, description, price, stock, category_id, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
                $stmt->bind_param("issdiss", $seller_id, $title, $description, $price, $stock, $category_id, $image_url);
                $stmt->execute();
                $success = "Product added! It will be visible after admin approval.";
            }
        }
    }
}

$categories = $conn->query("SELECT * FROM categories");
include '../includes/header.php';
?>

<style>
.form-container { max-width: 600px; margin: 60px auto; background: white; padding: 40px; border-radius: 20px; }
.form-container h2 { margin-bottom: 24px; }
.form-group { margin-bottom: 18px; }
.form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: #333; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px 14px; border: 1px solid #ddd; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.95rem; box-sizing: border-box; }
.form-group textarea { height: 100px; resize: vertical; }
.alert-error { background: #fdeef3; color: #c0395a; padding: 12px; border-radius: 10px; margin-bottom: 16px; }
.alert-success { background: #e0f5ec; color: #2e7d5a; padding: 12px; border-radius: 10px; margin-bottom: 16px; }
</style>

<div class="form-container">
    <h2><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></h2>

    <?php if ($error): ?><div class="alert-error"><?php echo $error; ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert-success"><?php echo $success; ?> <a href="dashboard.php">Back to dashboard</a></div><?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <?php if ($edit_product): ?>
            <input type="hidden" name="product_id" value="<?php echo $edit_product['product_id']; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>Product Name *</label>
            <input type="text" name="title" value="<?php echo $edit_product ? htmlspecialchars($edit_product['title']) : ''; ?>" placeholder="e.g. Crochet Hat">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" placeholder="Describe your product..."><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
        </div>
        <div class="form-group">
            <label>Price (R) *</label>
            <input type="number" name="price" step="0.01" value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>" placeholder="e.g. 150">
        </div>
        <div class="form-group">
            <label>Stock Quantity *</label>
            <input type="number" name="stock" value="<?php echo $edit_product ? $edit_product['stock'] : ''; ?>" placeholder="e.g. 5">
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category_id">
                <?php while($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $cat['category_id']; ?>" <?php echo ($edit_product && $edit_product['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Product Image</label>
            <?php if ($edit_product && $edit_product['image_url']): ?>
                <img src="../uploads/<?php echo htmlspecialchars($edit_product['image_url']); ?>" style="width:100px;border-radius:10px;margin-bottom:10px;display:block;">
            <?php endif; ?>
            <input type="file" name="image" accept="image/*">
        </div>

        <button type="submit" class="btn" style="width:100%;">
            <?php echo $edit_product ? 'Update Product' : 'Add Product'; ?>
        </button>
        <a href="dashboard.php" style="display:block;text-align:center;margin-top:15px;color:#999;">Cancel</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>