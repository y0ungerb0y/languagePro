<?php
$page_title = 'Уроки';
$page_css = 'lessons';
$page_js = 'lessons';
include 'app/views/components/header.php';

// Получаем уроки
$stmt = $pdo->prepare("SELECT * FROM lessons ORDER BY level ASC");
$stmt->execute();
$lessons = $stmt->fetchAll();

// Получаем завершенные уроки пользователя
$stmt = $pdo->prepare("SELECT lesson_id FROM user_lessons WHERE user_id = ?");
$stmt->execute([$user_id]);
$completed_lessons = array_column($stmt->fetchAll(), 'lesson_id');

// Получаем уровень пользователя
$stmt = $pdo->prepare("SELECT level FROM user_progress WHERE user_id = ?");
$stmt->execute([$user_id]);
$user_level = $stmt->fetchColumn();
?>

<div class="page-header">
    <h1>Уроки</h1>
    <div class="level-indicator">
        <span>Уровень: <?php echo $user_level; ?></span>
    </div>
</div>

<div class="lessons-container">
    <?php if(empty($lessons)): ?>
        <div class="empty-state">
            <i class="fas fa-book-open"></i>
            <h3>Уроки пока недоступны</h3>
            <p>Повышайте свой уровень, чтобы открыть новые уроки</p>
        </div>
    <?php else: ?>
        <?php foreach($lessons as $lesson): ?>
            <div class="lesson-card <?php echo in_array($lesson['id'], $completed_lessons) ? 'completed' : ''; ?>">
                <div class="lesson-header">
                    <h3><?php echo htmlspecialchars($lesson['title']); ?></h3>
                    <div class="lesson-meta">
                        <span class="level">Уровень <?php echo $lesson['level']; ?></span>
                        <span class="duration"><i class="fas fa-clock"></i> <?php echo $lesson['duration']; ?> мин</span>
                    </div>
                </div>
                <p class="lesson-description"><?php echo htmlspecialchars($lesson['description']); ?></p>
                <div class="lesson-actions">
                    <?php if(in_array($lesson['id'], $completed_lessons)): ?>
                        <span class="completed-badge"><i class="fas fa-check"></i> Завершено</span>
                        <button class="btn btn-outline review-btn" data-id="<?php echo $lesson['id']; ?>">Повторить</button>
                    <?php else: ?>
                        <button class="btn btn-primary start-btn" data-id="<?php echo $lesson['id']; ?>">Начать</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Модальное окно урока -->
<div id="lessonModal" class="modal">
    <div class="modal-content lesson-modal-content">
        <span class="close">&times;</span>
        <div id="lessonContent"></div>
        <div class="lesson-controls">
            <button id="prevSlide" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Назад</button>
            <span id="slideCounter">1/10</span>
            <button id="nextSlide" class="btn btn-primary">Далее <i class="fas fa-arrow-right"></i></button>
            <button id="completeLesson" class="btn btn-success" style="display: none;"><i class="fas fa-check"></i> Завершить урок</button>
        </div>
    </div>
</div>

<?php include 'app/views/components/footer.php'; ?>