<header class="hero-section text-white d-flex align-items-center">
    <div class="container text-center">
        <h1 class="display-4 fw-bold">Сайт агентства недвижимости</h1>
        <p class="lead mb-4">Квартиры, дома, фильтр, слайдер фото, личный кабинет и заявки на просмотр.</p>
        <a href="index.php?page=catalog" class="btn btn-primary btn-lg">Открыть каталог</a>
    </div>
</header>
<div class="container py-5">
    <div class="row g-4 mb-5">
        <div class="col-md-4"><div class="card h-100 shadow-sm"><div class="card-body"><h2 class="h5">Каталог объектов</h2><p class="mb-0">Просмотр квартир и домов с основной информацией.</p></div></div></div>
        <div class="col-md-4"><div class="card h-100 shadow-sm"><div class="card-body"><h2 class="h5">Сложный фильтр</h2><p class="mb-0">Поиск по цене, этажу, площади и типу недвижимости.</p></div></div></div>
        <div class="col-md-4"><div class="card h-100 shadow-sm"><div class="card-body"><h2 class="h5">Безопасная логика</h2><p class="mb-0">RBAC, CSRF, XSS, Anti-IDOR и Prepared Statements.</p></div></div></div>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h3 mb-0">Новые объекты</h2>
        <a href="index.php?page=catalog" class="btn btn-outline-primary">Смотреть все</a>
    </div>
    <div class="row">
        <?php foreach ($properties as $property): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if (!empty($property['main_photo'])): ?>
                        <img src="<?= $e($property['main_photo']) ?>" class="card-img-top" alt="Фото объекта" style="height:220px; object-fit:cover;">
                    <?php else: ?>
                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height:220px;">Нет фото</div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h3 class="h5 card-title"><?= $e($property['title']) ?></h3>
                        <p class="mb-1"><strong>Цена:</strong> <?= number_format((float) $property['price'], 0, ',', ' ') ?> ₽</p>
                        <p class="mb-1"><strong>Район:</strong> <?= $e($property['district_name']) ?></p>
                        <p class="mb-0 text-muted text-truncate"><?= $e($property['address']) ?></p>
                    </div>
                    <div class="card-footer bg-white border-0"><a href="index.php?page=property&id=<?= (int) $property['id'] ?>" class="btn btn-primary w-100">Подробнее</a></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
