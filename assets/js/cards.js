document.addEventListener('DOMContentLoaded', function() {
    const addCardBtn = document.getElementById('addCardBtn');
    const addFirstCardBtn = document.getElementById('addFirstCardBtn');
    const cardModal = document.getElementById('cardModal');
    const closeModal = document.querySelector('.modal-content .close');
    const cardForm = document.getElementById('cardForm');
    const modalTitle = document.getElementById('modalTitle');
    const cardIdInput = document.getElementById('cardId');
    const frontTextInput = document.getElementById('frontText');
    const backTextInput = document.getElementById('backText');
    const cardLanguageInput = document.getElementById('cardLanguage');
    const cardDifficultyInput = document.getElementById('cardDifficulty');
    const wordCards = document.querySelectorAll('.word-card');
    const languageFilter = document.getElementById('languageFilter');
    const difficultyFilter = document.getElementById('difficultyFilter');

    addCardBtn.addEventListener('click', () => openModal('add'));
    addFirstCardBtn.addEventListener('click', () => openModal('add'));

    closeModal.addEventListener('click', () => {
        cardModal.style.display = 'none';
    });

    window.addEventListener('click', (event) => {
        if (event.target === cardModal) {
            cardModal.style.display = 'none';
        }
    });

    cardForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const formData = new FormData(cardForm);
        const action = cardIdInput.value ? 'edit' : 'add';
        formData.append('action', action);

        fetch('cards.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    });

    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const cardId = btn.getAttribute('data-id');
            const card = document.querySelector(`.word-card[data-id="${cardId}"]`);
            openModal('edit', card);
        });
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', () => {
            const cardId = btn.getAttribute('data-id');
            if (confirm('Вы уверены, что хотите удалить эту карточку?')) {
                fetch('cards.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=delete&cardId=${cardId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        });
    });

    wordCards.forEach(card => {
        const btnFlip = card.querySelectorAll('.btn-flip');
        const cardFront = card.querySelector('.card-front');
        const cardBack = card.querySelector('.card-back');

        btnFlip.forEach(btn => {
            btn.addEventListener('click', () => {
                cardFront.classList.toggle('active');
                cardBack.classList.toggle('active');
            });
        });
    });

    languageFilter.addEventListener('change', filterCards);
    difficultyFilter.addEventListener('change', filterCards);

    function openModal(action, card = null) {
        if (action === 'add') {
            modalTitle.textContent = 'Добавить новую карточку';
            cardIdInput.value = '';
            frontTextInput.value = '';
            backTextInput.value = '';
            cardLanguageInput.value = 'english';
            cardDifficultyInput.value = 'easy';
        } else if (action === 'edit') {
            modalTitle.textContent = 'Редактировать карточку';
            cardIdInput.value = card.getAttribute('data-id');
            frontTextInput.value = card.querySelector('.card-front h3').textContent;
            backTextInput.value = card.querySelector('.card-back h3').textContent;
            cardLanguageInput.value = card.querySelector('.language').textContent.toLowerCase();
            cardDifficultyInput.value = card.querySelector('.badge').classList[1];
        }
        cardModal.style.display = 'flex';
    }

    function filterCards() {
        const language = languageFilter.value;
        const difficulty = difficultyFilter.value;

        wordCards.forEach(card => {
            const cardLanguage = card.querySelector('.language').textContent.toLowerCase();
            const cardDifficulty = card.querySelector('.badge').classList[1];

            const languageMatch = language === 'all' || cardLanguage === language;
            const difficultyMatch = difficulty === 'all' || cardDifficulty === difficulty;

            if (languageMatch && difficultyMatch) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
});