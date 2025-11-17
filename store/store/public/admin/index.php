<?php
require_once '../../config/database.php';
require_once '../../core/functions.php';

// **BẢO MẬT: Yêu cầu quyền admin**
require_admin();

$page_title = "Trang Quản Trị";
include '../includes/header.php'; // Chú ý đường dẫn

// Đếm số lượng
$user_count = $pdo->query("SELECT count(id) FROM users")->fetchColumn();
$product_count = $pdo->query("SELECT count(id) FROM products")->fetchColumn();
$order_count = $pdo->query("SELECT count(id) FROM orders WHERE status = 'pending'")->fetchColumn();

?>

<h2 class="mb-4">Dashboard</h2>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Người Dùng</h5>
                <p class="card-text fs-3"><?php echo $user_count; ?></p>
                <a href="manage_users.php" class="text-white stretched-link">Xem chi tiết</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Sản Phẩm</h5>
                <p class="card-text fs-3"><?php echo $product_count; ?></p>
                <a href="manage_products.php" class="text-white stretched-link">Xem chi tiết</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Đơn Hàng (Chờ xử lý)</h5>
                <p class="card-text fs-3"><?php echo $order_count; ?></p>
                <a href="manage_orders.php" class="text-white stretched-link">Xem chi tiết</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; // Chú ý đường dẫn ?>