<?php
require_once '../config/database.php';
$page_title = "Hồ Sơ Của Bạn";
include 'includes/header.php'; // Đã có nav.php
require_login();

$user_id = $_SESSION['user_id'];
$message = ""; 
$error_message = "";

// Xử lý CẬP NHẬT thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $address = trim($_POST['address']);
    try {
        $sql = "UPDATE users SET full_name = ?, address = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$full_name, $address, $user_id]);
        $message = "Cập nhật thông tin thành công!";
    } catch (PDOException $e) {
        $error_message = "Lỗi: Không thể cập nhật thông tin.";
    }
}

// Xử lý ĐỔI MẬT KHẨU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $new_password_confirm = $_POST['new_password_confirm'];

    $sql = "SELECT password_hash FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $user_pass = $stmt->fetch();

    if ($user_pass && password_verify($current_password, $user_pass['password_hash'])) {
        if (strlen($new_password) < 6) {
            $error_message = "Mật khẩu mới phải có ít nhất 6 ký tự.";
        } elseif ($new_password !== $new_password_confirm) {
            $error_message = "Mật khẩu mới không khớp.";
        } else {
            // Cập nhật mật khẩu mới
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$new_password_hash, $user_id]);
            $message = "Đổi mật khẩu thành công!";
        }
    } else {
        $error_message = "Mật khẩu hiện tại không đúng.";
    }
}


// Lấy thông tin hiện tại của người dùng (READ)
try {
    $sql = "SELECT username, email, full_name, address FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!$user) { throw new Exception("Không tìm thấy người dùng."); }
} catch (Exception $e) {
    echo "<p class='alert alert-danger'>Lỗi khi tải thông tin: " . $e->getMessage() . "</p>";
    include 'includes/footer.php';
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <h2 class="mb-4">Hồ Sơ Của Bạn</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Thông tin cá nhân</h5>
            </div>
            <div class="card-body">
                <form action="profile.php" method="POST">
                    <input type="hidden" name="update_profile" value="1">
                    <div class="mb-3">
                        <label for="username" class="form-label">Tên đăng nhập:</label>
                        <input type="text" id="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly disabled>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly disabled>
                    </div>
                    <hr class="my-3">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Họ và Tên:</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Địa chỉ:</label>
                        <textarea id="address" name="address" rows="3" class="form-control"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Cập Nhật Thông Tin</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Đổi Mật Khẩu</h5>
            </div>
            <div class="card-body">
                <form action="profile.php" method="POST">
                    <input type="hidden" name="change_password" value="1">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mật khẩu hiện tại:</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Mật khẩu mới:</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password_confirm" class="form-label">Xác nhận mật khẩu mới:</label>
                        <input type="password" id="new_password_confirm" name="new_password_confirm" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-warning">Đổi Mật Khẩu</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>