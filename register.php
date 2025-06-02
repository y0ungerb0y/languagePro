<?php
include 'includes/config.php';

if(isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Валидация
    if(empty($username)) $errors[] = "Имя пользователя обязательно";
    if(empty($email)) $errors[] = "Email обязателен";
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Некорректный email";
    if(empty($password)) $errors[] = "Пароль обязателен";
    if($password !== $confirm_password) $errors[] = "Пароли не совпадают";

    // Проверка уникальности
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if($stmt->fetch()) $errors[] = "Имя пользователя или email уже заняты";

    if(empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashed_password]);

        $user_id = $pdo->lastInsertId();
        $stmt = $pdo->prepare("INSERT INTO user_progress (user_id, language) VALUES (?, 'english')");
        $stmt->execute([$user_id]);

        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'errors' => $errors]);
    }
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/register.css">
    <title>Регистрация</title>
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Создать аккаунт</h2>
            <p>Начните свое языковое путешествие сегодня</p>
        </div>

        <div id="error-messages" class="alert alert-danger" style="display: none;"></div>

        <form id="registerForm" method="POST">
            <div class="form-group">
                <label for="username">Имя пользователя</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Подтвердите пароль</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Зарегистрироваться</button>
        </form>

        <div class="auth-footer">
            <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
        </div>
    </div>
</div>

<div id="notification" class="notification"></div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const errorMessages = document.getElementById('error-messages');

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const confirm_password = document.getElementById('confirm_password').value;

        if (username && email && password && confirm_password) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'register.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        window.location.href = 'dashboard.php';
                    } else {
                        errorMessages.style.display = 'block';
                        errorMessages.innerHTML = response.errors.map(error => `<p>${error}</p>`).join('');
                    }
                }
            };

            xhr.send(`username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&confirm_password=${encodeURIComponent(confirm_password)}`);
        } else {
            errorMessages.style.display = 'block';
            errorMessages.innerHTML = '<p>Пожалуйста, заполните все поля.</p>';
        }
    });
});
</script>
