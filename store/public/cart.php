<?php
require_once '../config/database.php';
$page_title = "Giỏ Hàng";
include 'includes/header.php'; // Đã có session_start()

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // Mảng [product_id => quantity]
}

$action = $_POST['action'] ?? $_GET['action'] ?? 'show';
$product_id = $_POST['product_id'] ?? $_GET['product_id'] ?? null;
$quantity = $_POST['quantity'] ?? 1;

// Xử lý logic giỏ hàng
try {
    switch ($action) {
        case 'add':
            if ($product_id && $quantity > 0) {
                // Kiểm tra số lượng tồn kho
                $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product_stock = $stmt->fetchColumn();
                
                $current_quantity = $_SESSION['cart'][$product_id] ?? 0;
                $new_quantity = $current_quantity + $quantity;

                if ($new_quantity > $product_stock) {
                    $_SESSION['error_message'] = "Số lượng trong kho không đủ.";
                } else {
                    $_SESSION['cart'][$product_id] = $new_quantity;
                }
            }
            header("Location: " . ($_POST['return_url'] ?? 'cart.php?cart_action=added'));
            exit;
        
        case 'update':
            if ($product_id && $quantity > 0) {
                 // Kiểm tra số lượng tồn kho
                $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product_stock = $stmt->fetchColumn();

                if ($quantity > $product_stock) {
                    $_SESSION['error_message'] = "Số lượng trong kho không đủ cho sản phẩm ID $product_id.";
                } else {
                    $_SESSION['cart'][$product_id] = $quantity;
                }
            } else if ($product_id && $quantity <= 0) {
                unset($_SESSION['cart'][$product_id]);
            }
            header("Location: cart.php");
            exit;

        case 'remove':
            if ($product_id) unset($_SESSION['cart'][$product_id]);
            header("Location: cart.php");
            exit;
            
        case 'clear':
            $_SESSION['cart'] = [];
            header("Location: cart.php");
            exit;
    }
} catch (PDOException $e) {
    echo "<p class='alert alert-danger'>Đã xảy ra lỗi: " . $e->getMessage() . "</p>";
}

// ---- Hiển thị giỏ hàng (action = 'show') ----
$cart_items = [];
$total_price = 0;

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = rtrim(str_repeat('?,', count($product_ids)), ',');
    
    $sql = "SELECT * FROM products WHERE id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($product_ids);
    
    while ($product = $stmt->fetch()) {
        $id = $product['id'];
        $quantity_in_cart = $_SESSION['cart'][$id];
        $subtotal = $product['price'] * $quantity_in_cart;
        
        $cart_items[] = [
            'id' => $id,
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity_in_cart,
            'subtotal' => $subtotal,
            'image_url' => $product['image_url']
        ];
        $total_price += $subtotal;
    }
}
?>

<h2 class="mb-4">Giỏ Hàng Của Bạn</h2>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (empty($cart_items)): ?>
    <div class="alert alert-info">Giỏ hàng của bạn đang trống. <a href="index.php" class="alert-link">Tiếp tục mua sắm</a></div>
<?php else: ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Sản phẩm trong giỏ</span>
                    <a href="cart.php?action=clear" class="btn btn-outline-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa sạch giỏ hàng?');">Xóa sạch giỏ hàng</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50%;">Sản Phẩm</th>
                                <th style="width: 15%;">Giá</th>
                                <th style="width: 20%;">Số Lượng</th>
                                <th style="width: 15%;">Tổng Phụ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'assets/images/placeholder.png'); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             style="width: 60px; height: 60px; object-fit: cover; margin-right: 15px;"
                                             class="rounded">
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                            <a href="cart.php?action=remove&product_id=<?php echo $item['id']; ?>" class="text-danger small">Xóa</a>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo number_format($item['price']); ?></td>
                                <td>
                                    <form action="cart.php" method="POST" class="d-flex">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="0" class="form-control form-control-sm" style="width: 70px;">
                                        <button type="submit" class="btn btn-outline-secondary btn-sm ms-2">Cập nhật</button>
                                    </form>
                                </td>
                                <td class="fw-bold"><?php echo number_format($item['subtotal']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Tổng Cộng Giỏ Hàng</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Tạm tính</span>
                            <span><?php echo number_format($total_price); ?> VNĐ</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Phí vận chuyển</span>
                            <span>Miễn phí</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 fw-bold fs-5">
                            <span>Tổng tiền</span>
                            <span><?php echo number_format($total_price); ?> VNĐ</span>
                        </li>
                    </ul>
                    <div class="d-grid gap-2 mt-4">
                        <a href="checkout.php" class="btn btn-primary btn-lg">Tiến hành Thanh Toán</a>
                        <a href="index.php" class="btn btn-outline-secondary">Tiếp tục mua sắm</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>