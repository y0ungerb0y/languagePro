<?php
$page_title = 'Карточки слов';
$page_css = 'cards';
$page_js = 'cards';
include 'app/views/components/header.php'; 

// ---- Обработка AJAX запросов ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $card_id = filter_input(INPUT_POST, 'cardId', FILTER_VALIDATE_INT);
        $front_text = trim($_POST['frontText'] ?? '');
        $back_text = trim($_POST['backText'] ?? '');
        $language = $_POST['language'] ?? '';
        $difficulty = $_POST['difficulty'] ?? '';

        if (empty($front_text) || empty($back_text) || empty($language) || empty($difficulty)) {
            http_response_code(400); // Bad Request
            exit(json_encode(['success' => false, 'error' => 'Заполните все поля']));
        }

        try {
            if ($card_id) {
                // Update
                $stmt = $pdo->prepare("UPDATE word_cards SET front_text = ?, back_text = ?, language = ?, difficulty = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$front_text, $back_text, $language, $difficulty, $card_id, $user_id]);
                $rows_affected = $stmt->rowCount();
                if ($rows_affected === 0) {
                    http_response_code(404); // Not Found (or not owned)
                    exit(json_encode(['success' => false, 'error' => 'Карточка не найдена или не принадлежит вам']));
                }
                $success = true;
                $card = ['id' => $card_id, 'front_text' => $front_text, 'back_text' => $back_text, 'language' => $language, 'difficulty' => $difficulty];
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO word_cards (user_id, front_text, back_text, language, difficulty, next_review) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$user_id, $front_text, $back_text, $language, $difficulty]);
                $card_id = $pdo->lastInsertId();
                $success = true;
                $card = ['id' => $card_id, 'front_text' => $front_text, 'back_text' => $back_text, 'language' => $language, 'difficulty' => $difficulty];
            }

            echo json_encode(['success' => true, 'card' => $card]); // Возвращаем карточку
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Ошибка базы данных: ' . $e->getMessage()]);
        }
        exit;
    } elseif ($action === 'delete') {
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
                http_response_code(404); // Not Found
                echo json_encode(['success' => false, 'error' => 'Карточка не найдена или не принадлежит вам']);
            }

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Ошибка базы данных: ' . $e->getMessage()]);
        }
        exit;
    } elseif ($action === 'getCards') {
        $stmt = $pdo->prepare("SELECT * FROM word_cards WHERE user_id = ? ORDER BY next_review ASC");
        $stmt->execute([$user_id]);
        $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode($cards);
        exit;
    }

    http_response_code(400);
    exit(json_encode(['success' => false, 'error' => 'Неверное действие']));
}

// ---- Получение данных для отображения (первоначальная загрузка) ----
try {
    $stmt = $pdo->prepare("SELECT * FROM word_cards WHERE user_id = ? ORDER BY next_review ASC");
    $stmt->execute([$user_id]);
    $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $cards = []; //  В случае ошибки, чтобы не было проблем с отображением
    error_log("Ошибка при получении карточек: " . $e->getMessage()); // Логируем ошибки в файл
}
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
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
    </div>
</div>
<script>
    // cards.js
