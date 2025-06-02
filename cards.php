<?php
include 'includes/config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем карточки пользователя
$stmt = $pdo->prepare("SELECT * FROM word_cards WHERE user_id = ? ORDER BY next_review ASC");
$stmt->execute([$user_id]);
$cards = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="dashboard-container">
    <div class="sidebar">
        <!-- Та же боковая панель, что и в dashboard.php -->
        <?php include 'includes/sidebar.php'; ?>
    </div>
    
    <div class="main-content">
        <div class="page-header">
            <h1>Карточки слов</h1>
            <button id="addCardBtn" class="btn btn-primary">Добавить карточку</button>
        </div>
        
        <div class="cards-filter">
            <div class="filter-group">
                <label for="languageFilter">Язык:</label>
                <select id="languageFilter">
                    <option value="all">Все</option>
                    <option value="english">Английский</option>
                    <option value="german">Немецкий</option>
                    <option value="french">Французский</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="difficultyFilter">Сложность:</label>
                <select id="difficultyFilter">
                    <option value="all">Все</option>
                    <option value="easy">Легкие</option>
                    <option value="medium">Средние</option>
                    <option value="hard">Сложные</option>
                </select>
            </div>
        </div>
        
        <div class="cards-container">
            <?php if(empty($cards)): ?>
                <div class="empty-state">
                    <i class="fas fa-layer-group"></i>
                    <h3>У вас пока нет карточек</h3>
                    <p>Начните добавлять карточки для изучения слов</p>
                    <button id="addFirstCardBtn" class="btn btn-primary">Добавить первую карточку</button>
                </div>
            <?php else: ?>
                <?php foreach($cards as $card): ?>
                    <div class="word-card" data-id="<?php echo $card['id']; ?>">
                        <div class="card-front">
                            <h3><?php echo htmlspecialchars($card['front_text']); ?></h3>
                            <button class="btn-flip"><i class="fas fa-sync-alt"></i> Перевернуть</button>
                        </div>
                        <div class="card-back">
                            <h3><?php echo htmlspecialchars($card['back_text']); ?></h3>
                            <div class="card-meta">
                                <span class="badge <?php echo $card['difficulty']; ?>"><?php echo ucfirst($card['difficulty']); ?></span>
                                <span class="language"><?php echo ucfirst($card['language']); ?></span>
                            </div>
                            <button class="btn-flip"><i class="fas fa-sync-alt"></i> Перевернуть</button>
                        </div>
                        <div class="card-actions">
                            <button class="btn-edit" data-id="<?php echo $card['id']; ?>"><i class="fas fa-edit"></i></button>
                            <button class="btn-delete" data-id="<?php echo $card['id']; ?>"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Модальное окно добавления карточки -->
<div id="addCardModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Добавить новую карточку</h2>
        <form id="addCardForm">
            <div class="form-group">
                <label for="frontText">Передняя сторона</label>
                <input type="text" id="frontText" name="frontText" required>
            </div>
            <div class="form-group">
                <label for="backText">Обратная сторона</label>
                <input type="text" id="backText" name="backText" required>
            </div>
            <div class="form-group">
                <label for="cardLanguage">Язык</label>
                <select id="cardLanguage" name="language" required>
                    <option value="english">Английский</option>
                    <option value="german">Немецкий</option>
                    <option value="french">Французский</option>
                </select>
            </div>
            <div class="form-group">
                <label for="cardDifficulty">Сложность</label>
                <select id="cardDifficulty" name="difficulty" required>
                    <option value="easy">Легкая</option>
                    <option value="medium">Средняя</option>
                    <option value="hard">Сложная</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>