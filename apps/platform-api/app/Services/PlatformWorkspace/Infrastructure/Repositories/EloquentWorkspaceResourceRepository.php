<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Infrastructure\Repositories;

use App\Services\PlatformWorkspace\Domain\Entities\WorkspaceResourceLink;
use App\Services\PlatformWorkspace\Domain\Enums\WorkspaceResourceType;
use App\Services\PlatformWorkspace\Domain\Exceptions\WorkspaceResourceAlreadyLinked;
use App\Services\PlatformWorkspace\Domain\Repositories\WorkspaceResourceRepository;
use App\Services\PlatformWorkspace\Domain\ValueObjects\WorkspaceId;
use App\Services\PlatformWorkspace\Domain\ValueObjects\WorkspaceResourceReference;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class EloquentWorkspaceResourceRepository implements WorkspaceResourceRepository
{
    public function externalIdsForWorkspace(WorkspaceId $workspaceId, WorkspaceResourceType $type): array
    {
        return DB::table('platform_workspace_resources')
            ->where('workspace_id', $workspaceId->toString())
            ->where('resource_type', $type->value)
            ->orderBy('created_at')
            ->pluck('external_resource_id')
            ->map(static fn ($id): string => (string) $id)
            ->all();
    }

    public function allExternalIds(WorkspaceResourceType $type): array
    {
        return DB::table('platform_workspace_resources')
            ->where('resource_type', $type->value)
            ->pluck('external_resource_id')
            ->map(static fn ($id): string => (string) $id)
            ->all();
    }

    public function isLinked(WorkspaceResourceReference $resource): bool
    {
        return DB::table('platform_workspace_resources')
            ->where('resource_type', $resource->type->value)
            ->where('external_resource_id', $resource->externalId)
            ->exists();
    }

    public function save(WorkspaceResourceLink $link): void
    {
        try {
            DB::table('platform_workspace_resources')->insert($link->persistenceRecord());
        } catch (QueryException $exception) {
            if (! $this->isUniqueConstraintViolation($exception)) {
                throw $exception;
            }

            throw new WorkspaceResourceAlreadyLinked(previous: $exception);
        }
    }

    public function unlink(WorkspaceId $workspaceId, WorkspaceResourceReference $resource): bool
    {
        return DB::table('platform_workspace_resources')
            ->where('workspace_id', $workspaceId->toString())
            ->where('resource_type', $resource->type->value)
            ->where('external_resource_id', $resource->externalId)
            ->delete() > 0;
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        $driverCode = (string) ($exception->errorInfo[1] ?? '');
        $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());

        return $driverCode === '1062'
            || $sqlState === '23505'
            || ($sqlState === '23000' && str_contains(mb_strtolower($exception->getMessage()), 'unique'))
            || ($driverCode === '19' && str_contains(mb_strtolower($exception->getMessage()), 'unique constraint'));
    }
}
