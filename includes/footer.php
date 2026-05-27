<?php
$is_live = $_SERVER['HTTP_HOST'] !== 'localhost';
$base_url = $is_live ? '' : '/handmadehub';
?>

<button id="scrollTopBtn" style="display:none;position:fixed;bottom:30px;right:30px;background:#8ed1c6;color:white;border:none;border-radius:50%;width:45px;height:45px;font-size:1.2rem;cursor:pointer;box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:999;">↑</button>

<footer>
    <p>© 2026 HandmadeHub</p>
</footer>

<script src="<?php echo $base_url; ?>/js/main.js"></script>
</body>
</html>