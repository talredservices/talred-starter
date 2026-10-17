<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Domain\Repositories;

use App\Services\PlatformWorkspace\Domain\Entities\WorkspaceResourceLink;
use App\Services\PlatformWorkspace\Domain\Enums\WorkspaceResourceType;
use App\Services\PlatformWorkspace\Domain\ValueObjects\WorkspaceId;
use App\Services\PlatformWorkspace\Domain\ValueObjects\WorkspaceResourceReference;

interface WorkspaceResourceRepository
{
    /** @return list<string> */
    public function externalIdsForWorkspace(WorkspaceId $workspaceId, WorkspaceResourceType $type): array;

    /** @return list<string> */
    public function allExternalIds(WorkspaceResourceType $type): array;

    public function isLinked(WorkspaceResourceReference $resource): bool;

    public function save(WorkspaceResourceLink $link): void;

    public function unlink(WorkspaceId $workspaceId, WorkspaceResourceReference $resource): bool;
}
