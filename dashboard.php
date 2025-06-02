<?php
$page_title = 'Личный кабинет';
$page_css = 'dashboard';
$page_js = 'dashboard';
include 'includes/header.php';

// Получаем прогресс пользователя
$stmt = $pdo->prepare("SELECT * FROM user_progress WHERE user_id = ?");
$stmt->execute([$user_id]);
$progress = $stmt->fetch();

// Получаем количество карточек пользователя
$stmt = $pdo->prepare("SELECT COUNT(*) AS total_cards FROM word_cards WHERE user_id = ?");
$stmt->execute([$user_id]);
$cards = $stmt->fetch();

// Получаем количество завершенных уроков пользователя
$stmt = $pdo->prepare("SELECT COUNT(*) AS completed_lessons FROM user_lessons WHERE user_id = ?");
$stmt->execute([$user_id]);
$lessons = $stmt->fetch();
?>

<div class="welcome-banner">
    <h1>Добро пожаловать, <?php echo $user['username']; ?>!</h1>
    <p>Продолжайте свое языковое путешествие</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background-color: #4361ee;">
            <i class="fas fa-flag"></i>
        </div>
        <div class="stat-info">
            <h3>Уровень</h3>
            <p><?php echo $progress['level']; ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background-color: #f72585;">
            <i class="fas fa-star"></i>
        </div>
        <div class="stat-info">
            <h3>Очки</h3>
            <p><?php echo $progress['points']; ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background-color: #4cc9f0;">
            <i class="fas fa-layer-group"></i>
        </div>
        <div class="stat-info">
            <h3>Карточки</h3>
            <p><?php echo $cards['total_cards']; ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background-color: #4895ef;">
            <i class="fas fa-book-open"></i>
        </div>
        <div class="stat-info">
            <h3>Уроки</h3>
            <p><?php echo $lessons['completed_lessons']; ?></p>
        </div>
    </div>
</div>

<div class="progress-section">
    <h2>Ваш прогресс</h2>
    <div class="progress-container">
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?php echo min(100, ($progress['points'] / 1000) * 100); ?>%;"></div>
        </div>
        <p><?php echo min(100, ($progress['points'] / 1000) * 100); ?>% до следующего уровня</p>
    </div>
</div>

<div class="recent-activity">
    <h2>Недавняя активность</h2>
    <div class="activity-list">
        <div class="activity-item">
            <i class="fas fa-layer-group activity-icon"></i>
            <div class="activity-content">
                <p>Вы добавили 5 новых карточек</p>
                <span class="activity-time">2 часа назад</span>
            </div>
        </div>
        <div class="activity-item">
            <i class="fas fa-book-open activity-icon"></i>
            <div class="activity-content">
                <p>Вы завершили урок "Основные глаголы"</p>
                <span class="activity-time">Вчера</span>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>