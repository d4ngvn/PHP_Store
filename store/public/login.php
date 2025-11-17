<?php
require_once '../config/database.php';

$page_title = "Đăng Nhập";
include 'includes/header.php'; // Đã có functions.php và session_start()

$username = $password = "";
$error = "";

if (is_logged_in()) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Chuyển hướng đến trang đã lưu trước đó (nếu có)
        $redirect_url = $_SESSION['redirect_url'] ?? 'index.php';
        if ($user['role'] === 'admin') $redirect_url = 'admin/index.php';
        unset($_SESSION['redirect_url']);
        
        header("Location: $redirect_url");
        exit;
    } else {
        $error = "Tên đăng nhập hoặc mật khẩu không đúng.";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
        <div class="card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header">
                <h3 class="text-center font-weight-light my-4">Đăng Nhập</h3>
            </div>
            <div class="card-body">
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (isset($_GET['register']) && $_GET['register'] === 'success'): ?>
                    <div class="alert alert-success">Đăng ký thành công! Vui lòng đăng nhập.</div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="form-floating mb-3">
                        <input class="form-control" id="username" type="text" name="username" placeholder="Tên đăng nhập" required>
                        <label for="username">Tên đăng nhập</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input class="form-control" id="password" type="password" name="password" placeholder="Mật khẩu" required>
                        <label for="password">Mật khẩu</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Đăng Nhập</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center py-3">
                <div class="small">
                    <a href="register.php">Chưa có tài khoản? Đăng ký</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>