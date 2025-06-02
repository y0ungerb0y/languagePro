document.addEventListener('DOMContentLoaded', function() {
    const addCardBtn = document.getElementById('addCardBtn');
    const cardsContainer = document.getElementById('cardsContainer');
    const cardModal = document.getElementById('cardModal');
    const closeModal = document.querySelector('.modal-content .close');
    const cardForm = document.getElementById('cardForm');
    const modalTitle = document.getElementById('modalTitle');
    const cardIdInput = document.getElementById('cardId');
    const languageFilter = document.getElementById('languageFilter');
    const difficultyFilter = document.getElementById('difficultyFilter');
    
    // Загрузить карточки при старте
    loadCards();
    
    // Открыть модальное окно для добавления
    addCardBtn.addEventListener('click', () => {
        openModal('add');
    });
    
    // Закрыть модальное окно
    closeModal.addEventListener('click', () => {
        cardModal.style.display = 'none';
    });
    
    // Закрыть модальное окно при клике вне его
    window.addEventListener('click', (event) => {
        if (event.target === cardModal) {
            cardModal.style.display = 'none';
        }
    });
    
    // Обработка формы
    cardForm.addEventListener('submit', (e) => {
        e.preventDefault();
        saveCard();
    });
    
    // Делегирование событий для динамических элементов
    cardsContainer.addEventListener('click', function(e) {
        const cardEl = e.target.closest('.word-card');
        if (!cardEl) return;
        
        const cardId = cardEl.dataset.id;
        
        // Редактирование
        if (e.target.classList.contains('btn-edit')) {
            loadCardForEditing(cardId);
        }
        // Удаление
        else if (e.target.classList.contains('btn-delete')) {
            deleteCard(cardId, cardEl);
        }
        // Переворот карточки
        else if (e.target.classList.contains('btn-flip')) {
            flipCard(cardEl);
        }
    });
    
    // Обработчики фильтров
    languageFilter.addEventListener('change', filterCards);
    difficultyFilter.addEventListener('change', filterCards);
    
    // Загрузка карточек
    function loadCards() {
        fetch('../../php/api/get_cards.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка загрузки карточек');
                }
                return response.json();
            })
            .then(cards => {
                renderCards(cards);
            })
            .catch(error => {
                console.error('Ошибка загрузки карточек:', error);
                showEmptyState();
            });
    }
    
    // Отображение карточек
    function renderCards(cards) {
        cardsContainer.innerHTML = ''; // Очищаем контейнер
        
        if (!cards || cards.length === 0) {
            showEmptyState();
            return;
        }
        
        cards.forEach(card => {
            cardsContainer.appendChild(createCardElement(card));
        });
    }
    
    // Создание HTML элемента карточки
    function createCardElement(card) {
        const cardEl = document.createElement('div');
        cardEl.className = 'word-card';
        cardEl.dataset.id = card.id;
        
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
    
    // Пустое состояние
    function showEmptyState() {
        cardsContainer.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-layer-group"></i>
                <h3>У вас пока нет карточек</h3>
                <p>Начните добавлять карточки для изучения слов</p>
                <button id="addFirstCardBtn" class="btn btn-primary">Добавить первую карточку</button>
            </div>
        `;
        document.getElementById('addFirstCardBtn')?.addEventListener('click', () => {
                openModal('add');
        });
    }
    
    // Открытие модального окна
    function openModal(action) {
        modalTitle.textContent = action === 'add' 
            ? 'Добавить новую карточку' 
            : 'Редактировать карточку';
        cardForm.reset(); // Очистка формы
        if (action === 'add') cardIdInput.value = '';
        cardModal.style.display = 'flex';
        // Анимация плавного появления
        cardModal.querySelector('.modal-content').style.animation = 'fadeIn 0.3s';
    }
    
    // Загрузка данных карточки для редактирования
    function loadCardForEditing(cardId) {
        fetch(`get_card.php?id=${cardId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка загрузки карточки для редактирования');
                }
                return response.json();
            })
            .then(card => {
                openModal('edit');
                document.getElementById('frontText').value = card.front_text;
                document.getElementById('backText').value = card.back_text;
                document.getElementById('cardLanguage').value = card.language;
                document.getElementById('cardDifficulty').value = card.difficulty;
                cardIdInput.value = card.id; // Заполняем скрытое поле ID
            })
            .catch(error => {
                console.error('Ошибка загрузки карточки:', error);
                alert('Ошибка при загрузке данных карточки');
            });
    }
    
    // Сохранение карточки (добавление/редактирование)
    function saveCard() {
        const formData = new FormData(cardForm);
        const cardId = cardIdInput.value; // Используем скрытое поле id
        const action = cardId ? 'edit' : 'add';
        formData.append('action', action);
        
        fetch('/../../php/api/save_card.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                cardModal.style.display = 'none';
                loadCards(); // Обновить список
            } else {
                alert(result.error || 'Ошибка при сохранении');
            }
        })
        .catch(error => {
            console.error('Ошибка при сохранении:', error);
            alert('Произошла ошибка при сохранении');
        });
    }
    
    // Удаление карточки
    function deleteCard(cardId, cardEl) {
        if (!confirm('Вы уверены, что хотите удалить эту карточку?')) return;
        
        fetch('../../php/api/delete_card.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ cardId: cardId })
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
                checkEmptyState();
            } else {
                alert(result.error || 'Ошибка при удалении');
            }
        })
        .catch(error => {
            console.error('Ошибка при удалении:', error);
            alert('Произошла ошибка при удалении');
        });
    }
    
    // Переворот карточки
    function flipCard(cardEl) {
        const front = cardEl.querySelector('.card-front');
        const back = cardEl.querySelector('.card-back');
        front.classList.toggle('active');
        back.classList.toggle('active');
    }
    
    // Фильтрация карточек
    function filterCards() {
        const language = languageFilter.value;
        const difficulty = difficultyFilter.value;
        
        document.querySelectorAll('.word-card').forEach(card => {
            const cardLanguage = card.querySelector('.language').textContent.toLowerCase();
            const cardDifficulty = card.querySelector('.badge').classList[1];
            
            const languageOk = language === 'all' || cardLanguage === language;
            const difficultyOk = difficulty === 'all' || cardDifficulty === difficulty;
            
            card.style.display = languageOk && difficultyOk ? 'block' : 'none';
        });
    }
    
    // Экранирование HTML
    function escHtml(unsafe) {
        const div = document.createElement('div');
        div.textContent = unsafe;
        return div.innerHTML;
    }
});