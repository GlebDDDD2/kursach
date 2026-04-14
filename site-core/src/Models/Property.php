<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class Property extends BaseModel
{
    public function latest(int $limit = 6): array
    {
        $limit = max(1, $limit);
        $stmt = $this->db->query(
            'SELECT p.*, d.name AS district_name
             FROM properties p
             JOIN districts d ON d.id = p.district_id
             WHERE p.is_published = 1
             ORDER BY p.id DESC
             LIMIT ' . $limit
        );
        return $stmt->fetchAll();
    }

    public function paginated(array $filters, int $page, int $limit): array
    {
        $where = ['p.is_published = 1'];
        $params = [];

        if (($filters['q'] ?? '') !== '') {
            $where[] = '(p.title LIKE :q OR p.address LIKE :q)';
            $params[':q'] = '%' . $filters['q'] . '%';
        }
        if (($filters['type'] ?? '') !== '') {
            $where[] = 'p.property_type = :type';
            $params[':type'] = $filters['type'];
        }
        foreach (['price_from' => 'p.price >= :price_from', 'price_to' => 'p.price <= :price_to', 'floor' => 'p.floor = :floor', 'area_from' => 'p.area >= :area_from', 'area_to' => 'p.area <= :area_to'] as $key => $sql) {
            if (($filters[$key] ?? '') !== '') {
                $where[] = $sql;
                $params[':' . $key] = $filters[$key];
            }
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);
        $countStmt = $this->db->prepare('SELECT COUNT(*) FROM properties p' . $whereSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $offset = max(0, ($page - 1) * $limit);
        $sql = 'SELECT p.*, d.name AS district_name, r.full_name AS realtor_name
                FROM properties p
                JOIN districts d ON d.id = p.district_id
                JOIN realtors r ON r.id = p.realtor_id'
                . $whereSql .
                ' ORDER BY p.id DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'pages' => (int) ceil($total / $limit),
        ];
    }

    public function allForAdmin(): array
    {
        $sql = 'SELECT p.*, d.name AS district_name, r.full_name AS realtor_name, u.username AS created_by_name
                FROM properties p
                JOIN districts d ON d.id = p.district_id
                JOIN realtors r ON r.id = p.realtor_id
                LEFT JOIN users u ON u.id = p.user_id
                ORDER BY p.id DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function findPublishedById(int $id): ?array
    {
        $sql = 'SELECT p.*, d.name AS district_name, r.full_name AS realtor_name, r.phone AS realtor_phone, r.email AS realtor_email
                FROM properties p
                JOIN districts d ON d.id = p.district_id
                JOIN realtors r ON r.id = p.realtor_id
                WHERE p.id = ? AND p.is_published = 1 LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        return $item ?: null;
    }

    public function findAnyById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM properties WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        return $item ?: null;
    }

    public function photos(int $propertyId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM property_photos WHERE property_id = ? ORDER BY sort_order, id');
        $stmt->execute([$propertyId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO properties
                (title, property_type, district_id, realtor_id, address, price, floor, total_floors, area, rooms, description, main_photo, user_id, is_published)
                VALUES (:title, :property_type, :district_id, :realtor_id, :address, :price, :floor, :total_floors, :area, :rooms, :description, :main_photo, :user_id, :is_published)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':title' => $data['title'],
            ':property_type' => $data['property_type'],
            ':district_id' => $data['district_id'],
            ':realtor_id' => $data['realtor_id'],
            ':address' => $data['address'],
            ':price' => $data['price'],
            ':floor' => $data['floor'],
            ':total_floors' => $data['total_floors'],
            ':area' => $data['area'],
            ':rooms' => $data['rooms'],
            ':description' => $data['description'],
            ':main_photo' => $data['main_photo'],
            ':user_id' => $data['user_id'],
            ':is_published' => $data['is_published'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE properties SET
                title = :title,
                property_type = :property_type,
                district_id = :district_id,
                realtor_id = :realtor_id,
                address = :address,
                price = :price,
                floor = :floor,
                total_floors = :total_floors,
                area = :area,
                rooms = :rooms,
                description = :description,
                main_photo = :main_photo,
                is_published = :is_published
                WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title' => $data['title'],
            ':property_type' => $data['property_type'],
            ':district_id' => $data['district_id'],
            ':realtor_id' => $data['realtor_id'],
            ':address' => $data['address'],
            ':price' => $data['price'],
            ':floor' => $data['floor'],
            ':total_floors' => $data['total_floors'],
            ':area' => $data['area'],
            ':rooms' => $data['rooms'],
            ':description' => $data['description'],
            ':main_photo' => $data['main_photo'],
            ':is_published' => $data['is_published'],
            ':id' => $id,
        ]);
    }

    public function replacePhotos(int $propertyId, array $photos): void
    {
        $delete = $this->db->prepare('DELETE FROM property_photos WHERE property_id = ?');
        $delete->execute([$propertyId]);

        if ($photos === []) {
            return;
        }

        $insert = $this->db->prepare('INSERT INTO property_photos (property_id, photo_path, sort_order) VALUES (?, ?, ?)');
        foreach (array_values($photos) as $index => $photo) {
            $insert->execute([$propertyId, $photo, $index + 1]);
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM properties WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function setPublished(int $id, int $published): bool
    {
        $stmt = $this->db->prepare('UPDATE properties SET is_published = ? WHERE id = ?');
        return $stmt->execute([$published, $id]);
    }
}
