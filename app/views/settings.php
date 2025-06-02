<?php
$page_title = 'Настройки';
$page_css = 'settings';
$page_js = 'settings';
include 'app/views/components/header.php';

// Получаем информацию о пользователе
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Обработка AJAX-запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'updateProfile') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);

        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->execute([$username, $email, $user_id]);

        echo json_encode(['success' => true]);
    } elseif ($action === 'updatePassword') {
        $current_password = trim($_POST['current_password']);
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);

        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);

                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Новые пароли не совпадают']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Текущий пароль неверен']);
        }
    } elseif ($action === 'uploadAvatar') {
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['avatar']['name'];
            $filetmp = $_FILES['avatar']['tmp_name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            if (in_array($ext, $allowed)) {
                $new_filename = uniqid() . '.' . $ext;
                $upload_dir = 'uploads/avatars/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                move_uploaded_file($filetmp, $upload_dir . $new_filename);

                $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $stmt->execute([$new_filename, $user_id]);

                echo json_encode(['success' => true, 'avatar' => $new_filename]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Недопустимый формат файла']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Ошибка загрузки файла']);
        }
    }

    exit;
}
?>

<div class="page-header">
    <h1>Настройки</h1>
</div>

<div class="settings-container">
    <div class="settings-card">
        <h2>Профиль</h2>
        <form id="profileForm">
            <div class="form-group">
                <label for="username">Имя пользователя</label>
                <input type="text" id="username" name="username" value="<?php echo $user['username']; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
    </div>

    <div class="settings-card">
        <h2>Изменить пароль</h2>
        <form id="passwordForm">
            <div class="form-group">
                <label for="current_password">Текущий пароль</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">Новый пароль</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Подтвердите новый пароль</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
    </div>

    <div class="settings-card">
        <h2>Загрузить аватар</h2>
        <form id="avatarForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="avatar">Аватар</label>
                <input type="file" id="avatar" name="avatar" required>
            </div>
            <button type="submit" class="btn btn-primary">Загрузить</button>
        </form>
    </div>
</div>

<?php include 'app/views/components/footer.php'; ?>