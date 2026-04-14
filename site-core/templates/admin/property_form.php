<?php $editing = isset($property['id']); $galleryText = implode(PHP_EOL, array_map(static fn(array $p): string => $p['photo_path'], $photos)); ?>
<div class="container py-4"><div class="row justify-content-center"><div class="col-lg-9"><div class="card shadow-sm"><div class="card-header bg-white"><h1 class="h4 mb-0"><?= $editing ? 'Редактирование объекта' : 'Добавление объекта' ?></h1></div><div class="card-body">
<?php if ($error !== ''): ?><div class="alert alert-danger"><?= $e($error) ?></div><?php endif; ?>
<form method="POST" action="index.php?page=<?= $e($actionPage) ?>">
<input type="hidden" name="csrf_token" value="<?= $e($csrf()) ?>">
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Название</label><input type="text" name="title" class="form-control" required value="<?= $e($property['title'] ?? '') ?>"></div>
<div class="col-md-3"><label class="form-label">Тип</label><select name="property_type" class="form-select" required><option value="apartment" <?= (($property['property_type'] ?? '') === 'apartment') ? 'selected' : '' ?>>Квартира</option><option value="house" <?= (($property['property_type'] ?? '') === 'house') ? 'selected' : '' ?>>Дом</option></select></div>
<div class="col-md-3"><label class="form-label">Цена</label><input type="number" step="0.01" name="price" class="form-control" required value="<?= $e((string) ($property['price'] ?? '')) ?>"></div>
<div class="col-md-6"><label class="form-label">Район</label><select name="district_id" class="form-select" required><?php foreach ($districts as $district): ?><option value="<?= (int) $district['id'] ?>" <?= ((int) ($property['district_id'] ?? 0) === (int) $district['id']) ? 'selected' : '' ?>><?= $e($district['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Риелтор</label><select name="realtor_id" class="form-select" required><?php foreach ($realtors as $realtor): ?><option value="<?= (int) $realtor['id'] ?>" <?= ((int) ($property['realtor_id'] ?? 0) === (int) $realtor['id']) ? 'selected' : '' ?>><?= $e($realtor['full_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-12"><label class="form-label">Адрес</label><input type="text" name="address" class="form-control" required value="<?= $e($property['address'] ?? '') ?>"></div>
<div class="col-md-3"><label class="form-label">Этаж</label><input type="number" name="floor" class="form-control" value="<?= $e((string) ($property['floor'] ?? '')) ?>"></div>
<div class="col-md-3"><label class="form-label">Всего этажей</label><input type="number" name="total_floors" class="form-control" value="<?= $e((string) ($property['total_floors'] ?? '')) ?>"></div>
<div class="col-md-3"><label class="form-label">Площадь</label><input type="number" step="0.01" name="area" class="form-control" required value="<?= $e((string) ($property['area'] ?? '')) ?>"></div>
<div class="col-md-3"><label class="form-label">Комнат</label><input type="number" name="rooms" class="form-control" value="<?= $e((string) ($property['rooms'] ?? '')) ?>"></div>
<div class="col-md-12"><label class="form-label">Главное фото (URL)</label><input type="text" name="main_photo" class="form-control" value="<?= $e($property['main_photo'] ?? '') ?>"></div>
<div class="col-md-12"><label class="form-label">Галерея фото (каждый URL с новой строки)</label><textarea name="gallery_photos" class="form-control" rows="5"><?= $e($galleryText) ?></textarea></div>
<div class="col-md-12"><label class="form-label">Описание</label><textarea name="description" class="form-control" rows="5"><?= $e($property['description'] ?? '') ?></textarea></div>
<div class="col-md-12 form-check mt-2 ms-2"><input type="checkbox" class="form-check-input" name="is_published" id="is_published" <?= ((int) ($property['is_published'] ?? 1) === 1) ? 'checked' : '' ?>><label for="is_published" class="form-check-label">Опубликовать объект</label></div>
<div class="col-12 d-flex gap-2 mt-3"><button class="btn btn-primary"><?= $editing ? 'Сохранить изменения' : 'Добавить объект' ?></button><a href="index.php?page=admin" class="btn btn-outline-secondary">Отмена</a></div>
</div></form></div></div></div></div></div>
