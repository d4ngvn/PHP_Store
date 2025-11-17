<?php
// core/functions.php sẽ tự động gọi session_start()
require_once '../core/functions.php'; 

// Xóa tất cả các biến session
$_SESSION = [];

// Hủy session
session_destroy();

// (Tùy chọn) Xóa cookie session nếu có
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Chuyển hướng về trang chủ
header("Location: index.php");
exit;
?>