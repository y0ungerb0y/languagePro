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
                        document.querySelector(`.word-card[data-id="${cardId}"]`).remove();
                        
                        // Если карточек не осталось, показываем пустое состояние
                        if (cardsContainer.querySelectorAll('.word-card').length === 0) {
                            cardsContainer.innerHTML = `
                                <div class="empty-state">
                                    <i class="fas fa-layer-group"></i>
                                    <h3>У вас пока нет карточек</h3>
                                    <p>Начните добавлять карточки для изучения слов</p>
                                </div>
                            `;
                        }
                    } else {
                        alert('Ошибка при удалении карточки');
                    }
                })
                .catch(error => console.error('Error:', error));
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