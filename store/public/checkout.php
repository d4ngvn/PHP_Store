<?php
require_once '../config/database.php';
$page_title = "Thanh Toán";
include 'includes/header.php'; // Đã có session_start()

// **BẢO MẬT: Yêu cầu đăng nhập**
require_login();

// 1. Kiểm tra giỏ hàng
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'];
$total_price = 0;
$cart_items = [];

$message = "";
$error_message = "";

// 2. Lấy thông tin giỏ hàng và tính tổng tiền (Giống cart.php)
try {
    $product_ids = array_keys($cart);
    if (empty($product_ids)) {
        throw new Exception("Giỏ hàng trống.");
    }

    $placeholders = rtrim(str_repeat('?,', count($product_ids)), ',');
    // Prepare a proper query to fetch product fields we need
    $sql_products = "SELECT id, name, price, stock_quantity FROM products WHERE id IN ($placeholders)";
    $stmt_products = $pdo->prepare($sql_products);
    $stmt_products->execute($product_ids);

    // Fetch rows and index by product id for easy lookup later
    $rows = $stmt_products->fetchAll(PDO::FETCH_ASSOC);
    $products_in_db = [];
    foreach ($rows as $row) {
        $products_in_db[$row['id']] = $row;
    }

    foreach ($cart as $product_id => $quantity) {
        if (!isset($products_in_db[$product_id])) continue; // Sản phẩm đã bị xóa
        
        $product = $products_in_db[$product_id];
        $subtotal = $product['price'] * $quantity;
        
        $cart_items[] = [
            'id' => $product_id,
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
        $total_price += $subtotal;
    }

    // Lấy thông tin địa chỉ người dùng
    $stmt_user = $pdo->prepare("SELECT full_name, address FROM users WHERE id = ?");
    $stmt_user->execute([$user_id]);
    $user = $stmt_user->fetch();

} catch (PDOException $e) {
    $error_message = "Lỗi khi tải thông tin: " . $e->getMessage();
} catch (Exception $e) {
    $error_message = "Lỗi: " . $e->getMessage();
}

// 3. XỬ LÝ ĐẶT HÀNG (Khi người dùng nhấn nút)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($cart_items)) {
    
    // **BẮT ĐẦU TRANSACTION**
    // Transaction đảm bảo tất cả các lệnh (trừ kho, tạo đơn hàng) cùng thành công
    // Hoặc cùng thất bại (rollback) nếu có lỗi
    try {
        $pdo->beginTransaction();

        // Bước A: Kiểm tra kho hàng một lần nữa (rất quan trọng)
        foreach ($cart as $product_id => $quantity) {
            $stmt_check = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ? FOR UPDATE"); // 'FOR UPDATE' để khóa dòng
            $stmt_check->execute([$product_id]);
            $stock = $stmt_check->fetchColumn();

            if ($stock < $quantity) {
                throw new Exception("Sản phẩm '" . $products_in_db[$product_id]['name'] . "' không đủ hàng (chỉ còn $stock).");
            }
        }

        // Bước B: Tạo đơn hàng (Bảng 'orders')
        $sql_order = "INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')";
        $stmt_order = $pdo->prepare($sql_order);
        $stmt_order->execute([$user_id, $total_price]);
        
        // Lấy ID của đơn hàng vừa tạo
        $order_id = $pdo->lastInsertId();

        // Bước C: Thêm các mặt hàng vào 'order_items' VÀ Trừ kho
        $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)";
        $sql_update_stock = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
        
        $stmt_item = $pdo->prepare($sql_item);
        $stmt_update_stock = $pdo->prepare($sql_update_stock);

        foreach ($cart_items as $item) {
            // Thêm vào order_items
            $stmt_item->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
            // Trừ kho
            $stmt_update_stock->execute([$item['quantity'], $item['id']]);
        }

        // Bước D: Hoàn tất
        $pdo->commit(); // Ghi tất cả thay đổi vào CSDL

        // Bước E: Xóa giỏ hàng và thông báo
        unset($_SESSION['cart']);
        $message = "Đặt hàng thành công! Mã đơn hàng của bạn là #$order_id.";

        // Xóa cart_items để ẩn form
        $cart_items = []; 

    } catch (Exception $e) {
        // Nếu có lỗi, hủy bỏ mọi thay đổi
        $pdo->rollBack();
        $error_message = "Lỗi khi đặt hàng: " . $e->getMessage();
    }
}

?>

<h2 class="mb-4">Xác Nhận Thanh Toán</h2>

<?php if ($message): ?>
    <div class="alert alert-success">
        <?php echo $message; ?>
        <br>
    </div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>


<?php if (!empty($cart_items)): ?>
<div class="row g-5">
    <div class="col-md-5 col-lg-4 order-md-last">
        <h4 class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-primary">Giỏ hàng của bạn</span>
            <span class="badge bg-primary rounded-pill"><?php echo count($cart_items); ?></span>
        </h4>
        <ul class="list-group mb-3">
            <?php foreach ($cart_items as $item): ?>
            <li class="list-group-item d-flex justify-content-between lh-sm">
                <div>
                    <h6 class="my-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                    <small class="text-muted">Số lượng: <?php echo $item['quantity']; ?></small>
                </div>
                <span class="text-muted"><?php echo number_format($item['subtotal']); ?> VNĐ</span>
            </li>
            <?php endforeach; ?>
            
            <li class="list-group-item d-flex justify-content-between bg-light">
                <span class="fw-bold">Tổng cộng (VNĐ)</span>
                <strong><?php echo number_format($total_price); ?></strong>
            </li>
        </ul>
    </div>

    <div class="col-md-7 col-lg-8">
        <h4 class="mb-3">Thông tin giao hàng</h4>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($user['full_name'] ?? 'Chưa cập nhật tên'); ?></h5>
                <p class="card-text">
                    <strong>Tài khoản:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?> <br>
                    <strong>Địa chỉ:</strong> 
                    <?php if (empty($user['address'])): ?>
                        <span class="text-danger">Bạn chưa cập nhật địa chỉ.</span>
                        <a href="profile.php">Cập nhật ngay</a>
                    <?php else: ?>
                        <?php echo nl2br(htmlspecialchars($user['address'])); ?>
                    <?php endif; ?>
                </p>
                <hr>
                <form action="checkout.php" method="POST">
                    <p>Vui lòng xác nhận thông tin trước khi đặt hàng. Chúng tôi sẽ giao hàng đến địa chỉ trên.</p>
                    <button type="submit" class="w-100 btn btn-primary btn-lg" 
                            <?php if (empty($user['address'])) echo 'disabled'; ?>>
                        Xác Nhận Đặt Hàng
                    </button>
                    <?php if (empty($user['address'])): ?>
                        <small class="text-danger d-block mt-2">Bạn phải cập nhật địa chỉ trong hồ sơ trước khi đặt hàng.</small>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?> <?php include 'includes/footer.php'; ?>