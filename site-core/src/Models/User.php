<?php
declare(strict_types=1);

namespace App\Models;

final class User extends BaseModel
{
    public function create(string $email, string $password, string $username, ?string $phone = null): bool
    {
        $sql = "INSERT INTO users (email, password_hash, username, phone, role) VALUES (:email, :hash, :username, :phone, 'client')";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':email' => $email,
            ':hash' => password_hash($password, PASSWORD_DEFAULT),
            ':username' => $username,
            ':phone' => $phone,
        ]);
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, email, username, phone, role, created_at, password_hash FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function updatePassword(int $id, string $newPassword): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        return $stmt->execute([
            ':hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            ':id' => $id,
        ]);
    }
}