document.addEventListener('DOMContentLoaded', function() {
    const addCardBtn = document.getElementById('addCardBtn');
    const cardsContainer = document.getElementById('cardsContainer');
    const cardModal = document.getElementById('cardModal');
    const closeModal = document.querySelector('.modal-content .close');
    const cardForm = document.getElementById('cardForm');
    const modalTitle = document.getElementById('modalTitle');
    const cardIdInput = document.getElementById('cardId');
    const frontTextInput = document.getElementById('frontText');
    const backTextInput = document.getElementById('backText');
    const cardLanguageInput = document.getElementById('cardLanguage');
    const cardDifficultyInput = document.getElementById('cardDifficulty');


    // 1. Загрузка карточек при загрузке страницы
    loadCards();

    // 2. Открытие модального окна для добавления
    addCardBtn.addEventListener('click', () => openModal('add'));

    // 3. Закрытие модального окна
    closeModal.addEventListener('click', () => closeModalFunction());

    // 4. Закрытие модального окна при клике вне его
    window.addEventListener('click', (event) => {
        if (event.target === cardModal) {
            closeModalFunction();
        }
    });

    // 5. Обработка отправки формы (добавление/редактирование)
    cardForm.addEventListener('submit', (e) => {
        e.preventDefault();
        saveCard();
    });

    // 6. Делегирование событий для кнопок (удаление/редактирование/переворот)
    cardsContainer.addEventListener('click', function(e) {
        const target = e.target;
        const cardEl = target.closest('.word-card'); // Находим ближайший .word-card

        if (!cardEl) return; //  Если клик не по карточке, выходим

        const cardId = cardEl.dataset.id;

        // Редактирование
        if (target.classList.contains('btn-edit')) {
            loadCardForEditing(cardId);
        }
        // Удаление
        else if (target.classList.contains('btn-delete')) {
            deleteCard(cardId, cardEl);
        }
        // Переворот
        else if (target.classList.contains('btn-flip')) {
            flipCard(cardEl);
        }
    });


    // Функции

    // 7. Загрузка карточек с сервера
    function loadCards() {
        fetch('cards.php?action=getCards') //  Используем GET параметр для запроса карточек
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка загрузки карточек');
                }
                return response.json();
            })
            .then(cards => {
                renderCards(cards); // Отображаем карточки
            })
            .catch(error => {
                console.error('Ошибка загрузки карточек:', error);
                cardsContainer.innerHTML = '<p>Не удалось загрузить карточки.</p>'; //  Простой вывод ошибки
            });
    }

    // 8. Отображение карточек (с экранированием HTML)
    function renderCards(cards) {
        cardsContainer.innerHTML = ''; // Очищаем контейнер
        if (!cards || cards.length === 0) {
            cardsContainer.innerHTML = '<p>Нет карточек.</p>'; // Простой вывод
            return;
        }

        cards.forEach(card => {
            cardsContainer.appendChild(createCardElement(card));
        });
    }

    // 9. Создание HTML элемента карточки
    function createCardElement(card) {
        const cardEl = document.createElement('div');
        cardEl.className = 'word-card';
        cardEl.dataset.id = card.id; // Важно для идентификации

        cardEl.innerHTML = `
            <div class="card-front active">
                <h3>${escHtml(card.front_text)}</h3>
                <button class="btn-flip"><i class="fas fa-sync-alt"></i> Перевернуть</button>
            </div>
            <div class="card-back">
                <h3>${escHtml(card.back_text)}</h3>
                <div class="card-meta">
                    <span class="badge ${card.difficulty}">${escHtml(card.difficulty.charAt(0).toUpperCase() + card.difficulty.slice(1))}</span>
                    <span class="language">${escHtml(card.language.charAt(0).toUpperCase() + card.language.slice(1))}</span>
                </div>
                <button class="btn-flip"><i class="fas fa-sync-alt"></i> Перевернуть</button>
            </div>
            <div class="card-actions">
                <button class="btn-edit" data-id="${card.id}"><i class="fas fa-edit"></i></button>
                <button class="btn-delete" data-id="${card.id}"><i class="fas fa-trash"></i></button>
            </div>
        `;

        return cardEl;
    }

    // 10. Открытие модального окна
    function openModal(action) {
        modalTitle.textContent = action === 'add' ? 'Добавить новую карточку' : 'Редактировать карточку';
        cardForm.reset();  // Очищаем форму
        cardIdInput.value = ''; //  Сбрасываем cardId
        cardModal.style.display = 'flex';
        // Анимация плавного появления
        cardModal.querySelector('.modal-content').style.animation = 'fadeIn 0.3s';
    }


    // 11. Загрузка данных для редактирования (и открытие модального окна)
    function loadCardForEditing(cardId) {
        fetch('cards.php', {  //  Помним, что save_card.php обрабатывает и add, и edit
          method: 'POST',
          headers: {
              'Content-Type': 'application/x-www-form-urlencoded',  // Важно!
          },
          body: `action=getCard&cardId=${cardId}`  // GET -  не используем  ,  используем POST с action getCard
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка при загрузке карточки для редактирования');
            }
            return response.json();
        })
        .then(card => {
            openModal('edit');
            frontTextInput.value = card.front_text;
            backTextInput.value = card.back_text;
            cardLanguageInput.value = card.language;
            cardDifficultyInput.value = card.difficulty;
            cardIdInput.value = card.id;
        })
        .catch(error => {
            console.error('Ошибка загрузки карточки для редактирования:', error);
            alert('Ошибка при загрузке карточки');
        });
    }

    // 12. Сохранение карточки (добавление/редактирование)
    function saveCard() {
        const formData = new FormData(cardForm);
        const cardId = cardIdInput.value;  // Используем скрытое поле
        formData.append('action', cardId ? 'edit' : 'add');

        fetch('cards.php', {  // Отправляем на cards.php
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка сохранения карточки');
            }
            return response.json();
        })
        .then(result => {
            if (result.success) {
                closeModalFunction();
                loadCards(); // Обновляем список
            } else {
                alert(result.error || 'Ошибка при сохранении');
            }
        })
        .catch(error => {
            console.error('Ошибка при сохранении:', error);
            alert('Произошла ошибка при сохранении');
        });
    }

    // 13. Удаление карточки
    function deleteCard(cardId, cardEl) {
      if (!confirm('Вы уверены, что хотите удалить эту карточку?')) return;

        fetch('cards.php', {  // отправляем запрос на cards.php
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete&cardId=${cardId}` //cardId передаем как строку
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка при удалении карточки');
            }
            return response.json();
        })
        .then(result => {
            if (result.success) {
                // Удаляем из DOM
                cardEl.remove();
                //  Если карточек больше нет, показываем пустое состояние (можно добавить)
            } else {
                alert(result.error || 'Ошибка при удалении');
            }
        })
        .catch(error => {
            console.error('Ошибка при удалении:', error);
            alert('Произошла ошибка при удалении');
        });
    }

    // 14. Переворот карточки
    function flipCard(cardEl) {
        const front = cardEl.querySelector('.card-front');
        const back = cardEl.querySelector('.card-back');
        front.classList.toggle('active');
        back.classList.toggle('active');
    }

    // 15. Закрытие модального окна с анимацией
    function closeModalFunction() {
        cardModal.style.opacity = 0; // Анимация исчезновения
        setTimeout(() => {
            cardModal.style.display = 'none';
        }, 300); // Задержка, чтобы анимация закончилась
    }

    // 16. Экранирование HTML (защита от XSS)
    function escHtml(unsafe) {
        const div = document.createElement('div');
        div.textContent = unsafe;
        return div.innerHTML;
    }
});
</script>