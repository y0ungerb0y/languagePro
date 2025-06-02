<?php
include 'includes/config.php';

if(isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Неверное имя пользователя или пароль']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h2>Вход</h2>
            <form id="loginForm" method="POST">
                <div class="form-group">
                    <label for="username">Имя пользователя</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Войти</button>
            </form>
            <div id="error-message" class="error-message"></div>
            <div class="auth-footer">
                <p>Еще нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
            </div>
        </div>
    </div>
    <script src="scripts.js"></script>
</body>
</html>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const errorMessage = document.getElementById('error-message');

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        if (username && password) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'login.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        window.location.href = 'dashboard.php';
                    } else {
                        errorMessage.textContent = response.error;
                    }
                }
            };

            xhr.send(`username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`);
        } else {
            errorMessage.textContent = 'Пожалуйста, заполните все поля.';
        }
    });
});
</script>