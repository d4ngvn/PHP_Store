<?php
require_once '../../config/database.php';
require_once '../../core/functions.php';

// **BẢO MẬT: Yêu cầu quyền admin**
require_admin();

$page_title = "Quản Lý Đơn Hàng";
include '../includes/header.php'; // Chú ý đường dẫn

$action = $_GET['action'] ?? 'list';
$order_id = $_GET['id'] ?? null;

$message = $_SESSION['message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['message'], $_SESSION['error_message']);

// Xử lý Cập nhật (Update Status)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        $id_to_update = $_POST['order_id'];
        $new_status = $_POST['status'];

        $sql = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$new_status, $id_to_update]);
        $_SESSION['message'] = "Cập nhật trạng thái đơn hàng #$id_to_update thành công!";
        
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Lỗi: " . $e->getMessage();
    }
    // Chuyển hướng lại trang chi tiết hoặc trang danh sách
    header("Location: manage_orders.php?action=view&id=" . $id_to_update);
    exit;
}

?>

<h2 class="mb-4">Quản Lý Đơn Hàng</h2>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>


<?php if ($action === 'view' && $order_id): ?>
    <?php
    try {
        // 1. Lấy thông tin đơn hàng chính + thông tin người dùng
        $sql_order = "SELECT orders.*, users.username, users.full_name, users.address 
                      FROM orders 
                      JOIN users ON orders.user_id = users.id 
                      WHERE orders.id = ?";
        $stmt_order = $pdo->prepare($sql_order);
        $stmt_order->execute([$order_id]);
        $order = $stmt_order->fetch();

        if (!$order) {
            echo "<div class='alert alert-danger'>Không tìm thấy đơn hàng.</div>";
            include '../includes/footer.php';
            exit;
        }

        // 2. Lấy các sản phẩm trong đơn hàng
        $sql_items = "SELECT oi.*, p.name AS product_name, p.image_url 
                      FROM order_items oi
                      JOIN products p ON oi.product_id = p.id
                      WHERE oi.order_id = ?";
        $stmt_items = $pdo->prepare($sql_items);
        $stmt_items->execute([$order_id]);
        $items = $stmt_items->fetchAll();

    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Lỗi CSDL: " . $e->getMessage() . "</div>";
        include '../includes/footer.php';
        exit;
    }
    ?>
    <h3 class="mb-3">Chi tiết Đơn hàng #<?php echo $order['id']; ?></h3>
    <a href="manage_orders.php" class="btn btn-secondary mb-3">Quay lại danh sách</a>
    
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5>Các sản phẩm</h5>
                </div>
                <div class="card-body">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Giá lúc mua</th>
                                <th>Số lượng</th>
                                <th>Tổng phụ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo number_format($item['price_at_purchase']); ?> VNĐ</td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo number_format($item['price_at_purchase'] * $item['quantity']); ?> VNĐ</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5>Thông tin Đơn hàng</h5>
                </div>
                <div class="card-body">
                    <p><strong>Khách hàng:</strong> <?php echo htmlspecialchars($order['full_name'] ?? $order['username']); ?></p>
                    <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                    <p class="fs-4"><strong>Tổng tiền:</strong> <span class="text-danger fw-bold"><?php echo number_format($order['total_amount']); ?> VNĐ</span></p>
                    <hr>
                    <form action="manage_orders.php" method="POST">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <label for="status" class="form-label fw-bold">Trạng thái:</label>
                        <select name="status" id="status" class="form-select">
                            <option value="pending" <?php echo ($order['status'] == 'pending') ? 'selected' : ''; ?>>Đang chờ xử lý</option>
                            <option value="completed" <?php echo ($order['status'] == 'completed') ? 'selected' : ''; ?>>Đã hoàn thành</option>
                            <option value="cancelled" <?php echo ($order['status'] == 'cancelled') ? 'selected' : ''; ?>>Đã hủy</option>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-primary w-100 mt-3">Cập nhật</button>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5>Địa chỉ giao hàng</h5>
                </div>
                <div class="card-body">
                    <?php echo nl2br(htmlspecialchars($order['address'])); ?>
                </div>
            </div>
        </div>
    </div>


<?php else: ?>
    <?php
    $sql_list = "SELECT orders.*, users.username 
                 FROM orders 
                 JOIN users ON orders.user_id = users.id 
                 ORDER BY created_at DESC";
    $stmt_list = $pdo->query($sql_list);
    $orders = $stmt_list->fetchAll();
    ?>
    <div class="card">
        <div class="card-header">
            <h5>Toàn bộ Đơn Hàng</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Khách hàng</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            <td><?php echo number_format($order['total_amount']); ?> VNĐ</td>
                            <td>
                                <?php 
                                $status_class = 'bg-secondary';
                                if ($order['status'] == 'pending') $status_class = 'bg-warning text-dark';
                                if ($order['status'] == 'completed') $status_class = 'bg-success';
                                if ($order['status'] == 'cancelled') $status_class = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($order['status']); ?></span>
                            </td>
                            <td>
                                <a href="manage_orders.php?action=view&id=<?php echo $order['id']; ?>" class="btn btn-info btn-sm">Xem chi tiết</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php endif; ?>

<?php include '../includes/footer.php'; // Chú ý đường dẫn ?>