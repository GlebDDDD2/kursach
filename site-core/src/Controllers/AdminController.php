<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Helpers;
use App\Core\View;
use App\Models\District;
use App\Models\Property;
use App\Models\Realtor;
use App\Models\ViewingRequest;

final class AdminController
{
    public function dashboard(): void
    {
        Auth::requireAdmin();
        View::render('admin/dashboard', [
            'title' => 'Админка',
            'properties' => (new Property())->allForAdmin(),
            'success' => Helpers::getFlash('success'),
            'error' => Helpers::getFlash('error'),
        ]);
    }

    public function propertyForm(?int $id = null): void
    {
        Auth::requireAdmin();
        $propertyModel = new Property();
        $property = $id ? $propertyModel->findAnyById($id) : null;
        if ($id && !$property) {
            exit('Объект не найден.');
        }

        View::render('admin/property_form', [
            'title' => $id ? 'Редактирование объекта' : 'Добавление объекта',
            'property' => $property,
            'photos' => $id ? $propertyModel->photos($id) : [],
            'districts' => (new District())->all(),
            'realtors' => (new Realtor())->all(),
            'actionPage' => $id ? 'admin_property_update&id=' . $id : 'admin_property_store',
            'error' => Helpers::getFlash('error'),
        ]);
    }

    public function storeProperty(): void
    {
        Auth::requireAdmin();
        $this->saveProperty();
    }

    public function updateProperty(): void
    {
        Auth::requireAdmin();
        $id = max(0, (int) ($_GET['id'] ?? 0));
        $this->saveProperty($id);
    }

