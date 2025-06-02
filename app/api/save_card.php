<?php
header('Content-Type: application/json');
session_start();

if(!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    exit(json_encode(['error' => 'Не авторизован']));
}

$user_id = $_SESSION['user_id'];
$card_id = filter_input(INPUT_POST, 'cardId', FILTER_VALIDATE_INT);
$front_text = trim($_POST['frontText'] ?? '');
$back_text = trim($_POST['backText'] ?? '');
$language = $_POST['language'] ?? '';
$difficulty = $_POST['difficulty'] ?? '';

if (empty($front_text) || empty($back_text) || empty($language) || empty($difficulty)) {
    http_response_code(400); // Bad Request
    exit(json_encode(['error' => 'Не все поля заполнены']));
}

try {
    if ($card_id) {
        // Update
        $stmt = $pdo->prepare("UPDATE word_cards SET front_text = ?, back_text = ?, language = ?, difficulty = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$front_text, $back_text, $language, $difficulty, $card_id, $user_id]);
        $rows_affected = $stmt->rowCount();
        if ($rows_affected === 0) {
            http_response_code(404); // Not Found (or not owned)
            exit(json_encode(['error' => 'Карточка не найдена или не принадлежит вам']));
        }
        $success = true;
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO word_cards (user_id, front_text, back_text, language, difficulty, next_review) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $front_text, $back_text, $language, $difficulty]);
        $card_id = $pdo->lastInsertId();
        $success = true;
    }

    if ($success) {
      echo json_encode(['success' => true, 'card_id' => $card_id]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Ошибка при сохранении']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных: ' . $e->getMessage()]);
}