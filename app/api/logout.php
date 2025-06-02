<?php
// Начинаем сессию
session_start();

// Уничтожаем все данные сессии
$_SESSION = array();

// Если нужно, уничтожаем сессионную куку
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Уничтожаем сессию
session_destroy();

// Перенаправляем пользователя на страницу входа или главную страницу
header("Location: /login");
exit;
?>