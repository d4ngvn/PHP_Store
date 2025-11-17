<?php
// file: public/includes/nav.php
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
  <div class="container">
    <a class="navbar-brand" href="index.php">🛍️ Cửa Hàng</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="index.php">Trang Chủ</a>
        </li>
      </ul>
      
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'cart.php') ? 'active' : ''; ?>" href="cart.php">
            🛒 Giỏ Hàng
            <?php 
            if (!empty($_SESSION['cart'])) {
                $item_count = array_sum($_SESSION['cart']); // Tính tổng số lượng
                echo ' <span class="badge bg-danger rounded-pill">' . $item_count . '</span>';
            }
            ?>
          </a>
        </li>

        <?php if (is_logged_in()): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Chào, <?php echo htmlspecialchars($_SESSION['username']); ?>!
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="profile.php">Hồ Sơ Của Bạn</a></li>
                <?php if (is_admin()): ?>
                    <li><a class="dropdown-item" href="admin/index.php">Trang Quản Trị</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php">Đăng Xuất</a></li>
              </ul>
            </li>
        <?php else: ?>
            <li class="nav-item">
              <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'login.php') ? 'active' : ''; ?>" href="login.php">Đăng Nhập</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'register.php') ? 'active' : ''; ?>" href="register.php">Đăng Ký</a>
            </li>
        <?php endif; ?>
      </ul>
      
    </div>
  </div>
</nav>