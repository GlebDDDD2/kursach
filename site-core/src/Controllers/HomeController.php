<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Property;

final class HomeController
{
    public function index(): void
    {
        $properties = (new Property())->latest(6);
        View::render('home/index', [
            'title' => 'Главная',
            'properties' => $properties,
        ]);
    }

    public function catalog(): void
    {
        $page = max(1, (int) ($_GET['p'] ?? 1));
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'type' => trim((string) ($_GET['type'] ?? '')),
            'price_from' => trim((string) ($_GET['price_from'] ?? '')),
            'price_to' => trim((string) ($_GET['price_to'] ?? '')),
            'floor' => trim((string) ($_GET['floor'] ?? '')),
            'area_from' => trim((string) ($_GET['area_from'] ?? '')),
            'area_to' => trim((string) ($_GET['area_to'] ?? '')),
        ];

        $result = (new Property())->paginated($filters, $page, 6);
        View::render('catalog/index', [
            'title' => 'Каталог',
            'properties' => $result['items'],
            'filters' => $filters,
            'page' => $page,
            'totalPages' => $result['pages'],
        ]);
    }

    public function property(): void
    {
        $id = max(0, (int) ($_GET['id'] ?? 0));
        $propertyModel = new Property();
        $property = $propertyModel->findPublishedById($id);

        if (!$property) {
            http_response_code(404);
            exit('Объект не найден.');
        }

        $photos = $propertyModel->photos($id);
        View::render('property/show', [
            'title' => $property['title'],
            'property' => $property,
            'photos' => $photos,
        ]);
    }
}
