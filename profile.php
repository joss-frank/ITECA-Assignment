<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    if (empty($name) || empty($email)) {
        $error = "Name and email cannot be empty.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
       
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $check->bind_param("si", $email, $user_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "This email is already in use by another account.";
        } else {
            
            $pic_sql = "";
            $pic_value = null;

            if (!empty($_FILES['profile_pic']['name'])) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) {
                    $error = "Invalid image type.";
                } else {
                    $filename = 'profile_' . $user_id . '_' . uniqid() . '.' . $ext;
                    move_uploaded_file($_FILES['profile_pic']['tmp_name'], 'uploads/' . $filename);
                    $pic_sql = ", profile_pic = ?";
                    $pic_value = $filename;
                }
            }

            if (!$error) {
                if ($pic_value) {
                    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? $pic_sql WHERE user_id = ?");
                    $stmt->bind_param("sssi", $name, $email, $pic_value, $user_id);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE user_id = ?");
                    $stmt->bind_param("ssi", $name, $email, $user_id);
                }
                $stmt->execute();
                $_SESSION['name'] = $name;
                $success = "Profile updated successfully!";
            }
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $user_data = $conn->query("SELECT password FROM users WHERE user_id = $user_id")->fetch_assoc();

    if (!password_verify($current, $user_data['password'])) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new !== $confirm) {
        $error = "New passwords do not match.";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed, $user_id);
        $stmt->execute();
        $success = "Password changed successfully!";
    }
}


$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();


$orders = null;
if ($user['role'] == 'buyer') {
    $orders = $conn->query("SELECT * FROM orders WHERE buyer_id = $user_id ORDER BY created_at DESC LIMIT 5");
}


$product_count = 0;
$order_count = 0;
if ($user['role'] == 'seller') {
    $product_count = $conn->query("SELECT COUNT(*) as c FROM products WHERE seller_id = $user_id")->fetch_assoc()['c'];
    $order_count = $conn->query("SELECT COUNT(DISTINCT o.order_id) as c FROM orders o JOIN order_items oi ON o.order_id = oi.order_id JOIN products p ON oi.product_id = p.product_id WHERE p.seller_id = $user_id")->fetch_assoc()['c'];
}

$root = '';
include 'includes/header.php';
?>

<style>
.profile-wrap { padding: 50px; max-width: 900px; margin: 0 auto; }
.profile-top {
    background: white;
    border-radius: 20px;
    padding: 36px;
    display: flex;
    align-items: center;
    gap: 30px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}
