<?php

declare(strict_types=1);

namespace App\MCF\AccessControl\Data;

final readonly class RoleData
{
    /**
     * @param string[] $permissions
     */
    public function __construct(
        public int|string $role,
        public string $access = 'all',
        public array $permissions = [],
    ) {
    }
}
