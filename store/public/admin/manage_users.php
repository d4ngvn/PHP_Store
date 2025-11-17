<?php
require_once '../../config/database.php';
require_once '../../core/functions.php';

// **BẢO MẬT: Yêu cầu quyền admin**
require_admin();

$page_title = "Quản Lý Người Dùng";
include '../includes/header.php'; // Chú ý đường dẫn

$action = $_GET['action'] ?? 'list';
$user_id = $_GET['id'] ?? null;
$user_data = null;

$message = $_SESSION['message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['message'], $_SESSION['error_message']);

// Xử lý Cập nhật (Update Role)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id_to_update = $_POST['id'];
        $new_role = $_POST['role'];
        $current_user_id = $_SESSION['user_id'];

        // Ngăn admin tự hạ vai trò của chính mình
        if ($id_to_update == $current_user_id && $new_role == 'customer') {
            $_SESSION['error_message'] = "Bạn không thể tự tước quyền admin của chính mình.";
            header("Location: manage_users.php");
            exit;
        }

        // Ngăn việc xóa/hạ quyền admin cuối cùng
        if ($new_role == 'customer') {
            $stmt = $pdo->query("SELECT COUNT(id) FROM users WHERE role = 'admin'");
            $admin_count = $stmt->fetchColumn();
            if ($admin_count <= 1) {
                $_SESSION['error_message'] = "Không thể hạ vai trò của admin cuối cùng.";
                header("Location: manage_users.php");
                exit;
            }
        }

        $sql = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$new_role, $id_to_update]);
        $_SESSION['message'] = "Cập nhật vai trò người dùng thành công!";
        
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Lỗi: " . $e->getMessage();
    }
    header("Location: manage_users.php");
    exit;
}

// Xử lý Xóa (Delete)
if ($action === 'delete' && $user_id) {
    try {
        // Ngăn admin tự xóa chính mình
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['error_message'] = "Bạn không thể tự xóa chính mình.";
            header("Location: manage_users.php");
            exit;
        }

        // Kiểm tra xem đây có phải admin cuối cùng không
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_role = $stmt->fetchColumn();

        if ($user_role == 'admin') {
            $stmt_count = $pdo->query("SELECT COUNT(id) FROM users WHERE role = 'admin'");
            $admin_count = $stmt_count->fetchColumn();
            if ($admin_count <= 1) {
                $_SESSION['error_message'] = "Không thể xóa admin cuối cùng.";
                header("Location: manage_users.php");
                exit;
            }
        }

        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $_SESSION['message'] = "Xóa người dùng thành công!";
    } catch (PDOException $e) {
         $_SESSION['error_message'] = "Lỗi khi xóa: Người dùng này có thể đã đặt hàng.";
    }
    header("Location: manage_users.php");
    exit;
}

// Lấy danh sách người dùng (Read)
$sql = "SELECT id, username, email, full_name, role, created_at FROM users ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll();

?>

<h2 class="mb-4">Quản Lý Người Dùng</h2>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5>Danh Sách Người Dùng</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tên đăng nhập</th>
                        <th>Email</th>
                        <th>Họ tên</th>
                        <th>Ngày tham gia</th>
                        <th>Vai trò</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <form action="manage_users.php" method="POST" class="d-inline">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <select name="role" class="form-select form-select-sm" style="width: 120px; display: inline-block;">
                                    <option value="customer" <?php echo ($user['role'] == 'customer') ? 'selected' : ''; ?>>
                                        Customer
                                    </option>
                                    <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>
                                        Admin
                                    </option>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm">Lưu</button>
                            </form>
                        </td>
                        <td>
                            <a href="manage_users.php?action=delete&id=<?php echo $user['id']; ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');">Xóa</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; // Chú ý đường dẫn ?>