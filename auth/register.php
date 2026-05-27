<?php
session_start();
include '../config.php';
include '../includes/header.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $role = $_POST['role'];

    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed, $role);

            if ($stmt->execute()) {
                $success = "Account created! You can now log in.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HandmadeHub – Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .auth-section { display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 40px 20px; }
        .auth-card { background: white; padding: 40px; border-radius: 20px; width: 100%; max-width: 480px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .auth-card h2 { text-align: center; font-size: 1.8rem; margin-bottom: 8px; }
        .auth-card p.subtitle { text-align: center; color: #777; margin-bottom: 28px; font-size: 0.9rem; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: #333; }
        .form-group input, .form-group select { width: 100%; padding: 12px 14px; border: 1px solid #ddd; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.95rem; box-sizing: border-box; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #8ed1c6; }
        .role-options { display: flex; gap: 12px; margin-top: 6px; }
        .role-card { flex: 1; padding: 14px; border: 2px solid #ddd; border-radius: 12px; text-align: center; cursor: pointer; transition: 0.2s; }
        .role-card:hover { border-color: #8ed1c6; }
        .role-card.selected { border-color: #8ed1c6; background: #f0faf8; }
        .role-icon { font-size: 1.5rem; margin-bottom: 6px; }
        .role-label { font-size: 0.85rem; font-weight: 600; color: #333; }
        .role-desc { font-size: 0.75rem; color: #888; margin-top: 3px; }
        .btn-full { width: 100%; padding: 13px; background: #8ed1c6; color: white; border: none; border-radius: 25px; font-family: 'Poppins', sans-serif; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 8px; }
        .btn-full:hover { background: #72bfb4; }
        .auth-footer { text-align: center; margin-top: 20px; font-size: 0.875rem; color: #777; }
        .auth-footer a { color: #8ed1c6; text-decoration: none; font-weight: 600; }
        .alert-error { background: #fdeef3; color: #c0395a; padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 0.875rem; }
        .alert-success { background: #e0f5ec; color: #2e7d5a; padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 0.875rem; }
    </style>
</head>
<body>


<section class="auth-section">
    <div class="auth-card">
        <h2>Create Account</h2>
        <p class="subtitle">Join HandmadeHub today</p>

        <?php if ($error): ?>
            <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert-success"><?php echo $success; ?> <a href="login.php">Login here</a></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>I want to:</label>
                <div class="role-options">
                    <div class="role-card selected" id="buyerCard" onclick="selectRole('buyer')">
                        <div class="role-icon">🛍</div>
                        <div class="role-label">Buy</div>
                        <div class="role-desc">Shop handmade goods</div>
                    </div>
                    <div class="role-card" id="sellerCard" onclick="selectRole('seller')">
                        <div class="role-icon">🧵</div>
                        <div class="role-label">Sell</div>
                        <div class="role-desc">List my products</div>
                    </div>
                </div>
                <input type="hidden" name="role" id="roleInput" value="buyer">
            </div>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Your full name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="you@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="At least 6 characters">
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm" placeholder="Repeat your password">
            </div>

            <button type="submit" class="btn-full">Create Account</button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</section>

<footer><p>© 2026 HandmadeHub</p></footer>

<script>
function selectRole(role) {
    document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
    document.getElementById(role + 'Card').classList.add('selected');
    document.getElementById('roleInput').value = role;
}
</script>
</body>
</html>