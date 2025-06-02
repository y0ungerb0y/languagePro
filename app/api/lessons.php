<?php
header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

try {
    switch($action) {
        case 'get':
            // Получение урока
            $lesson_id = $_GET['id'] ?? 0;
            
            $stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
            $stmt->execute([$lesson_id]);
            $lesson = $stmt->fetch();
            
            if(!$lesson) {
                throw new Exception('Урок не найден');
            }
            
            // Генерация слайдов урока (в реальном проекте это может быть из базы данных)
            $slides = [
                '<div class="lesson-slide" data-lesson-id="'.$lesson['id'].'">
                    <h2>'.$lesson['title'].'</h2>
                    <p>'.$lesson['description'].'</p>
                    <div class="lesson-image">
                        <img src="assets/images/lessons/lesson-'.$lesson['id'].'-1.jpg" alt="'.$lesson['title'].'">
                    </div>
                </div>',
                
                '<div class="lesson-slide">
                    <h3>Основные понятия</h3>
                    <p>Давайте изучим основные концепции этого урока...</p>
                    <div class="lesson-example">
                        <p><strong>Пример:</strong> Это пример использования изучаемого материала</p>
                    </div>
                </div>',
                
                '<div class="lesson-slide">
                    <h3>Практическое задание</h3>
                    <p>Попробуйте выполнить следующее задание:</p>
                    <div class="lesson-task">
                        <p>Переведите следующее предложение:</p>
                        <p>"Привет, как дела?"</p>
                        <input type="text" class="task-input" placeholder="Ваш перевод...">
                        <button class="btn btn-check">Проверить</button>
                    </div>
                </div>',
                
                '<div class="lesson-slide lesson-complete">
                    <i class="fas fa-check-circle"></i>
                    <h3>Урок завершен!</h3>
                    <p>Вы успешно завершили урок "'.$lesson['title'].'"</p>
                    <p>Вы получили 50 очков опыта!</p>
                </div>'
            ];
            
            echo json_encode([
                'success' => true,
                'content' => $slides[0], // Первый слайд
                'slides' => $slides
            ]);
            break;
            
        case 'complete':
            // Завершение урока
            $lesson_id = $_GET['id'] ?? 0;
            
            // Проверяем, существует ли урок
            $stmt = $pdo->prepare("SELECT id FROM lessons WHERE id = ?");
            $stmt->execute([$lesson_id]);
            
            if(!$stmt->fetch()) {
                throw new Exception('Урок не найден');
            }
            
            // Проверяем, не завершен ли уже урок
            $stmt = $pdo->prepare("SELECT id FROM user_lessons WHERE user_id = ? AND lesson_id = ?");
            $stmt->execute([$user_id, $lesson_id]);
            
            if($stmt->fetch()) {
                throw new Exception('Вы уже завершили этот урок');
            }
            
            // Добавляем запись о завершении урока
            $stmt = $pdo->prepare("INSERT INTO user_lessons (user_id, lesson_id, completed, completed_at) VALUES (?, ?, 1, NOW())");
            $stmt->execute([$user_id, $lesson_id]);
            
            // Начисляем очки опыта
            $points = 50;
            $stmt = $pdo->prepare("UPDATE user_progress SET points = points + ? WHERE user_id = ?");
            $stmt->execute([$points, $user_id]);
            
            // Проверяем, не достигнут ли новый уровень
            $stmt = $pdo->prepare("SELECT points FROM user_progress WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $progress = $stmt->fetch();
            
            $new_level = floor($progress['points'] / 1000) + 1;
            
            if($new_level > 1) {
                $stmt = $pdo->prepare("UPDATE user_progress SET level = ? WHERE user_id = ?");
                $stmt->execute([$new_level, $user_id]);
            }
            
            echo json_encode(['success' => true, 'points' => $points]);
            break;
            
        default:
            throw new Exception('Неизвестное действие');
    }
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>