<?php
$page_title = 'Карточки слов';
$page_css = 'cards';
$page_js = 'cards';


// Подключение к базе данных

// Обработка AJAX запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'add_card':
                $frontText = htmlspecialchars(trim($_POST['frontText']));
                $backText = htmlspecialchars(trim($_POST['backText']));
                $language = htmlspecialchars(trim($_POST['language']));
                $difficulty = htmlspecialchars(trim($_POST['difficulty']));
                
                $stmt = $pdo->prepare("INSERT INTO word_cards (front_text, back_text, language, difficulty, user_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$frontText, $backText, $language, $difficulty, $_SESSION['user_id']]);
                $cardId = $pdo->lastInsertId();
                
                echo json_encode([
                    'success' => true,
                    'card' => [
                        'id' => $cardId,
                        'front_text' => $frontText,
                        'back_text' => $backText,
                        'language' => $language,
                        'difficulty' => $difficulty
                    ]
                ]);
                exit;
                
            case 'edit_card':
                $cardId = (int)$_POST['cardId'];
                $frontText = htmlspecialchars(trim($_POST['frontText']));
                $backText = htmlspecialchars(trim($_POST['backText']));
                $language = htmlspecialchars(trim($_POST['language']));
                $difficulty = htmlspecialchars(trim($_POST['difficulty']));
                
                $stmt = $pdo->prepare("UPDATE word_cards SET front_text = ?, back_text = ?, language = ?, difficulty = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$frontText, $backText, $language, $difficulty, $cardId, $_SESSION['user_id']]);
                
                echo json_encode(['success' => true]);
                exit;
                
            case 'delete_card':
                $cardId = (int)$_POST['cardId'];
                $stmt = $pdo->prepare("DELETE FROM word_cards WHERE id = ? AND user_id = ?");
                $stmt->execute([$cardId, $_SESSION['user_id']]);
                
                echo json_encode(['success' => true]);
                exit;
                
            case 'filter_cards':
                $language = $_POST['language'] === 'all' ? null : htmlspecialchars(trim($_POST['language']));
                $difficulty = $_POST['difficulty'] === 'all' ? null : htmlspecialchars(trim($_POST['difficulty']));
                
                $query = "SELECT * FROM cards WHERE user_id = ?";
                $params = [$_SESSION['user_id']];
                
                if ($language) {
                    $query .= " AND language = ?";
                    $params[] = $language;
                }
                
                if ($difficulty) {
                    $query .= " AND difficulty = ?";
                    $params[] = $difficulty;
                }
                
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                ob_start();
                if (empty($cards)) {
                    echo '<div class="empty-state">
                        <i class="fas fa-layer-group"></i>
                        <h3>Карточки не найдены</h3>
                        <p>Попробуйте изменить параметры фильтра</p>
                    </div>';
                } else {
                    foreach ($cards as $card) {
                        echo '<div class="word-card" data-id="'.$card['id'].'">
                            <div class="card-front active">
                                <h3>'.htmlspecialchars($card['front_text']).'</h3>
                                <button class="btn-flip"><i class="fas fa-sync-alt"></i> Перевернуть</button>
                            </div>
                            <div class="card-back">
                                <h3>'.htmlspecialchars($card['back_text']).'</h3>
                                <div class="card-meta">
                                    <span class="badge '.$card['difficulty'].'">'.ucfirst($card['difficulty']).'</span>
                                    <span class="language">'.ucfirst($card['language']).'</span>
                                </div>
                                <button class="btn-flip"><i class="fas fa-sync-alt"></i> Перевернуть</button>
                            </div>
                            <div class="card-actions">
                                <button class="btn-edit" data-id="'.$card['id'].'"><i class="fas fa-edit"></i></button>
                                <button class="btn-delete" data-id="'.$card['id'].'"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>';
                    }
                }
                $html = ob_get_clean();
                
                echo json_encode(['success' => true, 'html' => $html]);
                exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error']);
        exit;
    }
}

