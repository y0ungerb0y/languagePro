<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/config.php';

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

try {
    switch($action) {
        case 'add':
            // Добавление новой карточки
            $frontText = trim($_POST['frontText']);
            $backText = trim($_POST['backText']);
            $language = trim($_POST['language']);
            $difficulty = trim($_POST['difficulty']);
            
            if(empty($frontText) || empty($backText)) {
                throw new Exception('Все поля обязательны для заполнения');
            }
            
            $next_review = date('Y-m-d', strtotime('+1 day'));
            
            $stmt = $pdo->prepare("INSERT INTO word_cards (user_id, front_text, back_text, language, difficulty, next_review) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $frontText, $backText, $language, $difficulty, $next_review]);
            
            echo json_encode(['success' => true]);
            break;
            
        case 'delete':
            // Удаление карточки
            $card_id = $_GET['id'] ?? 0;
            
            $stmt = $pdo->prepare("DELETE FROM word_cards WHERE id = ? AND user_id = ?");
            $stmt->execute([$card_id, $user_id]);
            
            if($stmt->rowCount() > 0) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Карточка не найдена или у вас нет прав для ее удаления');
            }
            break;
            
        default:
            throw new Exception('Неизвестное действие');
    }
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>