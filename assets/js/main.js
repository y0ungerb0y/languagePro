// Общие функции
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация модальных окон
    initModals();
    
    // Инициализация карточек слов
    if(document.querySelector('.word-card')) {
        initWordCards();
    }
    
    // Инициализация уроков
    if(document.querySelector('.lesson-card')) {
        initLessons();
    }
});

function initModals() {
    // Модальное окно добавления карточки
    const addCardModal = document.getElementById('addCardModal');
    if(addCardModal) {
        const addCardBtn = document.getElementById('addCardBtn');
        const addFirstCardBtn = document.getElementById('addFirstCardBtn');
        const span = addCardModal.querySelector('.close');
        
        if(addCardBtn) addCardBtn.onclick = function() { addCardModal.style.display = 'block'; }
        if(addFirstCardBtn) addFirstCardBtn.onclick = function() { addCardModal.style.display = 'block'; }
        span.onclick = function() { addCardModal.style.display = 'none'; }
        
        window.onclick = function(event) {
            if(event.target == addCardModal) {
                addCardModal.style.display = 'none';
            }
        }
    }
    
    // Обработка формы добавления карточки
    const addCardForm = document.getElementById('addCardForm');
    if(addCardForm) {
        addCardForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            fetch('php/api/cards.php?action=add', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Ошибка при добавлении карточки');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
}

function initWordCards() {
    // Переворот карточек
    document.querySelectorAll('.btn-flip').forEach(btn => {
        btn.addEventListener('click', function() {
            const card = this.closest('.word-card');
            card.classList.toggle('flipped');
        });
    });
    
    // Удаление карточек
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const cardId = this.getAttribute('data-id');
            if(confirm('Вы уверены, что хотите удалить эту карточку?')) {
                fetch(`php/api/cards.php?action=delete&id=${cardId}`)
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        this.closest('.word-card').remove();
                        
                        // Если карточек не осталось, показываем empty state
                        if(document.querySelectorAll('.word-card').length === 0) {
                            document.querySelector('.cards-container').innerHTML = `
                                <div class="empty-state">
                                    <i class="fas fa-layer-group"></i>
                                    <h3>У вас пока нет карточек</h3>
                                    <p>Начните добавлять карточки для изучения слов</p>
                                    <button id="addFirstCardBtn" class="btn btn-primary">Добавить первую карточку</button>
                                </div>
                            `;
                            
                            // Инициализируем кнопку в empty state
                            document.getElementById('addFirstCardBtn').onclick = function() {
                                document.getElementById('addCardModal').style.display = 'block';
                            };
                        }
                    } else {
                        alert(data.message || 'Ошибка при удалении карточки');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        });
    });
    
    // Фильтрация карточек
    const languageFilter = document.getElementById('languageFilter');
    const difficultyFilter = document.getElementById('difficultyFilter');
    
    if(languageFilter && difficultyFilter) {
        const filterCards = () => {
            const language = languageFilter.value;
            const difficulty = difficultyFilter.value;
            
            document.querySelectorAll('.word-card').forEach(card => {
                const cardLanguage = card.querySelector('.language').textContent.toLowerCase();
                const cardDifficulty = card.querySelector('.badge').textContent.toLowerCase();
                
                const languageMatch = language === 'all' || cardLanguage === language;
                const difficultyMatch = difficulty === 'all' || cardDifficulty === difficulty;
                
                if(languageMatch && difficultyMatch) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        };
        
        languageFilter.addEventListener('change', filterCards);
        difficultyFilter.addEventListener('change', filterCards);
    }
}

function initLessons() {
    // Модальное окно урока
    const lessonModal = document.getElementById('lessonModal');
    if(lessonModal) {
        const span = lessonModal.querySelector('.close');
        
        span.onclick = function() { 
            lessonModal.style.display = 'none';
            document.getElementById('lessonContent').innerHTML = '';
        }
        
        window.onclick = function(event) {
            if(event.target == lessonModal) {
                lessonModal.style.display = 'none';
                document.getElementById('lessonContent').innerHTML = '';
            }
        }
    }
    
    // Загрузка урока
    document.querySelectorAll('.start-btn, .review-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const lessonId = this.getAttribute('data-id');
            
            fetch(`php/api/lessons.php?action=get&id=${lessonId}`)
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('lessonContent').innerHTML = data.content;
                    document.getElementById('lessonModal').style.display = 'block';
                    
                    // Инициализация слайдов урока
                    initLessonSlides(data.slides);
                } else {
                    alert(data.message || 'Ошибка при загрузке урока');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
    
    // Завершение урока
    const completeLessonBtn = document.getElementById('completeLesson');
    if(completeLessonBtn) {
        completeLessonBtn.addEventListener('click', function() {
            const lessonId = this.getAttribute('data-lesson-id');
            
            fetch(`php/api/lessons.php?action=complete&id=${lessonId}`)
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Урок успешно завершен! Вы получили ' + data.points + ' очков.');
                    location.reload();
                } else {
                    alert(data.message || 'Ошибка при завершении урока');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
}

function initLessonSlides(slides) {
    let currentSlide = 0;
    const totalSlides = slides.length;
    const lessonContent = document.getElementById('lessonContent');
    const slideCounter = document.getElementById('slideCounter');
    const prevBtn = document.getElementById('prevSlide');
    const nextBtn = document.getElementById('nextSlide');
    const completeBtn = document.getElementById('completeLesson');
    
    const showSlide = (index) => {
        lessonContent.innerHTML = slides[index];
        slideCounter.textContent = `${index + 1}/${totalSlides}`;
        
        prevBtn.style.display = index === 0 ? 'none' : 'block';
        nextBtn.style.display = index === totalSlides - 1 ? 'none' : 'block';
        completeBtn.style.display = index === totalSlides - 1 ? 'block' : 'none';
        
        if(index === totalSlides - 1) {
            completeBtn.setAttribute('data-lesson-id', lessonContent.querySelector('[data-lesson-id]').getAttribute('data-lesson-id'));
        }
    };
    
    showSlide(0);
    
    prevBtn.addEventListener('click', () => {
        if(currentSlide > 0) {
            currentSlide--;
            showSlide(currentSlide);
        }
    });
    
    nextBtn.addEventListener('click', () => {
        if(currentSlide < totalSlides - 1) {
            currentSlide++;
            showSlide(currentSlide);
        }
    });
}