// Получение всех карточек пользователя
$stmt = $pdo->prepare("SELECT * FROM word_cards WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
include 'app/views/components/header.php';
?>
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
    <button id="applyFilterBtn" class="btn btn-secondary">Применить фильтр</button>
</div>

<div id="cardsContainer" class="cards-container">
    <?php if (empty($cards)): ?>
        <div class="empty-state">
            <i class="fas fa-layer-group"></i>
            <h3>У вас пока нет карточек</h3>
            <p>Начните добавлять карточки для изучения слов</p>
        </div>
    <?php else: ?>
        <?php foreach ($cards as $card): ?>
            <div class="word-card" data-id="<?php echo $card['id']; ?>">
                <div class="card-front active">
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

<!-- Модальное окно -->
<div id="cardModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalTitle">Добавить новую карточку</h2>
        <form id="cardForm">
            <input type="hidden" id="cardId" name="cardId">
            <div class="form-group">
                <label for="frontText">Передняя сторона</label>
                <input type="text" id="frontText" name="frontText" required placeholder="Слово или фраза">
            </div>
            <div class="form-group">
                <label for="backText">Обратная сторона</label>
                <input type="text" id="backText" name="backText" required placeholder="Перевод">
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
            <button type="submit" class="btn btn-primary" >Сохранить</button>
            
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Элементы DOM
    const modal = document.getElementById('cardModal');
    const addCardBtn = document.getElementById('addCardBtn');
    const closeBtn = document.querySelector('.close');
    const cardForm = document.getElementById('cardForm');
    const cardsContainer = document.getElementById('cardsContainer');
    const applyFilterBtn = document.getElementById('applyFilterBtn');
    
    // Открытие модального окна для добавления карточки
    addCardBtn.addEventListener('click', function() {
        document.getElementById('modalTitle').textContent = 'Добавить новую карточку';
        document.getElementById('cardId').value = '';
        cardForm.reset();
        modal.style.display = 'block';
    });
    
    // Закрытие модального окна
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    // Закрытие модального окна при клике вне его
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Переворот карточки
    cardsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-flip') || e.target.closest('.btn-flip')) {
            const card = e.target.closest('.word-card');
            const front = card.querySelector('.card-front');
            const back = card.querySelector('.card-back');
            
            front.classList.toggle('active');
            back.classList.toggle('active');
        }
    });
    
    // Редактирование карточки
    cardsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-edit') || e.target.closest('.btn-edit')) {
            const cardId = e.target.closest('.btn-edit').dataset.id;
            const card = document.querySelector(`.word-card[data-id="${cardId}"]`);
            
            const frontText = card.querySelector('.card-front h3').textContent;
            const backText = card.querySelector('.card-back h3').textContent;
            const language = card.querySelector('.language').textContent.toLowerCase();
            const difficulty = card.querySelector('.badge').textContent.toLowerCase();
            
            document.getElementById('modalTitle').textContent = 'Редактировать карточку';
            document.getElementById('cardId').value = cardId;
            document.getElementById('frontText').value = frontText;
            document.getElementById('backText').value = backText;
            document.getElementById('cardLanguage').value = language;
            document.getElementById('cardDifficulty').value = difficulty;
            
            modal.style.display = 'block';
        }
    });
    
    // Удаление карточки
    cardsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-delete') || e.target.closest('.btn-delete')) {
            if (confirm('Вы уверены, что хотите удалить эту карточку?')) {
                const cardId = e.target.closest('.btn-delete').dataset.id;
                
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_card&cardId=${cardId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const cardElement = document.querySelector(`.word-card[data-id="${cardId}"]`);
                        if (cardElement) {
                            cardElement.remove();
                            
                            // Check if there are any cards left
                            if (cardsContainer.querySelectorAll('.word-card').length === 0) {
                                cardsContainer.innerHTML = `
                                    <div class="empty-state">
                                        <i class="fas fa-layer-group"></i>
                                        <h3>У вас пока нет карточек</h3>
                                        <p>Начните добавлять карточки для изучения слов</p>
                                    </div>
                                `;
                            }
                        }
                    } else {
                        showAlert('Ошибка', 'Не удалось удалить карточку');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Ошибка', 'Произошла ошибка при удалении карточки');
                });
            }
        }
    });
    
    // Фильтрация карточек
    applyFilterBtn.addEventListener('click', function() {
        const language = document.getElementById('languageFilter').value;
        const difficulty = document.getElementById('difficultyFilter').value;
        
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=filter_cards&language=${language}&difficulty=${difficulty}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cardsContainer.innerHTML = data.html;
            } else {
                alert('Ошибка при фильтрации карточек');
            }
        })
        .catch(error => console.error('Error:', error));
    });
    
    // Обработка формы
    cardForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const cardId = document.getElementById('cardId').value;
        const frontText = document.getElementById('frontText').value;
        const backText = document.getElementById('backText').value;
        const language = document.getElementById('cardLanguage').value;
        const difficulty = document.getElementById('cardDifficulty').value;
        
        const action = cardId ? 'edit_card' : 'add_card';
        const formData = new FormData();
        formData.append('action', action);
        if (cardId) formData.append('cardId', cardId);
        formData.append('frontText', frontText);
        formData.append('backText', backText);
        formData.append('language', language);
        formData.append('difficulty', difficulty);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.style.display = 'none';
                
                if (action === 'add_card') {
                    // Добавляем новую карточку
                    const newCard = document.createElement('div');
                    newCard.className = 'word-card';
                    newCard.dataset.id = data.card.id;
                    newCard.innerHTML = `
                        <div class="card-front active">
                            <h3>${data.card.front_text}</h3>
                            <button class="btn-flip"><i class="fas fa-sync-alt"></i> Перевернуть</button>
                        </div>
                        <div class="card-back">
                            <h3>${data.card.back_text}</h3>
                            <div class="card-meta">
                                <span class="badge ${data.card.difficulty}">${data.card.difficulty.charAt(0).toUpperCase() + data.card.difficulty.slice(1)}</span>
                                <span class="language">${data.card.language.charAt(0).toUpperCase() + data.card.language.slice(1)}</span>
                            </div>
                            <button class="btn-flip"><i class="fas fa-sync-alt"></i> Перевернуть</button>
                        </div>
                        <div class="card-actions">
                            <button class="btn-edit" data-id="${data.card.id}"><i class="fas fa-edit"></i></button>
                            <button class="btn-delete" data-id="${data.card.id}"><i class="fas fa-trash"></i></button>
                        </div>
                    `;
                    
                    // Убираем пустое состояние, если оно есть
                    if (cardsContainer.querySelector('.empty-state')) {
                        cardsContainer.innerHTML = '';
                    }
                    
                    cardsContainer.appendChild(newCard);
                } else {
                    // Обновляем существующую карточку
                    const card = document.querySelector(`.word-card[data-id="${cardId}"]`);
                    if (card) {
                        card.querySelector('.card-front h3').textContent = frontText;
                        card.querySelector('.card-back h3').textContent = backText;
                        
                        const badge = card.querySelector('.badge');
                        badge.className = 'badge ' + difficulty;
                        badge.textContent = difficulty.charAt(0).toUpperCase() + difficulty.slice(1);
                        
                        card.querySelector('.language').textContent = language.charAt(0).toUpperCase() + language.slice(1);
                    }
                }
            } else {
                alert('Ошибка при сохранении карточки');
            }
        })
        .catch(error => console.error('Error:', error));
    });
});
</script>
