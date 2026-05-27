<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header("Location: ../auth/login.php");
    exit();
}

$success = "";
$error = "";


if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    if ($del_id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE user_id = $del_id");
        $success = "User deleted successfully.";
    } else {
        $error = "You cannot delete your own account.";
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_role'])) {
    $uid = (int)$_POST['user_id'];
    $role = $_POST['role'];
    $allowed_roles = ['buyer', 'seller', 'admin', 'superadmin'];
    if (in_array($role, $allowed_roles)) {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        $stmt->bind_param("si", $role, $uid);
        $stmt->execute();
        $success = "User role updated successfully.";
    }
}


if (isset($_GET['verify'])) {
    $vid = (int)$_GET['verify'];
    $current = $conn->query("SELECT verified FROM users WHERE user_id = $vid")->fetch_assoc()['verified'];
    $new = $current ? 0 : 1;
    $conn->query("UPDATE users SET verified = $new WHERE user_id = $vid");
    $success = "User verification updated.";
}

$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
include '../includes/header.php';
?>

<style>
.admin-wrap { padding: 40px 50px; }
.admin-table { width: 100%; border-collapse: collapse; background: white; border-radius: 15px; overflow: hidden; }
.admin-table th { text-align: left; font-size: 0.8rem; color: #999; padding: 14px 16px; font-weight: 600; background: #f9f9f9; }
.admin-table td { padding: 12px 16px; font-size: 0.875rem; border-bottom: 1px solid #f5efe6; vertical-align: middle; }
.badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.badge-buyer { background: #e8f0fe; color: #1a3c6e; }
.badge-seller { background: #fff4e0; color: #b07c1a; }
.badge-admin { background: #fde8ef; color: #c0395a; }
.badge-superadmin { background: #e0f5ec; color: #2e7d5a; }
.btn-sm { padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; border: none; cursor: pointer; font-family: 'Poppins', sans-serif; text-decoration: none; display: inline-block; }
.btn-verify { background: #e0f5ec; color: #2e7d5a; }
.btn-unverify { background: #fff4e0; color: #b07c1a; }
.btn-delete { background: #fde8ef; color: #c0395a; }
.role-select { padding: 6px 10px; border-radius: 8px; border: 1px solid #ddd; font-family: 'Poppins', sans-serif; font-size: 0.8rem; }
.alert-success { background: #e0f5ec; color: #2e7d5a; padding: 12px; border-radius: 10px; margin-bottom: 20px; }
.alert-error { background: #fdeef3; color: #c0395a; padding: 12px; border-radius: 10px; margin-bottom: 20px; }
@media (max-width: 900px) { .admin-wrap { padding: 20px; } .admin-table { font-size: 0.8rem; } }
    
@media (max-width: 768px) {
    .admin-wrap { padding: 20px !important; }
    .stats-grid { grid-template-columns: repeat(2, 1fr) !important; }
    .admin-grid { grid-template-columns: 1fr !important; }
    .admin-table { display: block; overflow-x: auto; white-space: nowrap; }
    body { overflow-x: hidden; }
    * { max-width: 100%; box-sizing: border-box; }
}    
    
</style>

<div class="admin-wrap">
    <h1 style="margin-bottom:4px;">Manage Users</h1>
    <p style="color:#777;margin-bottom:24px;">View, edit roles, verify and delete users.</p>

    <?php if ($success): ?><div class="alert-success"><?php echo $success; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert-error"><?php echo $error; ?></div><?php endif; ?>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Verified</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($u = $users->fetch_assoc()): ?>
            <tr>
                <td style="color:#999;">#<?php echo $u['user_id']; ?></td>
                <td><strong><?php echo htmlspecialchars($u['name']); ?></strong></td>
                <td style="color:#999;font-size:0.8rem;"><?php echo htmlspecialchars($u['email']); ?></td>
                <td>
                    <?php if ($_SESSION['role'] == 'superadmin'): ?>
                    <form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                        <select name="role" class="role-select" onchange="this.form.submit()">
                            <?php foreach(['buyer','seller','admin','superadmin'] as $r): ?>
                                <option value="<?php echo $r; ?>" <?php echo $u['role'] == $r ? 'selected' : ''; ?>><?php echo ucfirst($r); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="update_role" value="1">
                    </form>
                    <?php else: ?>
                        <span class="badge badge-<?php echo $u['role']; ?>"><?php echo ucfirst($u['role']); ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="users.php?verify=<?php echo $u['user_id']; ?>" class="btn-sm <?php echo $u['verified'] ? 'btn-unverify' : 'btn-verify'; ?>">
                        <?php echo $u['verified'] ? '✓ Verified' : '✗ Unverified'; ?>
                    </a>
                </td>
                <td style="color:#999;font-size:0.8rem;"><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                <td>
                    <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                        <a href="users.php?delete=<?php echo $u['user_id']; ?>" class="btn-sm btn-delete" onclick="return confirm('Delete this user?')">Delete</a>
                    <?php else: ?>
                        <span style="color:#ccc;font-size:0.8rem;">You</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>