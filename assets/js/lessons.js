document.addEventListener('DOMContentLoaded', function() {
    const lessonModal = document.getElementById('lessonModal');
    const closeModal = document.querySelector('.modal-content .close');
    const lessonContent = document.getElementById('lessonContent');
    const prevSlideBtn = document.getElementById('prevSlide');
    const nextSlideBtn = document.getElementById('nextSlide');
    const completeLessonBtn = document.getElementById('completeLesson');
    const slideCounter = document.getElementById('slideCounter');
    const lessonsContainer = document.querySelector('.lessons-container');

    let currentLessonId = null;
    let currentSlideIndex = 0;
    let lessonSlides = [];

    // Закрытие модального окна
    closeModal.addEventListener('click', () => {
        lessonModal.style.display = 'none';
    });

    // Закрытие модального окна при клике вне его
    window.addEventListener('click', (event) => {
        if (event.target === lessonModal) {
            lessonModal.style.display = 'none';
        }
    });

    // Обработка кнопок "Начать" и "Повторить"
    lessonsContainer.addEventListener('click', function(e) {
        const target = e.target;
        const lessonCard = target.closest('.lesson-card');

        if (!lessonCard) return;

        const lessonId = lessonCard.dataset.id;

        if (target.classList.contains('start-btn') || target.classList.contains('review-btn')) {
            startLesson(lessonId);
        }
    });

    // Начало урока
    function startLesson(lessonId) {
        currentLessonId = lessonId;
        currentSlideIndex = 0;

        fetch(`get_lesson_slides.php?lessonId=${lessonId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    lessonSlides = data.slides;
                    showSlide(currentSlideIndex);
                    lessonModal.style.display = 'flex';
                } else {
                    alert(data.error || 'Ошибка при загрузке урока');
                }
            })
            .catch(error => {
                console.error('Ошибка при загрузке урока:', error);
                alert('Произошла ошибка при загрузке урока');
            });
    }

    // Отображение слайда
    function showSlide(index) {
        if (index < 0 || index >= lessonSlides.length) return;

        lessonContent.innerHTML = lessonSlides[index].content;
        slideCounter.textContent = `${index + 1}/${lessonSlides.length}`;

        prevSlideBtn.style.display = index === 0 ? 'none' : 'inline-block';
        nextSlideBtn.style.display = index === lessonSlides.length - 1 ? 'none' : 'inline-block';
        completeLessonBtn.style.display = index === lessonSlides.length - 1 ? 'inline-block' : 'none';
    }

    // Переход к предыдущему слайду
    prevSlideBtn.addEventListener('click', () => {
        if (currentSlideIndex > 0) {
            currentSlideIndex--;
            showSlide(currentSlideIndex);
        }
    });

    // Переход к следующему слайду
    nextSlideBtn.addEventListener('click', () => {
        if (currentSlideIndex < lessonSlides.length - 1) {
            currentSlideIndex++;
            showSlide(currentSlideIndex);
        }
    });

    // Завершение урока
    completeLessonBtn.addEventListener('click', () => {
        fetch('complete_lesson.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ lessonId: currentLessonId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                lessonModal.style.display = 'none';
                alert('Урок завершен!');
                location.reload(); // Обновляем страницу для отображения изменений
            } else {
                alert(data.error || 'Ошибка при завершении урока');
            }
        })
        .catch(error => {
            console.error('Ошибка при завершении урока:', error);
            alert('Произошла ошибка при завершении урока');
        });
    });
});