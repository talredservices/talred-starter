<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Application\Projections;

final readonly class WorkspaceView
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public string $status,
        public string $role,
        public int $memberCount,
        public bool $isDefault = false,
    ) {}
}
