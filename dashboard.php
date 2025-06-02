<?php
include 'includes/config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Получаем прогресс
$stmt = $pdo->prepare("SELECT * FROM user_progress WHERE user_id = ?");
$stmt->execute([$user_id]);
$progress = $stmt->fetch();

// Получаем количество карточек
$stmt = $pdo->prepare("SELECT COUNT(*) as total_cards FROM word_cards WHERE user_id = ?");
$stmt->execute([$user_id]);
$cards = $stmt->fetch();


?>

<?php include 'includes/header.php'; ?>

<div class="dashboard-container">
    <div class="sidebar">
        <div class="profile-card">
            <div class="avatar">
                <img src="assets/images/avatars/<?php echo $user['avatar']; ?>" alt="Avatar">
            </div>
            <h3><?php echo $user['username']; ?></h3>
            <p><?php echo $user['email']; ?></p>
        </div>
        
        <nav class="dashboard-nav">
            <ul>
                <li class="active"><a href="dashboard.php"><i class="fas fa-home"></i> Главная</a></li>
                <li><a href="cards.php"><i class="fas fa-layer-group"></i> Карточки слов</a></li>
                <li><a href="lessons.php"><i class="fas fa-book-open"></i> Уроки</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Настройки</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Выход</a></li>
            </ul>
        </nav>
    </div>
    
    <div class="main-content">
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
    </div>
</div>

<?php include 'includes/footer.php'; ?>