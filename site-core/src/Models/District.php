<?php
declare(strict_types=1);

namespace App\Models;

final class District extends BaseModel
{
    public function all(): array
    {
        return $this->db->query('SELECT id, name FROM districts ORDER BY name')->fetchAll();
    }
}
