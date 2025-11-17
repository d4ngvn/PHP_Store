<?php
// config/database.php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Thay bằng user của bạn
define('DB_PASS', '');          // Thay bằng password của bạn
define('DB_NAME', 'store');  // Thay bằng tên CSDL của bạn

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    // Đặt chế độ báo lỗi PDO thành exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Đặt chế độ fetch mặc định là mảng kết hợp
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("ERROR: Không thể kết nối. " . $e->getMessage());
}
?>