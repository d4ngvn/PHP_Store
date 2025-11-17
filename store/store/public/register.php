<?php
require_once '../config/database.php';

$page_title = "Đăng Ký";
include 'includes/header.php';

$username = $email = $password = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $password_confirm = trim($_POST['password_confirm']);

    // Kiểm tra
    if (empty($username)) $errors[] = "Tên đăng nhập là bắt buộc.";
    if (empty($email)) $errors[] = "Email là bắt buộc.";
    if (empty($password)) $errors[] = "Mật khẩu là bắt buộc.";
    if (strlen($password) < 6) $errors[] = "Mật khẩu phải có ít nhất 6 ký tự.";
    if ($password !== $password_confirm) $errors[] = "Mật khẩu xác nhận không khớp.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Địa chỉ email không hợp lệ.";

    // Kiểm tra username hoặc email đã tồn tại
    if (empty($errors)) {
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = "Tên đăng nhập hoặc email đã tồn tại.";
        }
    }

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'customer')";
        $stmt = $pdo->prepare($sql);
        
        try {
            $stmt->execute([$username, $email, $password_hash]);
            header("Location: login.php?register=success");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Đã xảy ra lỗi: " . $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
        <div class="card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header">
                <h3 class="text-center font-weight-light my-4">Đăng Ký Tài Khoản</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p class="mb-0"><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <div class="form-floating mb-3">
                        <input class="form-control" id="username" type="text" name="username" placeholder="tendangnhap" value="<?php echo htmlspecialchars($username); ?>" required>
                        <label for="username">Tên đăng nhập</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input class="form-control" id="email" type="email" name="email" placeholder="name@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
                        <label for="email">Địa chỉ Email</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input class="form-control" id="password" type="password" name="password" placeholder="Mật khẩu" required>
                        <label for="password">Mật khẩu</label>
                    </div>
                     <div class="form-floating mb-3">
                        <input class="form-control" id="password_confirm" type="password" name="password_confirm" placeholder="Xác nhận mật khẩu" required>
                        <label for="password_confirm">Xác nhận mật khẩu</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Đăng Ký</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center py-3">
                <div class="small">
                    <a href="login.php">Đã có tài khoản? Đăng nhập ngay</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>