<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Domain\Repositories;

use App\Services\PlatformWorkspace\Application\Projections\WorkspaceView;
use App\Services\PlatformWorkspace\Domain\Aggregates\Workspace;
use App\Services\PlatformWorkspace\Domain\ValueObjects\AccountId;
use App\Services\PlatformWorkspace\Domain\ValueObjects\WorkspaceId;

interface WorkspaceRepository
{
    /** @return list<WorkspaceView> */
    public function listAccessible(AccountId $accountId): array;
    public function findAccessible(WorkspaceId $id, AccountId $accountId): ?WorkspaceView;
    public function findDefault(AccountId $accountId): ?WorkspaceView;
    public function findFirstOwnedActive(AccountId $accountId): ?Workspace;
    public function assignDefault(AccountId $accountId, WorkspaceId $workspaceId): void;
    public function lockDefaultProvisioning(AccountId $accountId): void;
    public function find(WorkspaceId $id): ?Workspace;
    public function slugExists(string $slug, ?WorkspaceId $except = null): bool;
    public function save(Workspace $workspace, string $role = 'owner'): void;
}
