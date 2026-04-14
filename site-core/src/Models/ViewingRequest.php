<?php
declare(strict_types=1);

namespace App\Models;

final class ViewingRequest extends BaseModel
{
    public function create(array $data): bool
    {
        $sql = 'INSERT INTO viewing_requests
                (property_id, user_id, client_name, client_phone, client_email, preferred_date, comment, status)
                VALUES (:property_id, :user_id, :client_name, :client_phone, :client_email, :preferred_date, :comment, :status)';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':property_id' => $data['property_id'],
            ':user_id' => $data['user_id'],
            ':client_name' => $data['client_name'],
            ':client_phone' => $data['client_phone'],
            ':client_email' => $data['client_email'],
            ':preferred_date' => $data['preferred_date'],
            ':comment' => $data['comment'],
            ':status' => 'new',
        ]);
    }

    public function hasRecentDuplicate(int $propertyId, int $userId, int $minutes = 5): bool
    {
        $sql = 'SELECT id FROM viewing_requests WHERE property_id = ? AND user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ' . max(1, $minutes) . ' MINUTE) LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$propertyId, $userId]);
        return (bool) $stmt->fetch();
    }

    public function byUser(int $userId): array
    {
        $sql = 'SELECT vr.id AS request_id, vr.created_at, vr.preferred_date, vr.status,
                       p.id AS property_id, p.title, p.price, p.main_photo
                FROM viewing_requests vr
                JOIN properties p ON p.id = vr.property_id
                WHERE vr.user_id = ?
                ORDER BY vr.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function detailForUser(int $requestId, int $userId): ?array
    {
        $sql = 'SELECT vr.*, p.title, p.price, p.address, d.name AS district_name
                FROM viewing_requests vr
                JOIN properties p ON p.id = vr.property_id
                JOIN districts d ON d.id = p.district_id
                WHERE vr.id = ? AND vr.user_id = ?
                LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$requestId, $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function allWithRelations(): array
    {
        $sql = 'SELECT vr.id AS request_id, vr.created_at, vr.preferred_date, vr.client_name, vr.client_phone, vr.client_email, vr.comment, vr.status,
                       u.email AS user_email,
                       p.id AS property_id, p.title AS property_title, p.price AS property_price,
                       d.name AS district_name
                FROM viewing_requests vr
                JOIN users u ON u.id = vr.user_id
                JOIN properties p ON p.id = vr.property_id
                JOIN districts d ON d.id = p.district_id
                ORDER BY vr.id DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function updateStatus(int $requestId, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE viewing_requests SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $requestId]);
    }
}
