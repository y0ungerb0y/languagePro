<?php
header('Content-Type: application/json');
session_start();

if(!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    exit(json_encode(['error' => 'Не авторизован']));
}

$user_id = $_SESSION['user_id'];
$card_id = filter_input(INPUT_POST, 'cardId', FILTER_VALIDATE_INT);

if(!$card_id) {
    http_response_code(400); // Bad Request
    exit(json_encode(['error' => 'Неверный ID карточки']));
}

try {
    $stmt = $pdo->prepare("DELETE FROM word_cards WHERE id = ? AND user_id = ?");
    $stmt->execute([$card_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(404); // Not Found (or not owned)
        echo json_encode(['success' => false, 'error' => 'Карточка не найдена или не принадлежит вам']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных: ' . $e->getMessage()]);
}