<div class="sidebar">
    <div class="profile-card">
        <div class="avatar">
            <img src="uploads/avatars/<?= $user['avatar'];?>" alt="Avatar"> 
        </div>
        <h3><?php echo $user['username']; ?></h3>
        <p><?php echo $user['email']; ?></p>
    </div>

    <nav class="dashboard-nav">
        <ul>
            <li <?php echo ($page_title == 'Личный кабинет') ? 'class="active"' : ''; ?>><a href="/dashboard"><i class="fas fa-home"></i> Главная</a></li>
            <li <?php echo ($page_title == 'Карточки слов') ? 'class="active"' : ''; ?>><a href="/cards"><i class="fas fa-layer-group"></i> Карточки слов</a></li>
            <li <?php echo ($page_title == 'Уроки') ? 'class="active"' : ''; ?>><a href="/lessons"><i class="fas fa-book-open"></i> Уроки</a></li>
            <li <?php echo ($page_title == 'Настройки') ? 'class="active"' : ''; ?>><a href="/settings"><i class="fas fa-cog"></i> Настройки</a></li>
            <li><a href="/logout"><i class="fas fa-sign-out-alt"></i> Выход</a></li>
        </ul>
    </nav>
</div>