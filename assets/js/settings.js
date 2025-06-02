document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.getElementById('profileForm');
    const passwordForm = document.getElementById('passwordForm');
    const avatarForm = document.getElementById('avatarForm');

    profileForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(profileForm);
        formData.append('action', 'updateProfile');

        fetch('settings.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Профиль обновлен успешно!');
            } else {
                alert('Ошибка обновления профиля');
            }
        });
    });

    passwordForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(passwordForm);
        formData.append('action', 'updatePassword');

        fetch('settings.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Пароль обновлен успешно!');
            } else {
                alert(data.error);
            }
        });
    });

    avatarForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(avatarForm);
        formData.append('action', 'uploadAvatar');

        fetch('settings.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Аватар загружен успешно!');
                document.querySelector('.profile-card .avatar img').src = 'uploads/avatars/' + data.avatar;
            } else {
                alert(data.error);
            }
        });
    });
});