.profile-avatar-wrap { position: relative; flex-shrink: 0; }
.profile-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #8ed1c6;
}
.profile-info h1 { font-size: 1.6rem; margin-bottom: 4px; }
.profile-info p { color: #777; margin: 0 0 8px; }
.role-badge { display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
.role-buyer { background: #e8f0fe; color: #1a3c6e; }
.role-seller { background: #fff4e0; color: #b07c1a; }
.role-admin { background: #fde8ef; color: #c0395a; }
.role-superadmin { background: #e0f5ec; color: #2e7d5a; }
.verified-badge { display: inline-block; background: #e0f5ec; color: #2e7d5a; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; margin-left: 8px; }
.profile-stats { display: flex; gap: 24px; margin-top: 16px; flex-wrap: wrap; }
.profile-stat .num { font-size: 1.4rem; font-weight: 800; color: #8ed1c6; }
.profile-stat .label { font-size: 0.8rem; color: #999; }
.tabs { display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap; }
.tab-btn { padding: 10px 22px; border-radius: 20px; border: none; background: white; color: #555; font-family: 'Poppins', sans-serif; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: 0.2s; }
.tab-btn.active { background: #8ed1c6; color: white; }
.tab-content { display: none; }
.tab-content.active { display: block; }
.form-card { background: white; border-radius: 20px; padding: 30px; }
.form-card h2 { font-size: 1.1rem; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #f5efe6; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: #333; }
.form-group input { width: 100%; padding: 12px 14px; border: 1px solid #ddd; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.95rem; box-sizing: border-box; }
.form-group input:focus { outline: none; border-color: #8ed1c6; }
.form-group input[type="file"] { padding: 10px; }
.btn-full { width: 100%; padding: 13px; background: #8ed1c6; color: white; border: none; border-radius: 25px; font-family: 'Poppins', sans-serif; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 8px; }
.btn-full:hover { background: #72bfb4; }
.alert-success { background: #e0f5ec; color: #2e7d5a; padding: 12px; border-radius: 10px; margin-bottom: 20px; }
.alert-error { background: #fdeef3; color: #c0395a; padding: 12px; border-radius: 10px; margin-bottom: 20px; }
.orders-list { list-style: none; padding: 0; margin: 0; }
.order-item { display: flex; justify-content: space-between; align-items: center; padding: 16px; background: #f9f9f9; border-radius: 12px; margin-bottom: 12px; flex-wrap: wrap; gap: 10px; }
.order-id { color: #8ed1c6; font-weight: 600; }
.badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.badge-pending { background: #fff4e0; color: #b07c1a; }
.badge-paid { background: #e8f0fe; color: #1a3c6e; }
.badge-shipped { background: #f0e8fe; color: #6b3fa0; }
.badge-delivered { background: #e0f5ec; color: #2e7d5a; }
.badge-cancelled { background: #fde8ef; color: #c0395a; }
.member-since { font-size: 0.8rem; color: #999; margin-top: 6px; }
@media (max-width: 700px) { .profile-wrap { padding: 20px; } .profile-top { flex-direction: column; align-items: flex-start; } }
</style>

<div class="profile-wrap">

    
    <div class="profile-top">
        <div class="profile-avatar-wrap">
            <img class="profile-avatar"
                 src="<?php echo $user['profile_pic'] ? 'uploads/' . htmlspecialchars($user['profile_pic']) : 'https://via.placeholder.com/100'; ?>"
                 alt="Profile Picture">
        </div>

        <div class="profile-info">
            <h1><?php echo htmlspecialchars($user['name']); ?></h1>
            <p><?php echo htmlspecialchars($user['email']); ?></p>

            <span class="role-badge role-<?php echo $user['role']; ?>">
                <?php echo ucfirst($user['role']); ?>
            </span>

            <?php if ($user['verified']): ?>
                <span class="verified-badge">✓ Verified</span>
            <?php endif; ?>

            <p class="member-since">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>

            <?php if ($user['role'] == 'seller'): ?>
                <div class="profile-stats">
                    <div class="profile-stat">
                        <div class="num"><?php echo $product_count; ?></div>
                        <div class="label">Products</div>
                    </div>
                    <div class="profile-stat">
                        <div class="num"><?php echo $order_count; ?></div>
                        <div class="label">Orders</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($success): ?><div class="alert-success"><?php echo $success; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert-error"><?php echo $error; ?></div><?php endif; ?>

    
    <div class="tabs">
        <button class="tab-btn active" onclick="showTab('edit')">Edit Profile</button>
        <button class="tab-btn" onclick="showTab('password')">Change Password</button>
        <?php if ($user['role'] == 'buyer'): ?>
            <button class="tab-btn" onclick="showTab('orders')">My Orders</button>
        <?php endif; ?>
        <?php if ($user['role'] == 'seller'): ?>
            <button class="tab-btn" onclick="showTab('shop')" >My Shop</button>
        <?php endif; ?>
    </div>

    
    <div class="tab-content active" id="tab-edit">
        <div class="form-card">
            <h2>Edit Profile</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>">
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
                <div class="form-group">
                    <label>Profile Picture</label>
                    <?php if ($user['profile_pic']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($user['profile_pic']); ?>" style="width:60px;height:60px;border-radius:50%;object-fit:cover;display:block;margin-bottom:10px;">
                    <?php endif; ?>
                    <input type="file" name="profile_pic" accept="image/*">
                </div>
                <button type="submit" name="update_profile" class="btn-full">Save Changes</button>
            </form>
        </div>
    </div>

    
    <div class="tab-content" id="tab-password">
        <div class="form-card">
            <h2>Change Password</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" placeholder="Enter current password">
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" placeholder="At least 6 characters">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" placeholder="Repeat new password">
                </div>
                <button type="submit" name="change_password" class="btn-full">Change Password</button>
            </form>
        </div>
    </div>

    
    <?php if ($user['role'] == 'buyer'): ?>
    <div class="tab-content" id="tab-orders">
        <div class="form-card">
            <h2>My Recent Orders</h2>
            <?php if ($orders && $orders->num_rows > 0): ?>
                <ul class="orders-list">
                    <?php while($o = $orders->fetch_assoc()): ?>
                        <li class="order-item">
                            <div>
                                <div class="order-id">#ORD-<?php echo $o['order_id']; ?></div>
                                <div style="font-size:0.8rem;color:#999;"><?php echo date('d M Y', strtotime($o['created_at'])); ?></div>
                            </div>
                            <div>R<?php echo number_format($o['total'], 2); ?></div>
                            <span class="badge badge-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
                <a href="buyer/orders.php" style="display:block;text-align:center;margin-top:20px;color:#8ed1c6;font-weight:600;">View all orders →</a>
            <?php else: ?>
                <p style="color:#999;text-align:center;padding:20px;">No orders yet. <a href="buyer/browse.php" style="color:#8ed1c6;">Start shopping!</a></p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if ($user['role'] == 'seller'): ?>
    <div class="tab-content" id="tab-shop">
        <div class="form-card">
            <h2>My Shop</h2>
            <p style="color:#777;margin-bottom:20px;">Manage your products and orders from your seller dashboard.</p>
            <div style="display:flex;gap:15px;flex-wrap:wrap;">
                <a href="seller/dashboard.php" class="btn">Go to Dashboard</a>
                <a href="seller/add_product.php" class="btn">Add New Product</a>
                <a href="seller/manage_products.php" class="btn">Manage Products</a>
                <a href="seller/orders.php" class="btn">View Orders</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
function showTab(tab) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    event.target.classList.add('active');
}
</script>

<?php include 'includes/footer.php'; ?>