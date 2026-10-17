<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Application\DTOs\Output;

use App\Services\PlatformWorkspace\Application\Projections\WorkspaceView;
use App\Services\PlatformWorkspace\Domain\Aggregates\Workspace;
use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class WorkspaceResponseDTO extends ResponseDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $status,
        public readonly string $role,
        public readonly int $member_count,
        public readonly bool $is_default = false,
    ) {}

    public static function fromView(WorkspaceView $view): self
    {
        return new self($view->id, $view->name, $view->slug, $view->status, $view->role, $view->memberCount, $view->isDefault);
    }

    public static function fromDomain(Workspace $workspace): self
    {
        return new self($workspace->id()->toString(), $workspace->name(), $workspace->slug(), $workspace->status()->value, 'owner', 1, false);
    }
}
