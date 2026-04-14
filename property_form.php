<?php
if (!isset($districts, $realtors, $formData)) {
    throw new RuntimeException('property_form.php requires $districts, $realtors and $formData.');
}
?>
<form method="POST" class="card shadow-sm p-4">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Название объекта</label>
            <input type="text" name="title" class="form-control" value="<?= h($formData['title']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Тип</label>
            <select name="property_type" class="form-select">
                <option value="apartment" <?= $formData['property_type'] === 'apartment' ? 'selected' : '' ?>>Квартира</option>
                <option value="house" <?= $formData['property_type'] === 'house' ? 'selected' : '' ?>>Дом</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Цена</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?= h($formData['price']) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Район</label>
            <select name="district_id" class="form-select" required>
                <option value="">Выберите район</option>
                <?php foreach ($districts as $district): ?>
                    <option value="<?= (int)$district['id'] ?>" <?= (int)$formData['district_id'] === (int)$district['id'] ? 'selected' : '' ?>><?= h($district['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Риелтор</label>
            <select name="realtor_id" class="form-select" required>
                <option value="">Выберите риелтора</option>
                <?php foreach ($realtors as $realtor): ?>
                    <option value="<?= (int)$realtor['id'] ?>" <?= (int)$formData['realtor_id'] === (int)$realtor['id'] ? 'selected' : '' ?>><?= h($realtor['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Адрес</label>
            <input type="text" name="address" class="form-control" value="<?= h($formData['address']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Этаж</label>
            <input type="number" name="floor" class="form-control" value="<?= h($formData['floor']) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Всего этажей</label>
            <input type="number" name="total_floors" class="form-control" value="<?= h($formData['total_floors']) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Площадь, м²</label>
            <input type="number" step="0.01" name="area" class="form-control" value="<?= h($formData['area']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Комнат</label>
            <input type="number" name="rooms" class="form-control" value="<?= h($formData['rooms']) ?>">
        </div>
        <div class="col-12">
            <label class="form-label">Главное фото (URL)</label>
            <input type="text" name="main_photo" class="form-control" value="<?= h($formData['main_photo']) ?>" placeholder="https://example.com/photo.jpg">
        </div>
        <div class="col-12">
            <label class="form-label">Галерея фото (по одной ссылке на строку)</label>
            <textarea name="gallery_urls" class="form-control" rows="4" placeholder="https://example.com/photo2.jpg&#10;https://example.com/photo3.jpg"><?= h($formData['gallery_urls']) ?></textarea>
        </div>
        <div class="col-12">
            <label class="form-label">Описание</label>
            <textarea name="description" class="form-control" rows="5"><?= h($formData['description']) ?></textarea>
        </div>
        <div class="col-md-4">
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" <?= !empty($formData['is_published']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_published">Показывать в каталоге</label>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-success mt-4"><?= h($submitLabel ?? 'Сохранить объект') ?></button>
</form>
