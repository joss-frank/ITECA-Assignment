<?php
$host = 'sql107.infinityfree.com';
$dbname = 'if0_41939127_handmadehub_db';
$username = 'if0_41939127';
$password = 'ZHKIzyFSvu';
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>