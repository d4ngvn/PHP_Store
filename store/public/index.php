<?php
require_once '../config/database.php';
$page_title = "Trang Chủ - Sản Phẩm"; 

// 1. Bao gồm Header (Đã có Bootstrap CSS và Nav)
include 'includes/header.php';
// nav.php đã được gọi trong header.php

// 2. Lấy dữ liệu sản phẩm... (Giữ nguyên code PHP)
try {
    $sql = "SELECT * FROM products WHERE stock_quantity > 0 ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "<p class='alert alert-danger'>Lỗi khi truy vấn CSDL: " . $e->getMessage() . "</p>";
    $products = []; 
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Danh Sách Sản Phẩm</h2>
</div>


<?php if (isset($_GET['cart_action']) && $_GET['cart_action'] == 'added'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      Sản phẩm đã được thêm vào giỏ hàng!
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    <?php if (empty($products)): ?>
        <div class="col">
            <p class="alert alert-info">Hiện chưa có sản phẩm nào để hiển thị.</p>
        </div>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'assets/images/placeholder.png'); ?>" 
                         class="card-img-top" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         style="height: 200px; object-fit: cover;">
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        
                        <p class="card-text text-muted flex-grow-1">
                            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                        </p>
                        
                        <h6 class="card-subtitle mb-2 text-danger fs-5">
                            <?php echo number_format($product['price']); ?> VNĐ
                        </h6>
                        
                        <p class="card-text">
                            <small class="text-muted">Còn lại: <?php echo $product['stock_quantity']; ?></small>
                        </p>

                        <form action="cart.php" method="POST" class="mt-auto">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            
                            <div class="input-group">
                                <input type="number" 
                                       name="quantity" 
                                       class="form-control" 
                                       value="1" 
                                       min="1" 
                                       max="<?php echo $product['stock_quantity']; ?>">
                                <button type="submit" class="btn btn-primary">Thêm vào giỏ</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php 
// 3. Bao gồm Footer (Đã có Bootstrap JS)
include 'includes/footer.php'; 
?>