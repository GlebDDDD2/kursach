<?php $queryBase = 'index.php?page=catalog'; ?>
<div class="container py-4">
    <h1 class="mb-4">Каталог объектов</h1>
    <div class="card mb-4 shadow-sm"><div class="card-body"><form method="GET" class="row g-3"><input type="hidden" name="page" value="catalog">
        <div class="col-md-3"><label class="form-label">Поиск</label><input type="text" name="q" class="form-control" value="<?= $e($filters['q']) ?>" placeholder="Название или адрес"></div>
        <div class="col-md-2"><label class="form-label">Тип</label><select name="type" class="form-select"><option value="">Все</option><option value="apartment" <?= $filters['type'] === 'apartment' ? 'selected' : '' ?>>Квартира</option><option value="house" <?= $filters['type'] === 'house' ? 'selected' : '' ?>>Дом</option></select></div>
        <div class="col-md-2"><label class="form-label">Цена от</label><input type="number" name="price_from" class="form-control" value="<?= $e($filters['price_from']) ?>"></div>
        <div class="col-md-2"><label class="form-label">Цена до</label><input type="number" name="price_to" class="form-control" value="<?= $e($filters['price_to']) ?>"></div>
        <div class="col-md-1"><label class="form-label">Этаж</label><input type="number" name="floor" class="form-control" value="<?= $e($filters['floor']) ?>"></div>
        <div class="col-md-1"><label class="form-label">Площадь от</label><input type="number" step="0.01" name="area_from" class="form-control" value="<?= $e($filters['area_from']) ?>"></div>
        <div class="col-md-1"><label class="form-label">Площадь до</label><input type="number" step="0.01" name="area_to" class="form-control" value="<?= $e($filters['area_to']) ?>"></div>
        <div class="col-12"><button class="btn btn-primary">Фильтровать</button> <a href="index.php?page=catalog" class="btn btn-outline-secondary">Сбросить</a></div>
    </form></div></div>
    <div class="row">
        <?php foreach ($properties as $property): ?>
            <div class="col-md-4 mb-4"><div class="card h-100 shadow-sm">
                <?php if (!empty($property['main_photo'])): ?><img src="<?= $e($property['main_photo']) ?>" class="card-img-top" alt="Фото" style="height:220px; object-fit:cover;"><?php else: ?><div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height:220px;">Нет фото</div><?php endif; ?>
                <div class="card-body"><h2 class="h5"><?= $e($property['title']) ?></h2><p class="mb-1"><strong>Цена:</strong> <?= number_format((float) $property['price'], 0, ',', ' ') ?> ₽</p><p class="mb-1"><strong>Район:</strong> <?= $e($property['district_name']) ?></p><p class="mb-1"><strong>Площадь:</strong> <?= $e($property['area']) ?> м²</p><p class="mb-0"><strong>Риелтор:</strong> <?= $e($property['realtor_name']) ?></p></div>
                <div class="card-footer bg-white border-0"><a href="index.php?page=property&id=<?= (int) $property['id'] ?>" class="btn btn-primary w-100">Подробнее</a></div>
            </div></div>
        <?php endforeach; ?>
        <?php if ($properties === []): ?><p class="text-muted">По вашему запросу ничего не найдено.</p><?php endif; ?>
    </div>
    <?php if ($totalPages > 1): ?>
        <nav><ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++):
                $params = array_filter(array_merge($filters, ['page' => 'catalog', 'p' => $i]), static fn($v) => $v !== '');
                ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="index.php?<?= http_build_query($params) ?>"><?= $i ?></a></li>
            <?php endfor; ?>
        </ul></nav>
    <?php endif; ?>
</div>