    private function saveProperty(int $id = 0): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helpers::redirect('index.php?page=admin');
        }
        if (!Helpers::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            exit('CSRF Attack blocked');
        }

        $data = [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'property_type' => trim((string) ($_POST['property_type'] ?? '')),
            'district_id' => max(0, (int) ($_POST['district_id'] ?? 0)),
            'realtor_id' => max(0, (int) ($_POST['realtor_id'] ?? 0)),
            'address' => trim((string) ($_POST['address'] ?? '')),
            'price' => (float) ($_POST['price'] ?? 0),
            'floor' => $_POST['floor'] !== '' ? (int) $_POST['floor'] : null,
            'total_floors' => $_POST['total_floors'] !== '' ? (int) $_POST['total_floors'] : null,
            'area' => (float) ($_POST['area'] ?? 0),
            'rooms' => $_POST['rooms'] !== '' ? (int) $_POST['rooms'] : null,
            'description' => trim((string) ($_POST['description'] ?? '')),
            'main_photo' => trim((string) ($_POST['main_photo'] ?? '')),
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
            'user_id' => Auth::id(),
        ];

        if ($data['title'] === '' || $data['address'] === '' || $data['district_id'] <= 0 || $data['realtor_id'] <= 0 || $data['price'] <= 0 || $data['area'] <= 0 || !in_array($data['property_type'], ['apartment', 'house'], true)) {
            Helpers::flash('error', 'Заполните обязательные поля корректно.');
            Helpers::redirect('index.php?page=' . ($id > 0 ? 'admin_property_edit&id=' . $id : 'admin_property_add'));
        }

        $photoLines = preg_split('/\r\n|\r|\n/', (string) ($_POST['gallery_photos'] ?? '')) ?: [];
        $photos = array_values(array_filter(array_map(static fn(string $item): string => trim($item), $photoLines)));

        $propertyModel = new Property();
        if ($id > 0) {
            $propertyModel->update($id, $data);
            $propertyModel->replacePhotos($id, $photos);
            Helpers::flash('success', 'Объект обновлен.');
        } else {
            $newId = $propertyModel->create($data);
            $propertyModel->replacePhotos($newId, $photos);
            Helpers::flash('success', 'Объект добавлен.');
        }
        Helpers::redirect('index.php?page=admin');
    }

    public function deleteProperty(): void
    {
        Auth::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Helpers::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            exit('CSRF Attack blocked');
        }
        $id = max(0, (int) ($_POST['id'] ?? 0));
        if ($id > 0) {
            (new Property())->delete($id);
            Helpers::flash('success', 'Объект удален.');
        }
        Helpers::redirect('index.php?page=admin');
    }

    public function togglePropertyVisibility(): void
    {
        Auth::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Helpers::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            exit('CSRF Attack blocked');
        }
        $id = max(0, (int) ($_POST['id'] ?? 0));
        $visibility = ((int) ($_POST['visibility'] ?? 0) === 1) ? 1 : 0;
        if ($id > 0) {
            (new Property())->setPublished($id, $visibility);
            Helpers::flash('success', $visibility ? 'Объект опубликован.' : 'Объект снят с публикации.');
        }
        Helpers::redirect('index.php?page=admin');
    }

    public function requests(): void
    {
        Auth::requireAdmin();
        View::render('admin/requests', [
            'title' => 'Заявки',
            'requests' => (new ViewingRequest())->allWithRelations(),
            'success' => Helpers::getFlash('success'),
            'error' => Helpers::getFlash('error'),
        ]);
    }

    public function updateRequestStatus(): void
    {
        Auth::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Helpers::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            exit('CSRF Attack blocked');
        }
        $requestId = max(0, (int) ($_POST['request_id'] ?? 0));
        $status = (string) ($_POST['status'] ?? 'new');
        if ($requestId <= 0 || !in_array($status, ['new', 'processed'], true)) {
            Helpers::flash('error', 'Некорректные данные для смены статуса.');
            Helpers::redirect('index.php?page=admin_requests');
        }
        (new ViewingRequest())->updateStatus($requestId, $status);
        Helpers::flash('success', 'Статус заявки обновлен.');
        Helpers::redirect('index.php?page=admin_requests');
    }

    public function seeder(): void
    {
        Auth::requireAdmin();
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Helpers::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                exit('CSRF Attack blocked');
            }
            $count = min(100, max(1, (int) ($_POST['count'] ?? 10)));
            $propertyModel = new Property();
            $items = $propertyModel->allForAdmin();
            if ($items === []) {
                $message = 'Сначала создайте хотя бы один объект вручную.';
            } else {
                $template = $items[array_rand($items)];
                $exportDir = dirname(__DIR__, 3) . '/public_html/exports';
                if (!is_dir($exportDir)) {
                    mkdir($exportDir, 0775, true);
                }
                $filename = $exportDir . '/properties_' . date('Y-m-d_H-i-s') . '.csv';
                $fp = fopen($filename, 'w');
                fputcsv($fp, array_keys($template));
                foreach ($items as $row) {
                    fputcsv($fp, $row);
                }
                fclose($fp);
                $created = 0;
                for ($i = 1; $i <= $count; $i++) {
                    $price = round((float) $template['price'] * (1 + mt_rand(-15, 15) / 100), 2);
                    $area = round((float) $template['area'] * (1 + mt_rand(-10, 10) / 100), 2);
                    $newId = $propertyModel->create([
                        'title' => $template['title'] . ' #' . mt_rand(1000, 9999),
                        'property_type' => $template['property_type'],
                        'district_id' => (int) $template['district_id'],
                        'realtor_id' => (int) $template['realtor_id'],
                        'address' => $template['address'] . ', корпус ' . mt_rand(1, 20),
                        'price' => $price,
                        'floor' => $template['floor'],
                        'total_floors' => $template['total_floors'],
                        'area' => $area,
                        'rooms' => $template['rooms'],
                        'description' => $template['description'],
                        'main_photo' => $template['main_photo'],
                        'user_id' => Auth::id(),
                        'is_published' => 1,
                    ]);
                    $propertyModel->replacePhotos($newId, array_column($propertyModel->photos((int) $template['id']), 'photo_path'));
                    $created++;
                }
                $message = 'Бэкап сохранен в public_html/exports. Сгенерировано объектов: ' . $created;
            }
        }

        View::render('admin/seeder', [
            'title' => 'Генератор данных',
            'message' => $message,
        ]);
    }
}
