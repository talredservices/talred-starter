<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Application\DTOs\Output;

use Zolta\Support\Application\DTO\Output\ResponseDTO;

final class WorkspaceCollectionResponseDTO extends ResponseDTO
{
    /** @param list<WorkspaceResponseDTO> $workspaces */
    public function __construct(public readonly array $workspaces) {}

    public function toArray(): array
    {
        return ['workspaces' => array_map(static fn (WorkspaceResponseDTO $workspace): array => $workspace->toArray(), $this->workspaces)];
    }
}
