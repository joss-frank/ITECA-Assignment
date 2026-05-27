<?php
session_start();
include '../config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit();
}

$success = "";


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_role'])) {
    $uid = (int)$_POST['user_id'];
    $role = $_POST['role'];
    $allowed_roles = ['buyer', 'seller', 'admin', 'superadmin'];
    if (in_array($role, $allowed_roles) && $uid != $_SESSION['user_id']) {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        $stmt->bind_param("si", $role, $uid);
        $stmt->execute();
        $success = "Role updated successfully.";
    }
}

$users = $conn->query("SELECT * FROM users ORDER BY role DESC, name ASC");
include '../includes/header.php';
?>

<style>
.admin-wrap { padding: 40px 50px; overflow: hidden; max-width: 100%; }
.info-box { background: #fff4e0; border-left: 4px solid #b07c1a; padding: 16px 20px; border-radius: 10px; margin-bottom: 24px; color: #b07c1a; font-size: 0.875rem; }
.admin-table { width: 100%; border-collapse: collapse; background: white; border-radius: 15px; overflow: hidden; }
.admin-table th { text-align: left; font-size: 0.8rem; color: #999; padding: 14px 16px; font-weight: 600; background: #f9f9f9; }
.admin-table td { padding: 12px 16px; font-size: 0.875rem; border-bottom: 1px solid #f5efe6; vertical-align: middle; }
.badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.badge-buyer { background: #e8f0fe; color: #1a3c6e; }
.badge-seller { background: #fff4e0; color: #b07c1a; }
.badge-admin { background: #fde8ef; color: #c0395a; }
.badge-superadmin { background: #e0f5ec; color: #2e7d5a; }
.role-select { padding: 8px 12px; border-radius: 8px; border: 1px solid #ddd; font-family: 'Poppins', sans-serif; font-size: 0.875rem; }
.alert-success { background: #e0f5ec; color: #2e7d5a; padding: 12px; border-radius: 10px; margin-bottom: 20px; }
@media (max-width: 900px) { .admin-wrap { padding: 20px; } }
    
@media (max-width: 768px) {
    .admin-wrap { padding: 20px !important; overflow-x: hidden !important; }
    .admin-table { display: block; overflow-x: auto; white-space: nowrap; width: 100%; }
    body { overflow-x: hidden !important; }
    * { box-sizing: border-box; }
}    
    
</style>

<div class="admin-wrap">
    <h1 style="margin-bottom:4px;">Manage Roles</h1>
    <p style="color:#777;margin-bottom:20px;">Assign and change user roles across the platform.</p>

    <div class="info-box">
        🔐 This page is only accessible to Super Admins. Role changes take effect immediately.
    </div>

    <?php if ($success): ?><div class="alert-success"><?php echo $success; ?></div><?php endif; ?>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Current Role</th>
                <th>Change Role</th>
            </tr>
        </thead>
        <tbody>
            <?php while($u = $users->fetch_assoc()): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($u['name']); ?></strong></td>
                <td style="color:#999;font-size:0.8rem;"><?php echo htmlspecialchars($u['email']); ?></td>
                <td><span class="badge badge-<?php echo $u['role']; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                <td>
                    <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                    <form method="POST" action="" style="display:flex;gap:8px;align-items:center;">
                        <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                        <select name="role" class="role-select">
                            <?php foreach(['buyer','seller','admin','superadmin'] as $r): ?>
                                <option value="<?php echo $r; ?>" <?php echo $u['role'] == $r ? 'selected' : ''; ?>><?php echo ucfirst($r); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="update_role" class="btn" style="padding:8px 16px;font-size:0.85rem;">Save</button>
                    </form>
                    <?php else: ?>
                        <span style="color:#ccc;font-size:0.8rem;">Your account</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>