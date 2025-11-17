<?php
// core/functions.php

// Luôn bắt đầu session ở đầu các file cần dùng
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Kiểm tra xem người dùng đã đăng nhập chưa
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Kiểm tra xem người dùng có phải là admin không
 */
function is_admin() {
    return (is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

/**
 * Yêu cầu đăng nhập để xem trang
 */
function require_login() {
    if (!is_logged_in()) {
        // Lưu lại trang muốn truy cập để redirect sau khi login
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: login.php");
        exit;
    }
}

/**
 * Yêu cầu quyền admin để xem trang
 */
function require_admin() {
    if (!is_admin()) {
        // Có thể chuyển về trang chủ hoặc trang "không có quyền"
        $_SESSION['error_message'] = "Bạn không có quyền truy cập trang này.";
        header("Location: ../index.php"); 
        exit;
    }
}
?>