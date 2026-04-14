<?php
declare(strict_types=1);

namespace App\Models;

final class Realtor extends BaseModel
{
    public function all(): array
    {
        return $this->db->query('SELECT id, full_name FROM realtors ORDER BY full_name')->fetchAll();
    }
}
