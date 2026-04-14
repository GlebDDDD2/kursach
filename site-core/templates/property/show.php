<?php use App\Core\Auth; ?>
<div class="container py-4">
    <?php if (($msg = \App\Core\Helpers::getFlash('success')) !== ''): ?><div class="alert alert-success"><?= $e($msg) ?></div><?php endif; ?>
    <?php if (($msg = \App\Core\Helpers::getFlash('error')) !== ''): ?><div class="alert alert-danger"><?= $e($msg) ?></div><?php endif; ?>
    <div class="row g-4">
        <div class="col-lg-7">
            <div id="propertyCarousel" class="carousel slide shadow-sm rounded overflow-hidden">
                <div class="carousel-inner">
                    <?php $slides = $photos !== [] ? $photos : [['photo_path' => $property['main_photo']]]; ?>
                    <?php foreach ($slides as $index => $photo): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <img src="<?= $e($photo['photo_path']) ?>" class="d-block w-100" alt="Фото объекта" style="height:420px; object-fit:cover;">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($slides) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                    <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm mb-4"><div class="card-body">
                <h1 class="h3 mb-3"><?= $e($property['title']) ?></h1>
                <p><strong>Цена:</strong> <?= number_format((float) $property['price'], 0, ',', ' ') ?> ₽</p>
                <p><strong>Тип:</strong> <?= $property['property_type'] === 'house' ? 'Дом' : 'Квартира' ?></p>
                <p><strong>Район:</strong> <?= $e($property['district_name']) ?></p>
                <p><strong>Адрес:</strong> <?= $e($property['address']) ?></p>
                <p><strong>Площадь:</strong> <?= $e($property['area']) ?> м²</p>
                <p><strong>Комнат:</strong> <?= $e($property['rooms'] ?: '-') ?></p>
                <p><strong>Этаж:</strong> <?= $e($property['floor'] ?: '-') ?></p>
                <p><strong>Риелтор:</strong> <?= $e($property['realtor_name']) ?></p>
                <p class="mb-0"><strong>Контакты:</strong> <?= $e($property['realtor_phone'] ?: '-') ?> / <?= $e($property['realtor_email'] ?: '-') ?></p>
            </div></div>
            <div class="card shadow-sm"><div class="card-body">
                <h2 class="h5">Заявка на просмотр</h2>
                <?php if ($isLoggedIn): ?>
                    <form method="POST" action="index.php?page=request_viewing">
                        <input type="hidden" name="csrf_token" value="<?= $e($csrf()) ?>">
                        <input type="hidden" name="property_id" value="<?= (int) $property['id'] ?>">
                        <div class="mb-3"><label class="form-label">Имя</label><input type="text" name="client_name" class="form-control" required value="<?= $e($_SESSION['username'] ?? '') ?>"></div>
                        <div class="mb-3"><label class="form-label">Телефон</label><input type="text" name="client_phone" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="client_email" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Желаемая дата</label><input type="date" name="preferred_date" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Комментарий</label><textarea name="comment" class="form-control" rows="3"></textarea></div>
                        <button class="btn btn-primary w-100">Отправить заявку</button>
                    </form>
                <?php else: ?>
                    <p class="text-muted">Чтобы отправить заявку, войдите в аккаунт.</p>
                    <a href="index.php?page=login" class="btn btn-outline-primary">Войти</a>
                <?php endif; ?>
            </div></div>
        </div>
    </div>
    <div class="card shadow-sm mt-4"><div class="card-body"><h2 class="h5">Описание</h2><p class="mb-0"><?= nl2br($e($property['description'] ?: 'Описание отсутствует.')) ?></p></div></div>
    <div class="card shadow-sm mt-4"><div class="card-body"><h2 class="h5">Расположение</h2><iframe class="w-100 rounded border" height="320" src="https://yandex.ru/map-widget/v1/?text=<?= urlencode($property['address']) ?>"></iframe><div class="mt-3"><a class="btn btn-outline-dark" target="_blank" href="https://yandex.ru/maps/?text=<?= urlencode($property['address']) ?>">Открыть в Яндекс.Картах</a></div></div></div>
</div>
