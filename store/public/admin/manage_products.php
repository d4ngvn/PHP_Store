<?php
require_once '../../config/database.php';
require_once '../../core/functions.php';

// **BẢO MẬT: Yêu cầu quyền admin**
require_admin();

$page_title = "Quản Lý Sản Phẩm";
include '../includes/header.php'; // Chú ý đường dẫn

$action = $_GET['action'] ?? 'list'; // Hành động mặc định là 'list'
$product_id = $_GET['id'] ?? null;
$product = null;

$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);

// Xử lý Cập nhật (Update) hoặc Thêm mới (Create)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock_quantity = $_POST['stock_quantity'];
        $image_url = $_POST['image_url']; // Đơn giản hóa, dùng link ảnh

        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Cập nhật
            $id = $_POST['id'];
            $sql = "UPDATE products SET name = ?, description = ?, price = ?, stock_quantity = ?, image_url = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $description, $price, $stock_quantity, $image_url, $id]);
            $_SESSION['message'] = "Cập nhật sản phẩm thành công!";
        } else {
            // Thêm mới
            $sql = "INSERT INTO products (name, description, price, stock_quantity, image_url) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $description, $price, $stock_quantity, $image_url]);
            $_SESSION['message'] = "Thêm sản phẩm mới thành công!";
        }
    } catch (PDOException $e) {
        $message = "Lỗi: " . $e->getMessage();
    }
    header("Location: manage_products.php"); // Quay lại trang danh sách
    exit;
}

// Xử lý Xóa (Delete)
if ($action === 'delete' && $product_id) {
    try {
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$product_id]);
        $_SESSION['message'] = "Xóa sản phẩm thành công!";
    } catch (PDOException $e) {
         $_SESSION['message'] = "Lỗi khi xóa: Sản phẩm này có thể đang nằm trong một đơn hàng.";
    }
    header("Location: manage_products.php");
    exit;
}

// Lấy dữ liệu cho form Sửa (Edit)
if ($action === 'edit' && $product_id) {
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
}

// Lấy danh sách sản phẩm (Read)
$sql = "SELECT * FROM products ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll();

?>

<h2 class="mb-4">Quản Lý Sản Phẩm</h2>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <h5><?php echo ($action === 'edit' && $product) ? 'Sửa Sản Phẩm (ID: ' . $product['id'] . ')' : 'Thêm Sản Phẩm Mới'; ?></h5>
    </div>
    <div class="card-body">
        <form action="manage_products.php" method="POST">
            <?php if ($action === 'edit' && $product): ?>
                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
            <?php endif; ?>
            
            <div class="mb-3">
                <label for="name" class="form-label">Tên sản phẩm:</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Mô tả:</label>
                <textarea id="description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
            </div>
             <div class="mb-3">
                <label for="image_url" class="form-label">Link hình ảnh:</label>
                <input type="text" id="image_url" name="image_url" class="form-control" value="<?php echo htmlspecialchars($product['image_url'] ?? ''); ?>" placeholder="https://example.com/image.png">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="price" class="form-label">Giá:</label>
                    <input type="number" id="price" name="price" class="form-control" step="0.01" value="<?php echo $product['price'] ?? ''; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="stock_quantity" class="form-label">Số lượng kho:</label>
                    <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" value="<?php echo $product['stock_quantity'] ?? ''; ?>" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary"><?php echo ($action === 'edit' && $product) ? 'Cập Nhật' : 'Thêm Mới'; ?></button>
            <?php if ($action === 'edit' && $product): ?>
                <a href="manage_products.php" class="btn btn-secondary">Hủy (Thêm mới)</a>
            <?php endif; ?>
        </form>
    </div>
</div>


<div class="card">
    <div class="card-header">
        <h5>Danh Sách Sản Phẩm</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Hình Ảnh</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?php echo $p['id']; ?></td>
                        <td>
                            <img src="<?php echo htmlspecialchars($p['image_url'] ?? '../assets/images/placeholder.png'); ?>" 
                                 alt="<?php echo htmlspecialchars($p['name']); ?>" 
                                 style="width: 50px; height: 50px; object-fit: cover;">
                        </td>
                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                        <td><?php echo number_format($p['price']); ?> VNĐ</td>
                        <td><?php echo $p['stock_quantity']; ?></td>
                        <td>
                            <a href="manage_products.php?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                            <a href="manage_products.php?action=delete&id=<?php echo $p['id']; ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">Xóa</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; // Chú ý đường dẫn ?>