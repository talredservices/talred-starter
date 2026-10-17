<?php

declare(strict_types=1);

namespace App\Services\PlatformWorkspace\Infrastructure\Mappers;

use App\Services\PlatformWorkspace\Application\Projections\WorkspaceView;
use App\Services\PlatformWorkspace\Domain\Aggregates\Workspace;
use App\Services\PlatformWorkspace\Domain\Enums\WorkspaceStatus;
use App\Services\PlatformWorkspace\Domain\ValueObjects\AccountId;
use App\Services\PlatformWorkspace\Domain\ValueObjects\WorkspaceId;
use App\Services\PlatformWorkspace\Infrastructure\Models\WorkspaceRecord;

final class WorkspaceMapper
{
    public static function toDomain(WorkspaceRecord $record): Workspace
    {
        return Workspace::restore(WorkspaceId::fromString((string) $record->id), AccountId::fromString((string) $record->owner_id), (string) $record->name, (string) $record->slug, WorkspaceStatus::from((string) $record->status));
    }

    public static function attributes(Workspace $workspace): array
    {
        return ['id' => $workspace->id()->toString(), 'owner_id' => $workspace->ownerId()->toString(), 'name' => $workspace->name(), 'slug' => $workspace->slug(), 'status' => $workspace->status()->value];
    }

    public static function toView(WorkspaceRecord $record, string $role, int $memberCount, bool $isDefault = false): WorkspaceView
    {
        return new WorkspaceView((string) $record->id, (string) $record->name, (string) $record->slug, (string) $record->status, $role, $memberCount, $isDefault);
    }
}
